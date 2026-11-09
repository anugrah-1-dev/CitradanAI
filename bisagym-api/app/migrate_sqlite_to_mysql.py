from __future__ import annotations

import argparse
import os
import sys
from collections.abc import Iterator
from typing import Any

from alembic import command
from alembic.config import Config
from sqlalchemy import Engine, Table, create_engine, func, insert, inspect, select
from sqlalchemy.engine import Connection, make_url
from sqlalchemy.exc import SQLAlchemyError

from app import models  # noqa: F401
from app.config import get_settings
from app.database import Base

BATCH_SIZE = 500
ALEMBIC_TABLE = "alembic_version"


def row_batches(connection: Connection, table: Table) -> Iterator[list[dict[str, Any]]]:
    result = connection.execute(select(table)).mappings()
    while batch := result.fetchmany(BATCH_SIZE):
        yield [dict(row) for row in batch]


def check_empty_target(engine: Engine) -> None:
    inspector = inspect(engine)
    table_names = set(inspector.get_table_names())
    unexpected_tables = table_names - set(Base.metadata.tables) - {ALEMBIC_TABLE}
    if unexpected_tables:
        raise RuntimeError(
            "The target database contains tables not owned by the biometric API: "
            + ", ".join(sorted(unexpected_tables))
        )

    for table_name in table_names & set(Base.metadata.tables):
        table = Base.metadata.tables[table_name]
        with engine.connect() as connection:
            primary_key = next(iter(table.primary_key.columns))
            has_rows = connection.execute(select(primary_key).limit(1)).first()
        if has_rows:
            raise RuntimeError(
                f"The target table {table_name!r} is not empty; refusing to overwrite it."
            )


def validate_source(source_engine: Engine) -> list[Table]:
    if source_engine.dialect.name != "sqlite":
        raise RuntimeError("The source database must be SQLite.")

    source_tables = set(inspect(source_engine).get_table_names()) - {ALEMBIC_TABLE}
    expected_tables = set(Base.metadata.tables)
    missing_tables = expected_tables - source_tables
    unexpected_tables = source_tables - expected_tables
    if missing_tables or unexpected_tables:
        details = []
        if missing_tables:
            details.append("missing tables: " + ", ".join(sorted(missing_tables)))
        if unexpected_tables:
            details.append("unrecognized tables: " + ", ".join(sorted(unexpected_tables)))
        raise RuntimeError(
            "The SQLite schema does not match this API version (" + "; ".join(details) + ")."
        )

    return Base.metadata.sorted_tables


def migrate(source_url: str) -> None:
    settings = get_settings()
    target_url = make_url(settings.database_url)
    if target_url.get_backend_name() != "mysql":
        raise RuntimeError(
            "DATABASE_URL must point to the dedicated MySQL biometric database before migration."
        )

    source_engine = create_engine(source_url)
    target_engine = create_engine(settings.database_url, pool_pre_ping=True)
    try:
        tables = validate_source(source_engine)
        check_empty_target(target_engine)

        alembic_config = Config("alembic.ini")
        command.upgrade(alembic_config, "head")
        check_empty_target(target_engine)

        copied_counts: dict[str, int] = {}
        with source_engine.connect() as source, target_engine.begin() as target:
            for table in tables:
                copied = 0
                for batch in row_batches(source, table):
                    target.execute(insert(table), batch)
                    copied += len(batch)
                actual = target.execute(select(func.count()).select_from(table)).scalar_one()
                if actual != copied:
                    raise RuntimeError(
                        f"Row count mismatch for {table.name}: copied {copied}, found {actual}."
                    )
                copied_counts[table.name] = copied

        for table_name, count in copied_counts.items():
            print(f"{table_name}: {count} rows")
        print("SQLite data copied to MySQL. The SQLite source was not modified.")
    finally:
        source_engine.dispose()
        target_engine.dispose()


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Copy the biometric API's SQLite data into its configured MySQL database."
    )
    parser.add_argument(
        "--source-url",
        default="sqlite:///./gym.db",
        help="SQLite source URL (default: sqlite:///./gym.db)",
    )
    args = parser.parse_args()

    try:
        migrate(args.source_url)
    except (RuntimeError, SQLAlchemyError) as error:
        target_url = os.environ.get("DATABASE_URL", "")
        message = str(error)
        if target_url:
            message = message.replace(target_url, "[DATABASE_URL]")
        print(f"Database migration failed: {message}", file=sys.stderr)
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())

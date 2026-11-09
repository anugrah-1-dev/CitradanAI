import logging
import threading
import time

import httpx

from .config import settings

logger = logging.getLogger(__name__)

_lock = threading.Lock()
_last_trigger = 0.0


def _send(duration_ms: int) -> None:
    try:
        httpx.get(
            f"{settings.esp_buzzer_url}/alarm",
            params={"ms": duration_ms, "token": settings.esp_buzzer_token},
            timeout=2.0,
        )
    except httpx.HTTPError as exc:
        logger.warning("Buzzer ESP32 gagal dihubungi: %s", exc)


def trigger_unknown_alarm() -> bool:
    """Nyalakan buzzer ESP32. Non-blocking, dengan cooldown.
    Return True jika perintah benar-benar dikirim."""
    global _last_trigger

    if not settings.esp_buzzer_enabled:
        return False

    with _lock:
        now = time.time()
        if now - _last_trigger < settings.esp_buzzer_cooldown_sec:
            return False
        _last_trigger = now

    threading.Thread(
        target=_send,
        args=(settings.esp_buzzer_duration_ms,),
        daemon=True,
    ).start()
    return True
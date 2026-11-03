@echo off
title BisaGym Biometric Face API (Port 8001)
echo =======================================================
echo  Memulai BisaGym Face Recognition API (FastAPI)
echo  Port: 8001
echo =======================================================
cd /d "%~dp0bisagym-api"
if exist ".venv\Scripts\python.exe" (
    ".venv\Scripts\python.exe" -m uvicorn app.main:app --host 0.0.0.0 --port 8001
) else (
    echo Error: Virtual environment .venv tidak ditemukan di folder bisagym-api!
    pause
)


# ============================================================
# Dockerfile untuk AI Risk Detection Service (FastAPI)
# Multi-stage build untuk meminimalkan ukuran image final.
# ============================================================

FROM python:3.12-slim AS base

# Variabel lingkungan untuk mencegah Python membuat file .pyc
# dan memastikan output tidak di-buffer (penting untuk logging)
ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PIP_NO_CACHE_DIR=1 \
    PIP_DISABLE_PIP_VERSION_CHECK=1

WORKDIR /app

# ============================================================
# Stage: Install dependencies
# ============================================================
FROM base AS deps

RUN apt-get update \
    && apt-get install -y --no-install-recommends gcc \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# ============================================================
# Stage: Final production image
# ============================================================
FROM base AS production

WORKDIR /app

# Salin packages Python yang sudah diinstall dari stage deps
COPY --from=deps /usr/local/lib/python3.12/site-packages /usr/local/lib/python3.12/site-packages
COPY --from=deps /usr/local/bin /usr/local/bin

# Salin source code
COPY app/ ./app/
COPY training/ ./training/

# Buat direktori logs dan models
RUN mkdir -p logs app/models

# Jalankan sebagai user non-root untuk keamanan
RUN useradd --uid 1001 --create-home appuser \
    && chown -R appuser:appuser /app
USER appuser

EXPOSE 8000

# Gunakan Gunicorn + Uvicorn worker untuk production
# --workers: sesuaikan dengan jumlah CPU (2 × CPU + 1)
CMD ["gunicorn", "app.main:app", \
     "--worker-class", "uvicorn.workers.UvicornWorker", \
     "--workers", "2", \
     "--bind", "0.0.0.0:8000", \
     "--timeout", "30", \
     "--graceful-timeout", "10", \
     "--access-logfile", "-", \
     "--error-logfile", "-"]

FROM python:3.11-slim

# Usuario no-root para seguridad
RUN adduser --disabled-password --gecos "" appuser

WORKDIR /app

# Copiar requirements primero (aprovecha cache de capas Docker)
COPY requirements.txt .

# Instalar dependencias como root antes de cambiar de usuario
RUN pip install --no-cache-dir -r requirements.txt

# Copiar código fuente
COPY . .

# Cambiar propietario y usuario
RUN chown -R appuser:appuser /app
USER appuser

# Puerto por defecto (Railway inyecta PORT, Docker Compose usa ML_PORT)
EXPOSE 5000

# Healthcheck — usa el endpoint GET / que ya existe en main.py
HEALTHCHECK --interval=30s --timeout=10s --start-period=10s --retries=3 \
  CMD python -c "import urllib.request; urllib.request.urlopen('http://localhost:${PORT:-5000}/')" || exit 1

CMD ["python", "main.py"]

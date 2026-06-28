FROM python:3.11-slim

RUN adduser --disabled-password --gecos "" appuser

WORKDIR /app

RUN chown -R appuser:appuser /app
USER appuser

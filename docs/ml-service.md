# ML Service — SIDERAE-Blenkir

Microservicio Flask para cálculo de índice de riesgo académico. Invocado **solo desde Laravel**, no desde el frontend.

Referencias: [`ml-service/main.py`](../ml-service/main.py) · [`backend/app/Services/MlRiskService.php`](../backend/app/Services/MlRiskService.php) · [`limitaciones.md`](limitaciones.md)

---

## 1. Rol en la arquitectura

```text
Frontend → POST /api/estudiantes/{id}/procesar-riesgo
         → Laravel (RiesgoAcademicoService)
         → MlRiskService → POST http://ml-engine:5000/predict
         → persistencia indices_riesgo + posible Alerta
```

El servicio ML **no recibe ni filtra por sede**; la decisión de sede única Chilca en V1 aplica a UI, consultas Laravel y seeders demo, no a este contrato.

Configuración URL: `ML_SERVICE_URL` en [`backend/.env.example`](../backend/.env.example) → [`backend/config/services.php`](../backend/config/services.php) clave `ml.url`.

---

## 2. Stack

| Componente | Versión / detalle |
|------------|-------------------|
| Python | Imagen Docker del servicio |
| Flask | Única dependencia en [`requirements.txt`](../ml-service/requirements.txt) |
| Puerto | 5000 (host y contenedor) |
| MySQL | **No** — sin acceso a base de datos |

Contenedor Compose: `ml-engine` (`siderae_ml`).

---

## 3. Endpoints

### `GET /`

Health check.

**Respuesta ejemplo:**

```json
{
  "status": "ok",
  "service": "SIDERAE-ML"
}
```

### `POST /predict`

Calcula índice y nivel de riesgo.

**Content-Type:** `application/json`

#### Payload (campos usados en código)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `promedio_notas` | number | Promedio académico (escala ~0–20) |
| `porcentaje_asistencia` | number | 0–100 |
| `reportes_conductuales` | number/int | Conteo |
| `fast_test_puntaje` | number | Puntaje Fast Test; 0 si ausente |
| `nivel_socioeconomico` | string | `bajo`, `medio`, `alto` |
| `acceso_internet` | bool/int/string | Penalización si no hay acceso |
| `distancia_colegio` | number | km aproximados |

Laravel construye el payload en `RiesgoAcademicoService` (no documentado aquí línea a línea).

#### Respuesta

```json
{
  "indice_riesgo": 0.4523,
  "nivel_riesgo": "medio"
}
```

| Campo | Rango / valores |
|-------|----------------|
| `indice_riesgo` | 0.0 – 1.0 (4 decimales) |
| `nivel_riesgo` | `bajo`, `medio`, `alto` |

#### Umbrales (hardcoded en `main.py`)

| Nivel | Condición |
|-------|-----------|
| `alto` | `indice_riesgo >= 0.70` |
| `medio` | `>= 0.40` y `< 0.70` |
| `bajo` | `< 0.40` |

Umbrales **no** configurables por administrador en código actual (DRS RN-01 parcial).

---

## 4. Modelo de cálculo

**Estado:** prototipo **determinístico** — combinación ponderada de señales en [`main.py`](../ml-service/main.py) L53–71.

**No implementado en código:**

- Random Forest, SVM, XGBoost (DRS RF-06 REQ-06.2)
- Entrenamiento offline / datasets
- Reentrenamiento (RF-18)
- Métricas accuracy/precision/recall/F1
- Persistencia de modelos `.pkl` / MLOps

---

## 5. Integración Laravel (`MlRiskService`)

| Aspecto | Comportamiento |
|---------|----------------|
| Timeout HTTP | 15 s |
| URL | `{ML_SERVICE_URL}/predict` |
| Errores | `RuntimeException` si URL vacía, conexión fallida, HTTP error o JSON inválido |
| Log | Warnings en canal Laravel log |

Tests relacionados: [`RiesgoTest.php`](../backend/tests/Feature/RiesgoTest.php), [`DemoProcesarRiesgosCommandTest.php`](../backend/tests/Feature/DemoProcesarRiesgosCommandTest.php).

---

## 6. Operación local

```bash
# Health desde host
curl http://localhost:5000/

# Predict manual (ejemplo anonimizado)
curl -X POST http://localhost:5000/predict \
  -H "Content-Type: application/json" \
  -d "{\"promedio_notas\":12,\"porcentaje_asistencia\":85,\"reportes_conductuales\":0,\"nivel_socioeconomico\":\"medio\",\"acceso_internet\":true,\"distancia_colegio\":2}"
```

Arranque vía Docker: [`instalacion-docker.md`](instalacion-docker.md).

Si `ml-engine` está caído, `procesar-riesgo` falla en Laravel (no hay fallback automático a cálculo local).

---

## 7. Estado frente a requerimientos DRS

| RF | Estado |
|----|--------|
| RF-06 Cálculo índice | **Parcial** — integración confirmada; modelo ≠ DRS |
| RF-07 Clasificación nivel | **Confirmado** — umbrales en Flask |
| RF-18 Reentrenamiento | **Pendiente** — sin endpoints |

Resumen DRS en repo: [`arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md).

---

## 8. Documentos relacionados

- [`arquitectura/contexto-ml-service-flask.md`](arquitectura/contexto-ml-service-flask.md) — contexto histórico IA
- [`manual-tecnico.md`](manual-tecnico.md)
- [`api.md`](api.md) — endpoint Laravel `procesar-riesgo`

---

*Fase 2 documental — 2026-06-09.*

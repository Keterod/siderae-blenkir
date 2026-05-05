# Contexto ML Service Flask (v1)

## Rol del ML Service
El microservicio Flask recibe variables academicas/socioeconomicas y devuelve un indice de riesgo con su nivel. En la arquitectura actual, es invocado por Laravel y no por el frontend.

## Relacion con el DRS
- El DRS define que RF-06 y RF-07 deben usar procesamiento ML con Random Forest, SVM y XGBoost.
- El estado real del codigo debe contrastarse con ese alcance formal.

## Stack verificado
- `ml-service/requirements.txt`: `flask`
- `ml-service/main.py`: app Flask con endpoints HTTP.

## Endpoints reales detectados (`ml-service/main.py`)
- `GET /` -> estado del servicio.
- `POST /predict` -> calcula y retorna riesgo.

## Payload esperado (confirmado en codigo)
Campos usados por el servicio:
- `promedio_notas`
- `porcentaje_asistencia`
- `reportes_conductuales`
- `fast_test_puntaje`
- `nivel_socioeconomico`
- `acceso_internet`
- `distancia_colegio`

## Respuesta esperada (confirmada en codigo)
- `indice_riesgo` (0..1)
- `nivel_riesgo` (`alto`, `medio`, `bajo`)

## Como lo llama Laravel
- Configuracion en `backend/config/services.php` (`services.ml.url`).
- Llamada desde `App\Services\MlRiskService` hacia `{ML_SERVICE_URL}/predict`.
- Integracion usada por `ProcesarRiesgoController`.

## Estado actual frente a RF de ML
- RF-06 Procesamiento multivariable:
  - **Definido en DRS**.
  - **Confirmado en codigo** para envio de variables y retorno de indice.
  - **Implementado parcialmente** respecto al DRS, porque no se confirma ejecucion real de Random Forest/SVM/XGBoost.
- RF-07 Clasificacion de riesgo:
  - **Definido en DRS**.
  - **Confirmado en codigo** (clasificacion operativo en backend/ML).
- RF-18 Reentrenamiento ML:
  - **Definido en DRS**.
  - **Pendiente de desarrollo** (no se detectan endpoints ni flujo de reentrenamiento).

## Limites actuales del modelo
- `main.py` describe el calculo como "prototipo deterministico".
- No se detecta en `ml-service/` codigo de entrenamiento (`fit`) ni librerias de modelos (`scikit-learn`, `xgboost`) en `requirements.txt`.
- No se confirma version productiva de modelos entrenados en el estado actual.

## Verificacion DRS (Random Forest, SVM, XGBoost)
- En DRS: definidos como parte del alcance formal.
- En codigo actual de `ml-service`: **No confirmado en el estado actual**.

## Reglas para Cursor (ML)
- No inventar modelo entrenado si no existe evidencia en codigo.
- No hacer que frontend llame directo a Flask para riesgo.
- No agregar acceso de Flask a MySQL si no esta definido formalmente.
- No presentar RF-18 como implementado mientras no haya flujo real.
- Mantener clara la distincion entre alcance DRS y estado implementado.

## Pendientes de verificar
- Si existe pipeline/modelo entrenado fuera de `ml-service/main.py` en otra rama o artefacto no versionado.
- Si la salida de `modelos_scores` esperada por backend sera implementada en una siguiente version.

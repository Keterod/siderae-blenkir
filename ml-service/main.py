from flask import Flask, request, jsonify

app = Flask(__name__)


def _float(value, default=0.0):
    try:
        return float(value)
    except (TypeError, ValueError):
        return float(default)


def _nivel_socioeconomico_factor(valor):
    if valor is None:
        return 0.55
    s = str(valor).lower().strip()
    if s == "bajo":
        return 1.0
    if s == "medio":
        return 0.55
    if s == "alto":
        return 0.15
    return 0.55


@app.route("/")
def root():
    return jsonify({"status": "ok", "service": "SIDERAE-ML"})


@app.route("/predict", methods=["POST"])
def predict():
    """
    Prototipo determinístico: combina señales en un índice 0..1 sin entrenar modelos.
    """
    data = request.get_json(silent=True) or {}

    promedio = _float(data.get("promedio_notas"), 10.0)
    pct_asist = _float(data.get("porcentaje_asistencia"), 50.0)
    reportes = int(_float(data.get("reportes_conductuales"), 0.0))
    fast = _float(data.get("fast_test_puntaje"), 0.0)

    socio = _nivel_socioeconomico_factor(data.get("nivel_socioeconomico"))

    raw_internet = data.get("acceso_internet")
    if raw_internet in (True, 1, "1", "true", "True"):
        internet_penalty = 0.0
    else:
        internet_penalty = 0.12

    distancia = _float(data.get("distancia_colegio"), 0.0)

    nota_riesgo = max(0.0, min(1.0, (20.0 - promedio) / 20.0))
    asis_riesgo = max(0.0, min(1.0, (100.0 - pct_asist) / 100.0))
    rep_riesgo = max(0.0, min(1.0, reportes / 5.0))
    dist_riesgo = max(0.0, min(1.0, distancia / 20.0))

    if fast > 0:
        fast_riesgo = max(0.0, min(1.0, (20.0 - fast) / 20.0))
    else:
        fast_riesgo = 0.05

    indice = (
        nota_riesgo * 0.28
        + asis_riesgo * 0.24
        + rep_riesgo * 0.14
        + socio * 0.14
        + dist_riesgo * 0.09
        + fast_riesgo * 0.06
        + internet_penalty
    )
    indice = round(max(0.0, min(1.0, indice)), 4)

    if indice >= 0.70:
        nivel = "alto"
    elif indice >= 0.40:
        nivel = "medio"
    else:
        nivel = "bajo"

    return jsonify({"indice_riesgo": indice, "nivel_riesgo": nivel})


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)

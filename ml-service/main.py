from flask import Flask, request, jsonify

app = Flask(__name__)


def _float(value, default=0.0):
    try:
        return float(value)
    except (TypeError, ValueError):
        return float(default)


@app.route("/")
def root():
    return jsonify({"status": "ok", "service": "SIDERAE-ML"})


@app.route("/predict", methods=["POST"])
def predict():
    """
    Prototipo determinístico RF-06C: combina señales académicas, asistencia y conductuales
    sin variables socioeconómicas ni Fast Test.
    Pesos RF-06C: notas 55%, asistencia 30%, reportes conductuales 15%.
    """
    data = request.get_json(silent=True) or {}

    promedio = _float(data.get("promedio_notas"), 10.0)
    pct_asist = _float(data.get("porcentaje_asistencia"), 50.0)
    reportes = int(_float(data.get("reportes_conductuales"), 0.0))

    nota_riesgo = max(0.0, min(1.0, (20.0 - promedio) / 20.0))
    asis_riesgo = max(0.0, min(1.0, (100.0 - pct_asist) / 100.0))
    rep_riesgo = max(0.0, min(1.0, reportes / 5.0))

    indice = (
        nota_riesgo * 0.55
        + asis_riesgo * 0.30
        + rep_riesgo * 0.15
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

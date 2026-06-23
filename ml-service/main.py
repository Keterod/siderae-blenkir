from flask import Flask, request, jsonify

app = Flask(__name__)


def _float(value, default=0.0):
    try:
        return float(value)
    except (TypeError, ValueError):
        return float(default)


def _int(value, default=0):
    try:
        return int(float(value))
    except (TypeError, ValueError):
        return default


@app.route("/")
def root():
    return jsonify({"status": "ok", "service": "SIDERAE-ML"})


@app.route("/predict", methods=["POST"])
def predict():
    """
    Prototipo determinístico RF-06D: combina señales enriquecidas académicas,
    asistencia y conductuales sin variables socioeconómicas ni Fast Test.
    Pesos globales: académico 55%, asistencia 30%, conductual 15%.
    """
    data = request.get_json(silent=True) or {}

    # --- Academic component (55%) ---
    promedio = _float(data.get("promedio_notas"), 10.0)
    nota_min = _float(data.get("nota_minima"), 0.0)
    cursos_riesgo = _int(data.get("cursos_en_riesgo"), 0)
    cursos_desap = _int(data.get("cursos_desaprobados"), 0)

    promedio_riesgo = max(0.0, min(1.0, (20.0 - promedio) / 20.0))
    nota_min_riesgo = max(0.0, min(1.0, (20.0 - nota_min) / 20.0)) if nota_min > 0 else 0.0
    cursos_riesgo_factor = max(0.0, min(1.0, cursos_riesgo / 3.0))
    cursos_desap_factor = max(0.0, min(1.0, cursos_desap / 3.0))

    academico = (
        promedio_riesgo * 0.50
        + nota_min_riesgo * 0.30
        + cursos_desap_factor * 0.10
        + cursos_riesgo_factor * 0.10
    )
    academico = max(0.0, min(1.0, academico))

    # --- Attendance component (30%) ---
    pct_asist = _float(data.get("porcentaje_asistencia"), 50.0)
    inasistencias = _int(data.get("inasistencias"), 0)
    inasistencias_recientes = _int(data.get("inasistencias_recientes"), 0)

    asis_riesgo = max(0.0, min(1.0, (100.0 - pct_asist) / 100.0))
    inasistencias_factor = max(0.0, min(1.0, inasistencias / 20.0))
    recientes_factor = max(0.0, min(1.0, inasistencias_recientes / 5.0))

    asistencia = (
        asis_riesgo * 0.60
        + inasistencias_factor * 0.25
        + recientes_factor * 0.15
    )
    asistencia = max(0.0, min(1.0, asistencia))

    # --- Behavioral component (15%) ---
    reportes = _int(data.get("reportes_conductuales"), 0)
    reportes_graves = _int(data.get("reportes_graves"), 0)
    gravedad_max = data.get("gravedad_maxima", "")
    reportes_recientes = _int(data.get("reportes_recientes"), 0)

    if reportes == 0:
        conductual = 0.0
    else:
        count_factor = max(0.0, min(1.0, reportes / 5.0))
        graves_factor = max(0.0, min(1.0, reportes_graves / 3.0))

        gravedad_map = {"": 0.0, "leve": 0.25, "moderado": 0.50, "grave": 1.0}
        gravedad_factor = gravedad_map.get(gravedad_max, 0.0)

        recientes_cond = max(0.0, min(1.0, reportes_recientes / 3.0))

        conductual = (
            count_factor * 0.30
            + graves_factor * 0.30
            + gravedad_factor * 0.25
            + recientes_cond * 0.15
        )
        conductual = max(0.0, min(1.0, conductual))

    # --- Combined index ---
    indice = academico * 0.55 + asistencia * 0.30 + conductual * 0.15
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

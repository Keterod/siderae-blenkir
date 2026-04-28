from flask import Flask, request, jsonify
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.svm import SVC
from xgboost import XGBClassifier

app = Flask(__name__)

@app.route('/')
def root():
    return jsonify({"status": "ok", "service": "SIDERAE-ML"})

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()

    features = [
        data.get('promedio_notas', 0),
        data.get('porcentaje_asistencia', 0),
        data.get('reportes_conductuales', 0),
        data.get('fast_test_puntaje', 0),
        data.get('nivel_socioeconomico', 0),
        data.get('acceso_internet', 0),
        data.get('distancia_colegio', 0),
    ]

    X = np.array(features).reshape(1, -1)

    # Modelos con datos de ejemplo (se reentrenan con RF-18)
    X_train = np.random.rand(100, 7)
    y_train = (X_train[:, 0] < 0.5).astype(int)

    rf = RandomForestClassifier(n_estimators=100, random_state=42)
    svm = SVC(probability=True, random_state=42)
    xgb = XGBClassifier(random_state=42, eval_metric='logloss')

    rf.fit(X_train, y_train)
    svm.fit(X_train, y_train)
    xgb.fit(X_train, y_train)

    rf_score = rf.predict_proba(X)[0][1]
    svm_score = svm.predict_proba(X)[0][1]
    xgb_score = xgb.predict_proba(X)[0][1]

    indice_riesgo = round((rf_score + svm_score + xgb_score) / 3, 4)

    if indice_riesgo >= 0.70:
        nivel = "Alto"
    elif indice_riesgo >= 0.40:
        nivel = "Medio"
    else:
        nivel = "Bajo"

    return jsonify({
        "indice_riesgo": indice_riesgo,
        "nivel_riesgo": nivel,
        "modelos": {
            "random_forest": round(rf_score, 4),
            "svm": round(svm_score, 4),
            "xgboost": round(xgb_score, 4)
        }
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)

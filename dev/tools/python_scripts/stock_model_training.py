from dataclasses import dataclass
from sklearn.ensemble import RandomForestRegressor
from sklearn.linear_model import Ridge
from sklearn.model_selection import train_test_split, TimeSeriesSplit
from sklearn.metrics import mean_absolute_error, mean_squared_error
import numpy as np


@dataclass
class TrainingResult:
    model: object
    metrics: dict
    X_val: np.ndarray
    y_val: np.ndarray


def train_regression_model(X, y, model_type: str = "ridge", test_size: float = 0.2, random_state: int = 42):
    """Entraîne un modèle de regression sur des données structurées."""
    if model_type == "ridge":
        model = Ridge(random_state=random_state)
    elif model_type == "rf":
        model = RandomForestRegressor(random_state=random_state, n_estimators=100)
    else:
        raise ValueError("model_type doit être 'ridge' ou 'rf'.")

    X_train, X_val, y_train, y_val = train_test_split(
        X, y, test_size=test_size, shuffle=False
    )

    model.fit(X_train, y_train)
    y_pred = model.predict(X_val)

    metrics = {
        "mae": mean_absolute_error(y_val, y_pred),
        "mse": mean_squared_error(y_val, y_pred),
        "rmse": np.sqrt(mean_squared_error(y_val, y_pred)),
    }

    return TrainingResult(model=model, metrics=metrics, X_val=X_val, y_val=y_val)


def cross_validate_model(X, y, n_splits=5, model_type: str = "ridge", random_state: int = 42):
    """Retourne métriques moyennes sur validation temporelle par TimeSeriesSplit."""
    tscv = TimeSeriesSplit(n_splits=n_splits)
    scores = []

    for train_index, val_index in tscv.split(X):
        X_train, X_val = X.iloc[train_index], X.iloc[val_index]
        y_train, y_val = y.iloc[train_index], y.iloc[val_index]

        result = train_regression_model(
            X_train, y_train, model_type=model_type, test_size=0.0, random_state=random_state
        )

        y_pred = result.model.predict(X_val)
        scores.append(
            {
                "mae": mean_absolute_error(y_val, y_pred),
                "mse": mean_squared_error(y_val, y_pred),
                "rmse": np.sqrt(mean_squared_error(y_val, y_pred)),
            }
        )

    aggregated = {k: np.mean([s[k] for s in scores]) for k in scores[0]}
    return aggregated


def predict_next(model, X_last):
    """Prédit la prochaine valeur à partir des dernières features."""
    return model.predict(X_last)

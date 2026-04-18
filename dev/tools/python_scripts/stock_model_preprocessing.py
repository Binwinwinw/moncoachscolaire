import pandas as pd
import numpy as np
from sklearn.preprocessing import StandardScaler


def load_stock_data(file_path: str, date_column: str = "Date") -> pd.DataFrame:
    """Charge un CSV de données boursières en date indexée."""
    df = pd.read_csv(file_path, parse_dates=[date_column])
    df = df.sort_values(date_column).reset_index(drop=True)
    df[date_column] = pd.to_datetime(df[date_column], errors="coerce")
    df = df.dropna(subset=[date_column])
    df = df.set_index(date_column)
    return df


def clean_stock_data(df: pd.DataFrame) -> pd.DataFrame:
    """Nettoie les données: supprime duplicats, comble NA, est standardisée en place."""
    df = df.drop_duplicates(keep="first")

    num_cols = df.select_dtypes(include=[np.number]).columns.tolist()
    for col in num_cols:
        df[col] = df[col].replace([np.inf, -np.inf], np.nan)
        if df[col].isna().any():
            df[col] = df[col].fillna(method="ffill").fillna(method="bfill")
            if df[col].isna().any():
                df[col] = df[col].fillna(df[col].median())

    return df


def enrich_features(df: pd.DataFrame) -> pd.DataFrame:
    """Ajoute quelques features standard pour la prédiction de prix boursiers."""
    if "Close" not in df.columns:
        raise ValueError("La colonne 'Close' est requise pour extraire des features.")

    df = df.copy()
    df["returns"] = df["Close"].pct_change().fillna(0)

    for w in (3, 5, 10, 20):
        df[f"ma_{w}"] = df["Close"].rolling(window=w, min_periods=1).mean()
        df[f"vol_{w}"] = df["returns"].rolling(window=w, min_periods=1).std().fillna(0)

    df["momentum_5"] = df["Close"].pct_change(periods=5).fillna(0)

    df = df.dropna()
    return df


def build_features_targets(df: pd.DataFrame, target_col: str = "Close", horizon: int = 1, scaler: StandardScaler | None = None):
    """Prépare X, y pour un horizon de prévision. Retourne scaler utilisé."""
    if target_col not in df.columns:
        raise ValueError(f"Colonne cible non trouvée: {target_col}")

    df = df.copy()
    df[f"{target_col}_t+{horizon}"] = df[target_col].shift(-horizon)
    df = df.dropna()

    X = df.drop(columns=[f"{target_col}_t+{horizon}"])
    y = df[f"{target_col}_t+{horizon}"]

    if scaler is None:
        scaler = StandardScaler()
        X_scaled = scaler.fit_transform(X)
    else:
        X_scaled = scaler.transform(X)

    X_scaled = pd.DataFrame(X_scaled, index=X.index, columns=X.columns)
    return X_scaled, y, scaler


def preprocess_stock_data(file_path: str, horizon: int = 1, scaler: StandardScaler | None = None):
    """Chaîne complète: chargement, nettoyage, enrichissement, X/y et mise à l'échelle."""
    df = load_stock_data(file_path)
    df = clean_stock_data(df)
    df = enrich_features(df)
    X, y, scaler = build_features_targets(df, horizon=horizon, scaler=scaler)
    return X, y, scaler

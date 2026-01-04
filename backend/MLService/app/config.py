# app/config.py

import os

class Settings:
    # Хост и порт ML-сервиса в docker-сети appnet
    ML_SERVICE_HOST: str = os.getenv("ML_SERVICE_HOST", "mlservice")
    ML_SERVICE_PORT: int = int(os.getenv("ML_SERVICE_PORT", 5000))

    @property
    def base_url(self):
        return f"http://{self.ML_SERVICE_HOST}:{self.ML_SERVICE_PORT}"

settings = Settings()

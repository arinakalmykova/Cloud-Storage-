from fastapi import FastAPI, UploadFile, File
from PIL import Image
from io import BytesIO
from app.predict import predict

app = FastAPI(title="ML Image Analyzer")

@app.post("/classify")
async def classify(file: UploadFile = File(...)):
    img_bytes = await file.read()
    image = Image.open(BytesIO(img_bytes)).convert("RGB")
    return predict(image)

from fastapi import FastAPI, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from PIL import Image
from io import BytesIO
import imghdr
from app.predict import predict

app = FastAPI(title="ML Image Analyzer")

# Добавить CORS для FastAPI
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost"],  # Ваш фронтенд
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Расширить список поддерживаемых форматов
SUPPORTED_FORMATS = {'jpeg', 'jpg', 'png', 'webp', 'avif'}

@app.post("/classify")
async def classify(file: UploadFile = File(...)):
    # Проверяем формат файла
    content = await file.read()
    
    # Определяем тип изображения
    file_type = imghdr.what(None, h=content)
    
    if not file_type or file_type not in SUPPORTED_FORMATS:
        # Если imghdr не распознал AVIF, пробуем через PIL
        try:
            image = Image.open(BytesIO(content))
            # PIL автоматически определяет AVIF если установлен pillow-avif-plugin
        except Exception as e:
            return {"error": f"Unsupported format: {file_type}. Error: {str(e)}"}
    
    # Возвращаем указатель на начало файла для PIL
    file.file.seek(0)
    img_bytes = await file.read()
    
    try:
        image = Image.open(BytesIO(img_bytes)).convert("RGB")
        return predict(image)
    except Exception as e:
        return {"error": f"Failed to process image: {str(e)}"}
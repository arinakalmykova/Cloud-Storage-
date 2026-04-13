from fastapi import FastAPI, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from PIL import Image, features
from io import BytesIO
from app.predict import predict

app = FastAPI(title="ML Image Analyzer")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

SUPPORTED_FORMATS = {'jpeg', 'jpg', 'png', 'webp', 'avif'}

@app.post("/classify")
async def classify(file: UploadFile = File(...)):
    content = await file.read()
    
    try:
        image = Image.open(BytesIO(content))
        image_format = image.format.lower() if image.format else None

        print(f"File received: {file.filename}")
        print(f"Detected image format: {image_format}")
        print(f"File size: {len(content)} bytes")
        print(f"PIL registered extensions: {list(Image.registered_extensions().keys())}")
        print(f"Pillow features available: {features.pilinfo()}")
        print(f"Supported formats set: {SUPPORTED_FORMATS}")

        if image_format not in SUPPORTED_FORMATS:
            print(f"ERROR: Format {image_format} not supported")
            return {
                "error": "Invalid file type",
                "messages": {
                    "file": [
                        f"The file must be an image of type: {', '.join(SUPPORTED_FORMATS)}.",
                        f"Detected format: {image_format}"
                    ]
                }
            }

        image = image.convert("RGB")
    except Exception as e:
        print(f"Exception while opening image: {str(e)}")
        return {
            "error": "Invalid file type",
            "messages": {"file": [f"Failed to open image: {str(e)}"]}
        }

    print("Image passed all checks, sending to predict()")
    result = predict(image)
    print(f"Prediction result: {result}")
    return result
from PIL import Image
import os

SOURCE_DIR = "raw_images"
TARGET_DIR = "data"

os.makedirs(TARGET_DIR, exist_ok=True)

for class_name in os.listdir(SOURCE_DIR):
    class_folder = os.path.join(SOURCE_DIR, class_name)
    target_folder = os.path.join(TARGET_DIR, class_name)
    os.makedirs(target_folder, exist_ok=True)

    for file in os.listdir(class_folder):
        if file.lower().endswith((".png", ".jpg", ".jpeg", ".webp", ".avif")):
            img_path = os.path.join(class_folder, file)
            img = Image.open(img_path).convert("RGB")
            img = img.resize((224, 224))
            img.save(os.path.join(target_folder, file))

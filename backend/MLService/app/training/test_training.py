# app/training/test_training.py
import sys
import os

# Добавляем путь к проекту
sys.path.append(os.path.join(os.path.dirname(__file__), '..', '..'))

from app.training.dataset import ImageDataset

DATA_DIR = "app/training/data"

# Проверяем датасет
try:
    dataset = ImageDataset(DATA_DIR)
    print(f"✅ Датасет загружен успешно")
    print(f"   Всего изображений: {len(dataset)}")
    
    # Проверяем первые несколько образцов
    for i in range(min(3, len(dataset))):
        image, label = dataset[i]
        print(f"   Образец {i}: форма изображения {image.shape}, метка {label}")
        
except Exception as e:
    print(f"❌ Ошибка при загрузке датасета: {e}")
    print("Проверьте структуру папок в app/training/data/")
import torch
import torch.nn as nn
from torchvision import models
import os

NUM_CLASSES = 5

def load_model():
    print("🤖 Загружаем модель...")
    
    # Сначала создаем модель с ПРАВИЛЬНОЙ структурой
    model = models.mobilenet_v2(weights=None)  # не загружаем ImageNet
    
    # СРАЗУ меняем классификатор на правильную структуру
    model.classifier = nn.Sequential(
        nn.Dropout(0.2),
        nn.Linear(model.last_channel, NUM_CLASSES)
    )
    
    # Теперь пытаемся загрузить обученные веса
    model_path = "app/model/content_classifier.pt"
    
    if os.path.exists(model_path):
        print(f"✅ Найдена обученная модель: {model_path}")
        try:
            # Загружаем веса
            state_dict = torch.load(model_path, map_location="cpu")
            print(f"📊 Загружено {len(state_dict)} параметров")
            
            # Загружаем веса в модель
            model.load_state_dict(state_dict, strict=False)  # strict=False для гибкости
            print("🎯 Обученные веса загружены!")
            
            # Проверяем последний слой
            for name, param in model.named_parameters():
                if 'classifier.1.weight' in name:
                    print(f"🔍 Последний слой: {param.shape}")
                    if param.shape != (5, 1280):  # 5 классов, 1280 features
                        print(f"⚠️ Внимание! Неожиданная форма: {param.shape}")
                    break
                    
        except Exception as e:
            print(f"⚠️ Ошибка загрузки обученных весов: {e}")
            print("🔄 Используем ImageNet веса как базовые")
            model = models.mobilenet_v2(weights="IMAGENET1K_V1")
            model.classifier = nn.Sequential(
                nn.Dropout(0.2),
                nn.Linear(model.last_channel, NUM_CLASSES)
            )
    else:
        print(f"⚠️ Обученная модель не найдена: {model_path}")
        print("🔄 Используем ImageNet веса как базовые")
        model = models.mobilenet_v2(weights="IMAGENET1K_V1")
        model.classifier = nn.Sequential(
            nn.Dropout(0.2),
            nn.Linear(model.last_channel, NUM_CLASSES)
        )
    
    # Замораживаем все параметры для inference
    for param in model.parameters():
        param.requires_grad = False
    
    model.eval()  # режим предсказания
    
    # Быстрая проверка
    with torch.no_grad():
        test_input = torch.randn(1, 3, 224, 224)
        output = model(test_input)
        print(f"✅ Модель инициализирована. Выход: {output.shape}")
    
    return model
import os
import torch
from torch import nn, optim
from torch.utils.data import DataLoader
from torchvision.models import mobilenet_v2
from dataset import ImageDataset

# ---------- Параметры ----------
DATA_DIR = "app/training/data"
MODEL_PATH = "app/model/content_classifier.pt"
BATCH_SIZE = 16          # увеличил batch для стабильности
EPOCHS = 20             # увеличил количество эпох
LEARNING_RATE = 1e-3
NUM_CLASSES = 5

# ---------- Проверяем данные ----------
print("Проверка структуры данных:")
for class_name in ["photo", "text_graphics", "illustration", "ui_screenshot", "mixed"]:
    folder = os.path.join(DATA_DIR, class_name)
    if os.path.exists(folder):
        count = len([f for f in os.listdir(folder) if f.lower().endswith(('.png', '.jpg', '.jpeg', '.webp', '.avif'))])
        print(f"  {class_name}: {count} изображений")
    else:
        print(f"  {class_name}: папка не найдена!")

# ---------- Датасет ----------
dataset = ImageDataset(DATA_DIR)
print(f"Всего изображений в датасете: {len(dataset)}")

if len(dataset) == 0:
    raise RuntimeError("Dataset пуст! Проверь структуру данных.")

dataloader = DataLoader(dataset, batch_size=BATCH_SIZE, shuffle=True)

# ---------- Модель ----------
# Загружаем предобученную модель
model = mobilenet_v2(weights="IMAGENET1K_V1")

# Замораживаем все слои кроме классификатора
for param in model.parameters():
    param.requires_grad = False

# Заменяем классификатор
model.classifier = nn.Sequential(
    nn.Dropout(0.2),
    nn.Linear(model.last_channel, NUM_CLASSES)
)

# Размораживаем только классификатор
for param in model.classifier.parameters():
    param.requires_grad = True

device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
print(f"Используется устройство: {device}")
model.to(device)

# ---------- Loss и оптимизатор ----------
criterion = nn.CrossEntropyLoss()
# Обучаем ТОЛЬКО параметры классификатора
optimizer = optim.Adam(model.classifier.parameters(), lr=LEARNING_RATE)

# ---------- Тренировка ----------
model.train()  # переводим модель в режим тренировки

for epoch in range(EPOCHS):
    running_loss = 0.0
    correct = 0
    total = 0
    
    for batch_idx, (images, labels) in enumerate(dataloader):
        images, labels = images.to(device), labels.to(device)
        
        optimizer.zero_grad()
        outputs = model(images)
        loss = criterion(outputs, labels)
        loss.backward()
        optimizer.step()
        
        running_loss += loss.item()
        
        # Считаем точность
        _, predicted = torch.max(outputs, 1)
        total += labels.size(0)
        correct += (predicted == labels).sum().item()
        
        if batch_idx % 10 == 0:
            print(f"  Batch {batch_idx}, Loss: {loss.item():.4f}")
    
    epoch_accuracy = 100 * correct / total if total > 0 else 0
    print(f"Epoch {epoch+1}/{EPOCHS}, Loss: {running_loss/len(dataloader):.4f}, "
          f"Accuracy: {epoch_accuracy:.2f}%")

# ---------- Сохраняем всю модель ----------
os.makedirs(os.path.dirname(MODEL_PATH), exist_ok=True)
torch.save(model.state_dict(), MODEL_PATH)
print(f"Model saved to {MODEL_PATH}")

# Проверяем размер файла
if os.path.exists(MODEL_PATH):
    size_mb = os.path.getsize(MODEL_PATH) / (1024 * 1024)
    print(f"Размер сохраненной модели: {size_mb:.2f} MB")
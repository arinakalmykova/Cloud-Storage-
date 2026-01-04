import os
from PIL import Image
from torch.utils.data import Dataset
from torchvision import transforms

LABELS = {
    "photo": 0,
    "text_graphics": 1,
    "illustration": 2,
    "ui_screenshot": 3,
    "mixed": 4
}

transform = transforms.Compose([
    transforms.Resize((224, 224)),
    transforms.ToTensor(),
    transforms.Normalize(
        mean=[0.485, 0.456, 0.406],
        std=[0.229, 0.224, 0.225]
    )
])

class ImageDataset(Dataset):
    def __init__(self, root_dir):
        self.root_dir = root_dir
        self.files = []
        self.labels = []

        for label_name, label_idx in LABELS.items():
            folder = os.path.join(root_dir, label_name)
            if not os.path.exists(folder):
                continue
            for f in os.listdir(folder):
                if f.lower().endswith(('.png', '.jpg', '.jpeg', '.webp', '.avif')):
                    self.files.append(os.path.join(folder, f))
                    self.labels.append(label_idx)

    def __len__(self):
        return len(self.files)

    def __getitem__(self, idx):
        img_path = self.files[idx]
        image = Image.open(img_path).convert("RGB")
        label = self.labels[idx]
        return transform(image), label

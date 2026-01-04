import torch
from app.model import load_model
from app.labels import LABELS
from app.utils import preprocess

model = load_model()

def predict(image):
    tensor = preprocess(image)

    with torch.no_grad():
        out = model(tensor)
        probs = torch.softmax(out, dim=1)
        conf, idx = torch.max(probs, 1)

    return {
        "content_type": LABELS[idx.item()],
        "confidence": round(conf.item(), 3)
    }

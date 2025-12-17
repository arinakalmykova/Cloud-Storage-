import { useState, useEffect, useRef } from "react";
import { initEcho } from "./echo"; 
import { API_UPLOAD_URL } from "./api.tsx";

interface PhotoEvent {
  photoId: string;
  url: string;
}

export default function Upload() {
  const [file, setFile] = useState<File | null>(null);
  const [uploading, setUploading] = useState<boolean>(false);
  const [status, setStatus] = useState<string>("");
  const [finalUrl, setFinalUrl] = useState<string | null>(null);
  const animationInterval = useRef<number | null>(null);
  const photoIdRef = useRef<string | null>(null);

  const token = localStorage.getItem("token");
  const userId = localStorage.getItem("user_id"); 
  
  useEffect(() => {
    if (!userId || !token) return;

    const echo = initEcho(token);

    const channel = echo.private(`user.${userId}`);

    channel.listen("photo.compressed", (e: PhotoEvent) => {
      if (e.photoId === photoIdRef.current) {
        if (animationInterval.current) {
          clearInterval(animationInterval.current);
        }
        setFinalUrl(e.url);
        setStatus("Готово! Фото сжато в WebP");
        setUploading(false);
      }
    });

    return () => {
      echo.leave(`user.${userId}`);
    };
  }, [userId, token]);


  const handleUpload = async () => {
    if (!file || !token) {
      setStatus("Файл не выбран или нет авторизации");
      return;
    }

    setUploading(true);
    setStatus("Получаем безопасную ссылку...");
    setFinalUrl(null);

    try {
      const res = await fetch(`${API_UPLOAD_URL}/photos/upload-url`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          fileName: file.name,
          mimeType: file.type || "image/jpeg",
        }),
      });

      if (!res.ok) throw new Error("Не удалось получить ссылку");

      const { photo_id, upload_url }: { photo_id: string; upload_url: string } = await res.json();

      photoIdRef.current = photo_id;

      const currentPhotoId = photo_id;
      setStatus("Загружаем в облако...");

      const uploadRes = await fetch(upload_url, {
        method: "PUT",
        body: file,
        headers: {
          "Content-Type": file.type || "image/jpeg",
        },
      });

      if (!uploadRes.ok) {
        const errorText = await uploadRes.text();
        throw new Error("Ошибка загрузки: " + errorText);
      }

      setStatus("Сжимаем в WebP... (обычно 5–15 сек)");
      startProcessingAnimation();
      

      await fetch(`${API_UPLOAD_URL}/photos/mark-uploaded`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          photo_id: currentPhotoId,
          url: upload_url.split("?")[0], 
          size: file.size,
        }),
      });

      setStatus("Фото загружено! Сжимаем в WebP...");
      startProcessingAnimation();
      setTimeout(() => fallbackCheckStatus(photo_id), 10000);
      
    } catch (err: any) {
      setStatus("Ошибка: " + err.message);
      setUploading(false);
    }
  };

  const startProcessingAnimation = () => {
    let dots = 0;

    if (animationInterval.current) {
      clearInterval(animationInterval.current);
    }

    animationInterval.current = window.setInterval(() => {
      dots = (dots + 1) % 4;
      setStatus("Сжимаем в WebP" + ".".repeat(dots));
    }, 800);
  };

  const fallbackCheckStatus = async (id: string) => {
    if (finalUrl) return;

    try {
      const res = await fetch(`${API_UPLOAD_URL}/photos/${id}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      const data: { status: string; url?: string } = await res.json();

      if (data.status === "compressed" && data.url) {
        if (animationInterval.current) {
          clearInterval(animationInterval.current);
        }
        setFinalUrl(data.url);
        setStatus("Готово! Фото сжато в WebP. ");
        setUploading(false);
      }
    } catch {
      // молчим
    }
  };

  return (
    <div className="loaded">
      <h2 style={{ fontSize: "34px", color: "#000000ff"}}>Загрузить фото</h2>
      <input
        type="file"
        className="loaded_file-input"
        accept="image/*"
        onChange={(e) => setFile(e.target.files?.[0] || null)}
        disabled={uploading}
      />
      <form className ="loaded_form" method="post" action="#">
        <div className="form-group">
          <label htmlFor="Quality">Качество</label>
          <input type="text" />
        </div>
        <div className="form-group">
          <label htmlFor="Resize">Изменение размера</label>
          <input type="text" />
        </div>
        
      </form>
      <button
        onClick={handleUpload}
        disabled={!file || uploading}
        style={{
          padding: "15px 35px",
          fontSize: "19px",
          fontWeight: "600",
          background: uploading ? "#ffffffff" : "#000000ff",
          color: "white",
          border: "none",
          borderRadius: "5px",
          cursor: uploading ? "not-allowed" : "pointer",
          transition: "all 0.3s",
        }}
      >
        {uploading ? "Загрузка..." : "Загрузить"}
      </button>
      {status && <p style={{ marginTop: "30px", fontSize: "20px", fontWeight: "500", color: uploading ? "#4a76fd" : "#333", minHeight: "30px" }}>{status}</p>}
      {finalUrl && (
        <div style={{ marginTop: "40px", animation: "fadeIn 0.8s" }}>
          <img src={finalUrl} alt="Готовое фото" style={{ maxWidth: "100%", maxHeight: "70vh", borderRadius: "20px", boxShadow: "0 20px 40px rgba(0,0,0,0.2)", border: "6px solid #4a76fd" }} />
          <p style={{ marginTop: "20px", color: "#4a76fd", fontSize: "18px", fontWeight: "600" }}>Сжато в WebP — размер уменьшен в 5–10 раз</p>
        </div>
      )}
    </div>
  );
}

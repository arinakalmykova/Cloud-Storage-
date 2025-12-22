import { useState } from 'react';
import { usePhotoUpload } from '../../features/photo-upload/lib/usePhotoUpload.tsx';
import { usePhotoCompressionEcho } from '../../features/photo-upload/lib/usePhotoCompressionEcho.tsx';

export default function Upload() {
  const [file, setFile] = useState<File | null>(null);
  const token = localStorage.getItem('token');
  const userId = localStorage.getItem('user_id');
  const [title, setTitle] = useState<string>('');
  const [description, setDescription] = useState<string>('');
  const [tags, setTags] = useState<string>('');
  const [tagList, setTagList] = useState<string[]>([]);
  // const [quality, setQuality] = useState<string>("");
  // const [resize, setResize] = useState<string>("");
  const { uploading, status, finalUrl, upload, photoIdRef, setFinalUrl, setUploading } =
    usePhotoUpload(token, title, description, tagList);

  usePhotoCompressionEcho({
    userId,
    token,
    photoIdRef,
    onDone: (url) => {
      setFinalUrl(url);
      setUploading(false);
    },
  });

  const handleTagsChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const input = e.target.value;
    setTags(input);
    const tagsArray = input
      .split(',')
      .map((tag) => tag.trim())
      .filter((tag) => tag !== '');
    setTagList(tagsArray);
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0] || null;
    setFile(selectedFile);

    if (selectedFile) {
      const nameWithoutExt = selectedFile.name.replace(/\.[^/.]+$/, '');
      setTitle(nameWithoutExt);
    }
  };

  return (
    <div className="loaded">
      <h2 style={{ fontSize: '34px', color: '#000000ff' }}>Загрузить фото</h2>

      <input type="file" accept="image/*" disabled={uploading} onChange={handleFileChange} />

      <form className="loaded_form" method="post" action="#">
        <div className="form-group">
          <label htmlFor="Title">Название</label>
          <input
            type="text"
            name="title"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
          />
        </div>
        <div className="form-group">
          <label htmlFor="Description">Описание</label>
          <input
            type="text"
            name="description"
            value={description}
            onChange={(e) => setDescription(e.target.value)}
          />
        </div>
        <div className="form-group">
          <label htmlFor="Quality">Качество</label>
          <input type="text" name="quality" />
        </div>
        <div className="form-group">
          <label htmlFor="Resize">Изменение размера</label>
          <input type="text" name="resize" />
        </div>
        <div>
          <label htmlFor="Tags">Теги</label>
          <input
            type="text"
            id="tags"
            value={tags}
            placeholder="Введите теги через запятую"
            onChange={handleTagsChange}
          />
          {/* Отображение тегов как подсказка */}
          {tagList.length > 0 && (
            <div style={{ fontSize: '12px', color: '#666', marginTop: '4px' }}>
              Теги: {tagList.join(', ')}
            </div>
          )}
        </div>
      </form>

      <button
        style={{
          padding: '15px 35px',
          fontSize: '19px',
          fontWeight: '600',
          background: '#000000ff',
          color: 'white',
          border: 'none',
          borderRadius: '5px',
          cursor: uploading ? 'not-allowed' : 'pointer',
          transition: 'all 0.3s',
        }}
        disabled={!file || uploading}
        onClick={() => file && upload(file)}
      >
        {uploading ? 'Загрузка...' : 'Загрузить'}
      </button>

      {status && (
        <p
          style={{
            marginTop: '30px',
            fontSize: '20px',
            fontWeight: '500',
            color: uploading ? '#4a76fd' : '#333',
            minHeight: '30px',
          }}
        >
          {status}
        </p>
      )}
      {finalUrl && (
        <div style={{ marginTop: '40px', animation: 'fadeIn 0.8s' }}>
          <img
            src={finalUrl}
            alt="Готовое фото"
            style={{
              maxWidth: '100%',
              maxHeight: '70vh',
              borderRadius: '20px',
              boxShadow: '0 20px 40px rgba(0,0,0,0.2)',
              border: '6px solid #4a76fd',
            }}
          />
          <p style={{ marginTop: '20px', color: '#4a76fd', fontSize: '18px', fontWeight: '600' }}>
            Сжато в WebP — размер уменьшен в 5–10 раз
          </p>
        </div>
      )}
    </div>
  );
}

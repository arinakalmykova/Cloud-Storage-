import { useState, useEffect } from 'react';
import { useSearchPhotos, usePhotoFilters, useFolderPhoto } from '../../features';
import { PhotoCard } from '../../entities';
import { FilterPanel, ModalWindowPhoto } from '../../widgets';
import type { Photo, Filters } from '../../entities';
import { Input, Button } from '../../shared';
import { useAppSelector } from '../../app';
import { Search } from 'lucide-react';
import { motion } from 'framer-motion';
import '../../app/styles/SearchPage.css';

export function SearchPage() {
  const [selectedPhoto, setSelectedPhoto] = useState<Photo | null>(null);

  const token = useAppSelector((state) => state.auth.token);
  const { downloadPhotoHook } = useFolderPhoto(token ?? '');
  const { photos, searchPhotos } = useSearchPhotos();
  const [searchQuery, setSearchQuery] = useState('');
  const [filterClicked, setFilterClicked] = useState(false);
  const [filters, setFilters] = useState<Filters>({
    file_name: '',
    tags: [],
    dominant_color: [],
    description: '',
    dateFrom: '',
    dateTo: '',
    format: '',
  });
  const { tags, colors } = usePhotoFilters();
  useEffect(() => {
    searchPhotos({ query: searchQuery, filters });
  }, []);
  return (
    <div className="search-page">
      <div className="search-page__content">
        <div className="search-page__topbar">
          <motion.div
            className="search-page__welcome"
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
          >
            <h1>Поиск фото</h1>
          </motion.div>
        </div>
        <div className="search-input-container">
          <Button
            className="search-button"
            onClick={() => searchPhotos({ query: searchQuery, filters })}
          >
            <Search />
          </Button>
          <Input
            className="search-input"
            type="text"
            placeholder="Поиск по названию, тегам..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
          <Button className="filter-button" onClick={() => setFilterClicked(!filterClicked)}>
            Фильтры
          </Button>
        </div>
        {filterClicked && (
          <FilterPanel
            filters={filters}
            setFilters={setFilters}
            availableTags={tags}
            availableColors={colors}
          />
        )}

        <div className="search-grid">
          {photos.map((photo: Photo) => (
            <PhotoCard
              key={photo.id}
              photo={photo}
              onClick={() => setSelectedPhoto(photo)}
              onDownload={(photo) => {
                downloadPhotoHook(photo.url, photo.title);
              }}
            />
          ))}
        </div>
        {selectedPhoto && (
          <ModalWindowPhoto selectedPhoto={selectedPhoto} onClose={() => setSelectedPhoto(null)} />
        )}
      </div>
    </div>
  );
}

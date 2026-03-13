import React, { useState, useEffect } from 'react';
import { Input } from '../../shared';
import type { Filters } from '../../entities';
import styles from '../../app/styles/FilterPanel.module.css';

interface Props {
  filters: Filters;
  setFilters: React.Dispatch<React.SetStateAction<Filters>>;
  availableTags: string[];
  availableColors: string[];
}

export function FilterPanel({ filters, setFilters, availableTags, availableColors }: Props) {
  const [selectedTags, setSelectedTags] = useState<string[]>(filters.tags || []);
  const [selectedColors, setSelectedColors] = useState<string[]>(filters.dominant_color || []);

  useEffect(() => {
    setFilters({ ...filters, tags: selectedTags, dominant_color: selectedColors });
  }, [selectedTags, selectedColors]);

  const toggleTag = (tag: string) => {
    setSelectedTags(
      selectedTags.includes(tag) ? selectedTags.filter((t) => t !== tag) : [...selectedTags, tag]
    );
  };

  const toggleColor = (color: string) => {
    setSelectedColors(
      selectedColors.includes(color)
        ? selectedColors.filter((c) => c !== color)
        : [...selectedColors, color]
    );
  };

  return (
    <div className={styles.filterPanel}>
      <Input
        label="Название:"
        type="text"
        placeholder="Название"
        value={filters.file_name}
        onChange={(e) => setFilters({ ...filters, file_name: e.target.value })}
      />

      <div className={styles.tagPicker}>
        <label>Теги:</label>
        <div className={styles.tags}>
          {availableTags.map((tag) => (
            <label key={tag} className={`${styles.tagItem} ${selectedTags.includes(tag) ? styles.selected : ''}`}>
              <Input
                type="checkbox"
                checked={selectedTags.includes(tag)}
                onChange={() => toggleTag(tag)}
              />
              {tag}
            </label>
          ))}
        </div>
      </div>

      <div className={styles.colorPicker}>
        <label>Цвета:</label>
        <div className={styles.colorSquares}>
          {availableColors.map((color) => (
            <div
              key={color}
              className={`${styles.colorSquare} ${selectedColors.includes(color) ? styles.selected : ''}`}
              style={{ backgroundColor: color }}
              onClick={() => toggleColor(color)}
            />
          ))}
        </div>
      </div>

      <Input
        label="Описание:"
        type="text"
        placeholder="Описание"
        value={filters.description}
        onChange={(e) => setFilters({ ...filters, description: e.target.value })}
      />
      <Input
        label="Дата от:"
        type="date"
        placeholder="Дата от"
        value={filters.dateFrom}
        onChange={(e) => setFilters({ ...filters, dateFrom: e.target.value })}
      />
      <Input
        label="Дата до:"
        type="date"
        placeholder="Дата до"
        value={filters.dateTo}
        onChange={(e) => setFilters({ ...filters, dateTo: e.target.value })}
      />
      <select
        value={filters.format}
        onChange={(e) => setFilters({ ...filters, format: e.target.value })}
      >
        <option value="">Все форматы</option>
        <option value="webp">WEBP</option>
        <option value="png">PNG</option>
        <option value="jpeg">JPEG</option>
        <option value="avif">AVIF</option>
      </select>
    </div>
  );
}

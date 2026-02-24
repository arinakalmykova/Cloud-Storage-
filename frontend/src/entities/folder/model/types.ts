import type { Photo } from '../../photo/model/types';

export interface Folder {
  id: string;
  name: string;
  photos: Photo[];
  createdAt: string;
}

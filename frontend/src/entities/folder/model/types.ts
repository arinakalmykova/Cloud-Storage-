import type { Photo } from '../../../entities';

export interface Folder {
  id: string;
  name: string;
  photos: Photo[];
  createdAt: string;
}

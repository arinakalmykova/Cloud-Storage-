export interface Photo {
  id: string;
  title: string;
  url: string;
  size: number;
  format: string;
  description: string;
  createdAt: string;
  folder?: string | null;
  tags?: string[];
  contentType?: string | null;
}

type SupportedFormat = 'jpeg' | 'png' | 'webp' | 'avif';
const ALL_FORMATS: SupportedFormat[] = ['jpeg', 'png', 'webp', 'avif'];

export function formatsForContentType(contentType?: string | null): SupportedFormat[] {
  void contentType;
  return [...ALL_FORMATS];
}

export function normalizeQualityForFormat(
  quality: number,
  format: string,
  _contentType?: string | null
): number {
  if (format === 'png') {
    return 100;
  }

  return Math.max(0, Math.min(100, quality));
}

import { useCallback, useState } from 'react';
import { estimateCompressionPreview, recommendML } from '../../../entities';

type RecommendationOptions = {
  format?: string;
  quality?: number;
  contentType?: string;
};

export function useMLRecommendation(token: string | null) {
  const [MLQuality, setMLQuality] = useState(0);
  const [MLFormat, setMLFormat] = useState('');
  const [MLContentType, setMLContentType] = useState('');
  const [estimatedSizeBytes, setEstimatedSizeBytes] = useState<number | null>(null);
  const [savedPercent, setSavedPercent] = useState<number | null>(null);
  const [loading, setLoading] = useState(false);
  const [estimating, setEstimating] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const recommend = useCallback(async (file: File) => {
    if (!token) return;

    try {
      setLoading(true);
      setError(null);

      const result = await recommendML(token, file);

      if (typeof result.quality === 'number') setMLQuality(result.quality);
      if (result.format) setMLFormat(result.format);
      if (result.content_type) setMLContentType(result.content_type);
      setEstimatedSizeBytes(typeof result.estimated_size === 'number' ? result.estimated_size : null);
      setSavedPercent(typeof result.saved_percent === 'number' ? result.saved_percent : null);

      return result;
    } catch (e: any) {
      setError(e.message);
      console.error(e);
    } finally {
      setLoading(false);
    }
  }, [token]);

  const estimate = useCallback(async (file: File, options: RecommendationOptions) => {
    if (!token) return;

    try {
      setEstimating(true);

      if (!options.format || typeof options.quality !== 'number' || !options.contentType) {
        throw new Error('Compression estimate requires format, quality and content type.');
      }

      const result = await estimateCompressionPreview(token, file, {
        format: options.format,
        quality: options.quality,
        contentType: options.contentType,
      });

      setEstimatedSizeBytes(typeof result.estimated_size === 'number' ? result.estimated_size : null);
      setSavedPercent(typeof result.saved_percent === 'number' ? result.saved_percent : null);

      return result;
    } catch (e) {
      console.error(e);
    } finally {
      setEstimating(false);
    }
  }, [token]);

  return {
    MLQuality,
    MLFormat,
    MLContentType,
    estimatedSizeBytes,
    savedPercent,
    recommend,
    estimate,
    loading,
    estimating,
    error,
  };
}

import { recommendML } from '../../../entities';
import { useState } from 'react';

export function useMLRecommendation(token: string | null) {
  const [MLQuality, setMLQuality] = useState(0);
  const [MLFormat, setMLFormat] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const run = async (file: File) => {
    if (!token) return;
    try {
      setLoading(true);
      const result = await recommendML(token, file);
      if (result.quality) setMLQuality(result.quality);
      if (result.format) setMLFormat(result.format);
      return result;  
    } catch (e: any) {
      setError(e.message);
      console.error(e);
    }
    finally {
      setLoading(false);
    }
  };

  return { MLQuality, MLFormat, run, loading, error };
}

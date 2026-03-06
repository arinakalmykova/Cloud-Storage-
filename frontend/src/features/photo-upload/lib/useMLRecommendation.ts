import { recommendML } from '../../../entities';
import { useState } from 'react';

export function useMLRecommendation(token: string | null) {
  const [MLQuality, setMLQuality] = useState(0);
  const [MLFormat, setMLFormat] = useState('');

  const run = async (file: File) => {
    if (!token) return;
    try {
      const result = await recommendML(token, file);
      if (result.quality) setMLQuality(result.quality);
      if (result.format) setMLFormat(result.format);
      return result;
    } catch (e) {
      console.error(e);
    }
  };

  return { MLQuality, MLFormat, run };
}

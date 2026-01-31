import { useState } from 'react';
import '../../../app/styles/Slider.css';
import {Input} from '../../../widgets';

type Props = {
  originalSrc: string;
  compressedSrc: string;
};

export function Slider({ originalSrc, compressedSrc }: Props) {
  const [value, setValue] = useState(50);

  return (
    <div className="container">
      <div
        className="image after"
        style={{ backgroundImage: `url(${compressedSrc})` }}
      />

      <div className="image before">
      <div
        className="inner"
        style={{
          backgroundImage: `url(${originalSrc})`,
          clipPath: `inset(0 ${100 - value}% 0 0)`, 
        }}
      />
    </div>


      <Input
        type="range"
        className="slider"
        min={1}
        max={100}
        value={value}
        onChange={(e) => setValue(Number(e.target.value))}
      />

      <div
        className="slider-button"
        style={{ left: `calc(${value}% - 15px)` }}
      />
    </div>
  );
}

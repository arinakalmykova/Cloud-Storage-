import { useState } from 'react';
import { Input } from '../../../shared';
import styles from '../../../app/styles/Slider.module.css';

type Props = {
  originalSrc: string;
  compressedSrc: string;
};

export function Slider({ originalSrc, compressedSrc }: Props) {
  const [value, setValue] = useState(50);

  return (
    <div className={styles.container}>
      <div className={`${styles.image} ${styles.after}`} style={{ backgroundImage: `url(${compressedSrc})` }} />

      <div className={`${styles.image} ${styles.before}`}>
        <div
          className={styles.inner}
          style={{
            backgroundImage: `url(${originalSrc})`,
            clipPath: `inset(0 ${100 - value}% 0 0)`,
          }}
        />
      </div>

      <Input
        type="range"
        className={styles.slider}
        min={1}
        max={100}
        value={value}
        onChange={(e) => setValue(Number(e.target.value))}
      />

      <div className={styles.sliderButton} style={{ left: `calc(${value}% - 15px)` }} />
    </div>
  );
}

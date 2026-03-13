import styles from '../../../app/styles/Input.module.css';

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
}

export function Input({ label, id, ...rest }: InputProps) {
  return (
    <div className={styles.input}>
      {label && <label htmlFor={id}>{label}</label>}
      <input id={id} {...rest} />
    </div>
  );
}

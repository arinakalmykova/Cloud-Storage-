import styles from "../../../app/styles/Error.module.css";

export function Error({ error }: { error: string }) {
    return <div className={styles.error}>Ошибка:{error}</div>;
}
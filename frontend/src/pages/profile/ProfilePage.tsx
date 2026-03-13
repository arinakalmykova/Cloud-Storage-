import { useState } from 'react';
import { motion } from 'framer-motion';
import { useAuth } from '../../features';
import { Button, Input, Loader, Error } from '../../shared';
import styles from '../../app/styles/ProfilePage.module.css';

export function ProfilePage() {
  const { user, logout, deleteUserFunc, updateUserFunc, loading, error } = useAuth();
  const [editOpen, setEditOpen] = useState(false);
  const [name, setName] = useState(user?.name || '');
  const [email, setEmail] = useState(user?.email || '');
  const [formError, setFormError] = useState('');

  if (loading) return <Loader />;
  if (error) return <Error error={error} />;
  if (!user) return <div>Вы не авторизованы</div>;

  const handleSave = async () => {
    const result = await updateUserFunc({ name, email });
    if (result.success) {
      setEditOpen(false);
    } else {
      setFormError(result.message || 'Произошла ошибка');
    }
  };

  const handleDeleteAccount = async () => {
    if (window.confirm('Вы точно хотите удалить аккаунт?')) {
      const result = await deleteUserFunc();
      if (!result.success) {
        setFormError(result.message || 'Произошла ошибка');
        alert (result.message || 'Произошла ошибка');
      }
    }
  };

  return (
    <div className={styles.profilePage}>
      <div className={styles.profilePageContent}>
        <div className={styles.profilePageToolbar}>
          <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }}>
            <h1>Профиль</h1>
          </motion.div>
        </div>

        <div className={styles.profilePageBody}>
          {editOpen ? (
            <div className={styles.profilePageEdit}>
              <h2>Редактирование профиля</h2>
              <Input
                label="Имя:"
                value={name}
                onChange={(e) => setName(e.target.value)}
                placeholder="Имя"
              />
              <Input
                label="Email:"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="Email"
              />


              {formError && <div className={styles.formError}>{formError}</div>}

              <div className={styles.profilePageEditButtons}>
                <Button onClick={handleSave}>Сохранить</Button>
              </div>
            </div>
          ) : (
            <>
              <div className={styles.profilePageInfo}>
                <h3>
                  <strong>Имя:</strong> {user.name}
                </h3>
                <h3>
                  <strong>Email:</strong> {user.email}
                </h3>
                <h3>
                  <strong>Дата регистрации:</strong> {new Date(user.createdAt).toLocaleDateString()}
                </h3>
              </div>
              <div className={styles.profilePageButtons}>
                <Button className={styles.profileButton} onClick={() => setEditOpen(true)}>
                  Редактировать
                </Button>
                <Button className={styles.profileButton} onClick={handleDeleteAccount}>
                  Удалить аккаунт
                </Button>
                <Button className={styles.profileButton} onClick={logout}>
                  Выйти
                </Button>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

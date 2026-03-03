import { useState } from 'react';
import { motion } from 'framer-motion';
import { useAuth } from '../../features';
import { Button, Input } from '../../shared';
import '../../app/styles/ProfilePage.css';

export function ProfilePage() {
  const { user, logout, deleteUserFunc, updateUserFunc } = useAuth();
  const [editOpen, setEditOpen] = useState(false);
  const [name, setName] = useState(user?.name || '');
  const [email, setEmail] = useState(user?.email || '');

  if (!user) return <div>Вы не авторизованы</div>;

  const handleSave = async () => {
    const result = await updateUserFunc({ name, email });
    if (result.success) {
      setEditOpen(false);
    } else {
      alert(result.message || 'Ошибка при обновлении профиля');
    }
  };

  const handleDeleteAccount = async () => {
    if (window.confirm('Вы точно хотите удалить аккаунт?')) {
      const result = await deleteUserFunc();
      if (!result.success) alert(result.message || 'Ошибка при удалении аккаунта');
    }
  };

  return (
    <div className="profile-page">
      <div className="profile-page__content">
        <div className="profile-page__topbar">
          <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }}>
            <h1>Профиль</h1>
          </motion.div>
        </div>

        <div className="profile-page__body">
          {editOpen ? (
            <div className="profile-page__edit">
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
              <div className="profile-page-edit__buttons">
                <Button onClick={handleSave}>Сохранить</Button>
              </div>
            </div>
          ) : (
            <>
              <div className="profile-page__info">
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
              <div className="profile-page__buttons">
                <Button className="profile-button" onClick={() => setEditOpen(true)}>
                  Редактировать
                </Button>
                <Button className="profile-button" onClick={handleDeleteAccount}>
                  Удалить аккаунт
                </Button>
                <Button className="profile-button" onClick={logout}>
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

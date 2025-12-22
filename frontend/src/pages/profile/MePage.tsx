import { Link } from 'react-router-dom';
import type { FC } from 'react';
import type { User } from '../../entities/user/model/types.ts';

interface UserProps {
  user: User;
}
const MePage: FC<UserProps> = ({ user }) => {
  return (
    <div style={{ padding: '50px', textAlign: 'center' }}>
      <h1>Мой профиль</h1>
      <div style={{ marginTop: '30px', fontSize: '1.4rem', lineHeight: '2' }}>
        <p>
          <strong>Имя:</strong> {user.name}
        </p>
        <p>
          <strong>Email:</strong> {user.email}
        </p>
        <p>
          <strong>ID:</strong> {user.id}
        </p>
      </div>
      <Link to="/profile" style={{ color: '#000000ff', fontSize: '1.2rem' }}>
        Назад к загрузке фото
      </Link>
    </div>
  );
};

export default MePage;

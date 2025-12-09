import { Link } from "react-router-dom";
import Upload from "../Upload.tsx";
import type {User} from '../api.tsx';
import type {FC} from "react";

interface ProfileProps {
  user:User;
  setUser: (user:User | null) => void;
}
const Profile: FC<ProfileProps> = ({ user, setUser }) => {
  const handleLogout = () => {
    localStorage.removeItem("token");
    setUser(null);
  };

  return (
    <div style={{ minHeight: "100vh", background: "#f5f7fa" }}>
      <header style={{
        background: "#1a1a2e",
        color: "white",
        padding: "1rem 2rem",
        display: "flex",
        justifyContent: "space-between",
        alignItems: "center",
        boxShadow: "0 4px 12px rgba(0,0,0,0.2)"
      }}>
        <h1 style={{ margin: 0, fontSize: "1.8rem" }}>ФотоЗагрузчик</h1>
        <nav style={{ display: "flex", gap: "2rem", alignItems: "center" }}>
          <span>Привет, <strong>{user.name}</strong>!</span>
          <Link to="/me" style={{ color: "#4a76fd", fontWeight: "bold" }}>Мой профиль</Link>
          <button onClick={handleLogout} style={{
            background: "#ff4444",
            color: "white",
            border: "none",
            padding: "10px 20px",
            borderRadius: "8px",
            cursor: "pointer"
          }}>
            Выйти
          </button>
        </nav>
      </header>

      <main style={{ padding: "40px 20px", maxWidth: "800px", margin: "0 auto" }}>
        <Upload />
      </main>
    </div>
  );
}

export default Profile;
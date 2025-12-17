import { Link } from "react-router-dom";
import Upload from "../Upload.tsx";
import type { User } from '../AuthFunctions.tsx';
import type { FC } from "react";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faUserCircle } from '@fortawesome/free-regular-svg-icons';  // regular для user-circle
import { faSignOutAlt } from '@fortawesome/free-solid-svg-icons';   // опционально для кнопки выхода

interface ProfileProps {
  user: User;
  setUser: (user: User | null) => void;
}

const Profile: FC<ProfileProps> = ({ user, setUser }) => {
  const handleLogout = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user_id"); 
    setUser(null);
  };

  return (
    <div style={{ minHeight: "100vh", background: "#f8f9fa" }}>
      <header style={{
        background: "#ffffff",
        padding: "2rem",
        borderBottom: "1px solid #e0e0e0",
        display: "flex",
        justifyContent: "space-between",
        alignItems: "center",
      }}>
        <div className="logo">
          <img src="/logo.svg" alt="PIXORY" style={{ height: "40px" }} />
        </div>

        <nav style={{ display: "flex", gap: "2rem", alignItems: "center" }}>
          <Link 
            to="/me" 
            style={{ color: "#000000ff", fontSize: "28px" }}
            title="Профиль"
          >
            <FontAwesomeIcon icon={faUserCircle} />
          </Link>

          <button 
            onClick={handleLogout}
            style={{
              background: "#000000ff",
              color: "white",
              border: "none",
              padding: "10px 20px",
              borderRadius: "5px",
              cursor: "pointer",
              fontSize: "16px",
              display: "flex",
              alignItems: "center",
              gap: "8px",
              transition: "background 0.3s",
            }}
          >
            Выйти
          </button>
        </nav>
      </header>

      <main style={{ padding: "40px 20px", maxWidth: "900px", margin: "0 auto" }}>
        <Upload />
      </main>
    </div>
  );
};

export default Profile;
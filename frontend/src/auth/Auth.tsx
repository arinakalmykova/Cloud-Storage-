import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import Login from "./Login.tsx";
import Register from "./Register.tsx";
import type {User} from '../AuthFunctions.tsx';
import type {FC} from "react";

export interface AuthProps {
  setToken: (token: string) => void;
  setUser: (user: User | null) => void;
  error?: string;
}

const Auth:FC<AuthProps> = ({ setToken, setUser}) =>{
  const [isRegister, setIsRegister] = useState<boolean>(false);
  const navigate = useNavigate();

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (token) {
      navigate("/profile");
    }
  }, [navigate]);

  return (
    <div style={{ padding: "50px", textAlign: "center" }}>
      <h1>{isRegister ?  "Вход": "Регистрация"}</h1>

      {isRegister ? (
         <Login setToken={setToken} setUser={setUser} />
      ) : (
       <Register setToken={setToken} setUser={setUser} />
      )}

      <button
        onClick={() => setIsRegister(!isRegister)}
        style={{
          marginTop: "20px",
          background: "none",
          border: "none",
          color: "#4a76fd",
          cursor: "pointer",
          textDecoration: "underline"
        }}
      >
        {isRegister ? "Нет аккаунта? Зарегистрироваться" : "Уже есть аккаунт? Войти"}
      </button>
    </div>
  );
}

export default Auth;

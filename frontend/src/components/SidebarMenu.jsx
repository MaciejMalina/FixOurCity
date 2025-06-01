import React from "react";
import { useNavigate } from "react-router-dom";
import "../styles/Dashboard.css";

export default function SidebarMenu({ children }) {
  const navigate = useNavigate();

  const handleLogout = () => {
    localStorage.clear();
    navigate("/login");
  };

  return (
    <div className="dashboard">
      <aside className="dashboard__sidebar">
        <div className="logo" onClick={() => navigate("/")}>
          <img src="/logo.png" alt="FixOurCity" />
        </div>
        <nav>
          <ul>
            <li onClick={() => navigate("/reports")}>
              Lista zgłoszeń
            </li>
            <li onClick={() => navigate("/new-report")}>Dodaj nowe zgłoszenie</li>
            <li onClick={() => navigate("/admin")}>Panel administratora</li>
            <li onClick={() => navigate("/settings")}>Ustawienia</li>
          </ul>
        </nav>
        <button className="dashboard__logout" onClick={handleLogout}>
          Wyloguj
        </button>
      </aside>
      <main className="dashboard__map" style={{ background: "#f4f6f7", minHeight: "100vh" }}>
        {children}
      </main>
    </div>
  );
}
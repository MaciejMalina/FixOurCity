import React from "react";
import { useNavigate } from "react-router-dom";
import "../styles/Dashboard.css";

function clearAllCookies() {
  document.cookie.split(";").forEach(cookie => {
    const eqPos = cookie.indexOf("=");
    const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
  });
}

export default function SidebarMenu({ children, reports = [] }) {
  const navigate = useNavigate();
  const token = localStorage.getItem('token');

  const handleLogout = async () => {
    try {
      await fetch('http://localhost:8000/api/v1/auth/logout', {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Authorization': token ? `Bearer ${token}` : ''
        }
      });
    } catch (e) {
      console.error('Logout error:', e);
    }
    localStorage.clear();
    clearAllCookies();
    navigate("/login");
  };

  return (
    <div className="dashboard">
      <aside className="dashboard__sidebar">
        <div className="logo" onClick={() => navigate("/dashboard")}>
          <img src="/logo.png" alt="FixOurCity" />
        </div>
        <nav>
          <ul>
            <li onClick={() => navigate("/reports")}>
              Lista zgłoszeń
              {reports.length > 0 && (
                <ul className="dashboard__recent">
                  {reports.map(r => (
                    <li key={r.id} onClick={() => navigate(`/reports/${r.id}`)}>
                      • {r.title || `#${r.id}`}
                    </li>
                  ))}
                </ul>
              )}
            </li>
            <li onClick={() => navigate("/new-report")}>Dodaj nowe zgłoszenie</li>
            <li onClick={() => navigate("/admin")}>Panel administratora</li>
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
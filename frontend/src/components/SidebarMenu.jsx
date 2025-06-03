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

function prefetchIsAdmin(token) {
  const cached = sessionStorage.getItem("isAdmin");
  if (cached !== null) return;
  fetch('/api/v1/auth/me', {
    headers: { Authorization: `Bearer ${token}` },
    credentials: 'include'
  })
    .then(res => res.json())
    .then(data => {
      sessionStorage.setItem("isAdmin", data.roles?.includes('ROLE_ADMIN') ? "1" : "0");
    })
    .catch(() => sessionStorage.setItem("isAdmin", "0"));
}

export default function SidebarMenu({ children, reports = [] }) {
  const navigate = useNavigate();
  const token = localStorage.getItem('token');
  const [isAdmin, setIsAdmin] = React.useState(false);

  const handleAdminHover = () => {
    if (token) prefetchIsAdmin(token);
  };

  React.useEffect(() => {
    if (!token) return;
    const cached = sessionStorage.getItem("isAdmin");
    if (cached !== null) {
      setIsAdmin(cached === "1");
      return;
    }
    fetch('/api/v1/auth/me', {
      headers: { Authorization: `Bearer ${token}` },
      credentials: 'include'
    })
      .then(res => res.json())
      .then(data => {
        const admin = data.roles?.includes('ROLE_ADMIN');
        setIsAdmin(admin);
        sessionStorage.setItem("isAdmin", admin ? "1" : "0");
      })
      .catch(() => {
        setIsAdmin(false);
        sessionStorage.setItem("isAdmin", "0");
      });
  }, [token]);

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
    sessionStorage.removeItem("isAdmin");
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
            <li
              style={{ display: isAdmin ? undefined : "none" }}
              onClick={() => navigate("/admin")}
              onMouseEnter={handleAdminHover}
            >
              Panel administratora
            </li>
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
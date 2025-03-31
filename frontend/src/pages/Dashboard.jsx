import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import "../styles/Dashboard.css";

export default function Dashboard() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  const fetchUsers = async () => {
    setLoading(true);
    try {
      const response = await fetch("http://localhost:8000/api/users");
      const data = await response.json();
      setUsers(data);
    } catch (err) {
      console.error("Error fetching users:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  const handleRefresh = () => {
    fetchUsers();
  };

  const handleUserClick = (id) => {
    navigate(`/users/${id}`);
  };

  const handleLogout = () => {
    localStorage.removeItem("user");
    navigate("/");
  };

  return (
    <div className="dashboard-container">
      <h2>Dashboard</h2>
      <div className="dashboard-buttons">
        <button onClick={handleRefresh}>Odśwież dane</button>
        <button onClick={() => navigate("/reports")}>Zobacz zgłoszenia</button>
        <button onClick={() => navigate("/new-report")}>Dodaj zgłoszenie</button>
        <button onClick={handleLogout}>Wyloguj się</button>
      </div>
      {loading ? (
        <p className="loading-text">Ładowanie użytkowników...</p>
      ) : (
        <ul className="user-list">
          {users.map((user) => (
            <li key={user.id} onClick={() => handleUserClick(user.id)}>
              {user.email}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

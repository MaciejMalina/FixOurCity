import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

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
    <div style={{ padding: "30px" }}>
      <h2>Dashboard</h2>
      <button onClick={handleRefresh}>Odśwież dane</button>
      <button onClick={handleLogout} style={{ marginLeft: "10px" }}>
        Wyloguj się
      </button>
      {loading ? (
        <p>Ładowanie użytkowników...</p>
      ) : (
        <ul>
          {users.map((user) => (
            <li
              key={user.id}
              style={{ cursor: "pointer", margin: "10px 0" }}
              onClick={() => handleUserClick(user.id)}
            >
              {user.email}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
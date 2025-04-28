import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import "../styles/Dashboard.css";
import Loading from "../components/Loading";

export default function Dashboard() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  const fetchUsers = async () => {
    setLoading(true);
    try {
      const response = await fetch("http://localhost:8000/api/users", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });
  
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error("Unauthorized. Please log in again.");
        }
        throw new Error("Failed to fetch users.");
      }
  
      const data = await response.json();
      setUsers(data);
    } catch (err) {
      console.error("Error fetching users:", err.message);
      setUsers([]);
      if (err.message === "Unauthorized. Please log in again.") {
        localStorage.removeItem("token");
        navigate("/login");
      }
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

  const handleLogout = async () => {
    const token = localStorage.getItem("token");
  
    try {
      await fetch("http://localhost:8000/api/logout", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ token }),
      });
    } catch (err) {
      console.error("Logout error:", err.message);
    } finally {
      localStorage.removeItem("token");
      localStorage.removeItem("refresh_token");
      navigate("/login");
    }
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

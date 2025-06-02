import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import "../../styles/UserList.css";
import Loading from "../ui/Loading";

export default function UsersList() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    fetch('/api/v1/users', {
      credentials: 'include',
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
      .then(res => res.json())
      .then(data => {
        setUsers(data.data || []);
        setLoading(false);
      });
  }, []);

  if (loading) return <Loading />;

    return (
    <div className="users-container">
      <h2>Użytkownicy</h2>

      {users.length === 0 ? (
        <div className="users-empty">Brak użytkowników.</div>
      ) : (
        <ul className="users-list">
          {users.map(u => (
            <li key={u.id}>
              <div className="user-info">
                <span className="user-email">{u.email}</span>
                <span className="user-roles">({u.roles.join(", ")})</span>
              </div>
              <button
                className="user-edit-btn"
                onClick={() => navigate(`/admin/edit-user/${u.id}`)}
              >
                Edytuj
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
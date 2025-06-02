import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

export default function UsersList() {
  const [users, setUsers] = useState([]);
  const navigate = useNavigate();

  useEffect(() => {
    fetch('/api/v1/users', {
      credentials: 'include',
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
      .then(res => res.json())
      .then(data => setUsers(data.data || []));
  }, []);

  return (
    <div>
      <h2>Użytkownicy</h2>
      <ul>
        {users.map(u => (
          <li key={u.id}>
            {u.email} ({u.roles.join(', ')})
            <button onClick={() => navigate(`/admin/edit-user/${u.id}`)}>Edytuj</button>
          </li>
        ))}
      </ul>
    </div>
  );
}
import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

export default function AdminUsers() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  const fetchUsers = async () => {
    try {
      const response = await fetch("http://localhost:8000/api/users/profile", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });

      const profile = await response.json();

      if (!profile.roles.includes("ROLE_ADMIN")) {
        navigate("/dashboard");
        return;
      }

      const usersResponse = await fetch("http://localhost:8000/api/admin/users", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });
      const data = await usersResponse.json();
      setUsers(data);
    } catch (err) {
      console.error("Error fetching users:", err.message);
      navigate("/dashboard");
    } finally {
      setLoading(false);
    }
  };

  const deleteUser = async (id) => {
    if (!window.confirm("Are you sure you want to delete this user?")) return;

    await fetch(`http://localhost:8000/api/admin/users/${id}/delete`, {
      method: "DELETE",
      headers: {
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    });

    fetchUsers();
  };

  const promoteToAdmin = async (id) => {
    await fetch(`http://localhost:8000/api/admin/users/${id}/role`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
      body: JSON.stringify({ roles: ["ROLE_ADMIN"] }),
    });

    fetchUsers();
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  if (loading) return <p>Loading users...</p>;

  return (
    <div className="admin-users">
      <h2>Manage Users</h2>
      <ul>
        {users.map((user) => (
          <li key={user.id}>
            {user.email} - {user.roles.join(", ")}
            <button onClick={() => deleteUser(user.id)}>Delete</button>
            <button onClick={() => promoteToAdmin(user.id)}>Promote to Admin</button>
          </li>
        ))}
      </ul>
    </div>
  );
}

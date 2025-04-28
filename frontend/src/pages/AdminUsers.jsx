import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import Loading from "../components/Loading";

export default function AdminUsers() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null); // <<< dodajemy error
  const navigate = useNavigate();

  const fetchUsers = async () => {
    try {
      const profileResponse = await fetch("http://localhost:8000/api/users/profile", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });

      if (!profileResponse.ok) {
        if (profileResponse.status === 401) {
          throw new Error("Unauthorized 401");
        }
        if (profileResponse.status === 403) {
          throw new Error("Forbidden 403");
        }
        throw new Error("Failed to fetch profile");
      }

      const profile = await profileResponse.json();

      if (!profile.roles.includes("ROLE_ADMIN")) {
        throw new Error("Forbidden 403");
      }

      const usersResponse = await fetch("http://localhost:8000/api/admin/users", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });

      if (!usersResponse.ok) {
        if (usersResponse.status === 401) {
          throw new Error("Unauthorized 401");
        }
        if (usersResponse.status === 403) {
          throw new Error("Forbidden 403");
        }
        throw new Error("Failed to fetch users");
      }

      const data = await usersResponse.json();
      setUsers(data);
    } catch (err) {
      console.error("Fetch users error:", err.message);

      if (err.message === "Unauthorized") {
        localStorage.removeItem("token");
        localStorage.removeItem("refresh_token");
        navigate("/login");
      } else {
        setError(err);
      }
    } finally {
      setLoading(false);
    }
  };

  const deleteUser = async (id) => {
    if (!window.confirm("Are you sure you want to delete this user?")) return;

    try {
      const response = await fetch(`http://localhost:8000/api/admin/users/${id}/delete`, {
        method: "DELETE",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });

      if (!response.ok) {
        throw new Error("Failed to delete user");
      }

      fetchUsers();
    } catch (err) {
      console.error("Delete user error:", err.message);
      setError(err);
    }
  };

  const promoteToAdmin = async (id) => {
    try {
      const response = await fetch(`http://localhost:8000/api/admin/users/${id}/role`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
        body: JSON.stringify({ roles: ["ROLE_ADMIN"] }),
      });

      if (!response.ok) {
        throw new Error("Failed to promote user");
      }

      fetchUsers();
    } catch (err) {
      console.error("Promote user error:", err.message);
      setError(err);
    }
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  if (loading) return <Loading />;
  
  if (error) throw error;

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

import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";

export default function UserDetails() {
  const { id } = useParams();
  const [user, setUser] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const response = await fetch(`http://localhost:8000/api/users/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`,
          },
        });
        
        if (response.ok) {
          const data = await response.json();
          setUser(data);
        } else {
          navigate("/dashboard");
        }
      } catch (err) {
        console.error(err);
        navigate("/dashboard");
      }
    };
    fetchUser();
  }, [id, navigate]);

  if (!user) {
    return <p>Ładowanie użytkownika...</p>;
  }

  return (
    <div style={{ padding: "30px" }}>
      <h2>Szczegóły użytkownika</h2>
      <p><strong>ID:</strong> {user.id}</p>
      <p><strong>Email:</strong> {user.email}</p>
      <p><strong>Role:</strong> {user.roles.join(", ")}</p>
      <button onClick={() => navigate("/dashboard")}>Powrót</button>
    </div>
  );
}

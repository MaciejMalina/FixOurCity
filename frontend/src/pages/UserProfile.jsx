import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";

export default function UserProfile() {
  const { id } = useParams();  // <=== Bierzemy ID z URL
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

        if (!response.ok) {
          if (response.status === 401) {
            throw new Error("Unauthorized");
          }
          if (response.status === 404) {
            throw new Error("User not found");
          }
          throw new Error("Failed to fetch user");
        }

        const data = await response.json();
        setUser(data);
      } catch (err) {
        console.error("Error fetching user:", err.message);
        if (err.message === "Unauthorized") {
          localStorage.removeItem("token");
          navigate("/login");
        }
      }
    };

    fetchUser();
  }, [id, navigate]);

  if (!user) return <p>Loading user...</p>;

  return (
    <div className="user-profile-page">
      <h2>User Details</h2>
      <p><strong>Email:</strong> {user.email}</p>
      <p><strong>First Name:</strong> {user.firstName}</p>
      <p><strong>Last Name:</strong> {user.lastName}</p>
      <p><strong>Roles:</strong> {(user.roles || []).join(", ")}</p>
    </div>
  );
}

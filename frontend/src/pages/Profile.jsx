import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

export default function Profile() {
  const [profile, setProfile] = useState(null);
  const [currentPassword, setCurrentPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [message, setMessage] = useState("");
  const navigate = useNavigate();

  useEffect(() => {
    const fetchProfile = async () => {
      try {
        const response = await fetch("http://localhost:8000/api/users/profile", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`,
          },
        });

        if (!response.ok) {
          if (response.status === 401) {
            throw new Error("Unauthorized");
          }
          throw new Error("Failed to fetch profile");
        }

        const data = await response.json();
        setProfile(data);
      } catch (err) {
        console.error("Error fetching profile:", err.message);
        if (err.message === "Unauthorized") {
          localStorage.removeItem("token");
          navigate("/login");
        }
      }
    };

    fetchProfile();
  }, [navigate]);

  const handleChangePassword = async (e) => {
    e.preventDefault();
    setMessage("");

    try {
      const response = await fetch("http://localhost:8000/api/users/change-password", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
        body: JSON.stringify({ currentPassword, newPassword }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || "Failed to change password");
      }

      setMessage("✅ Password changed successfully!");
      setCurrentPassword("");
      setNewPassword("");
    } catch (err) {
      console.error("Error changing password:", err.message);
      setMessage(`❌ ${err.message}`);
    }
  };

  if (!profile) return <p>Loading profile...</p>;

  return (
    <div className="profile-page">
      <h2>My Profile</h2>
      <p><strong>Email:</strong> {profile.email}</p>
      <p><strong>First Name:</strong> {profile.firstName}</p>
      <p><strong>Last Name:</strong> {profile.lastName}</p>
      <p><strong>Roles:</strong> {profile.roles.join(", ")}</p>

      <hr />

      <h3>Change Password</h3>
      <form onSubmit={handleChangePassword}>
        <input
          type="password"
          placeholder="Current Password"
          value={currentPassword}
          onChange={(e) => setCurrentPassword(e.target.value)}
          required
        />
        <input
          type="password"
          placeholder="New Password"
          value={newPassword}
          onChange={(e) => setNewPassword(e.target.value)}
          required
        />
        <button type="submit">Change Password</button>
      </form>

      {message && <p>{message}</p>}
    </div>
  );
}

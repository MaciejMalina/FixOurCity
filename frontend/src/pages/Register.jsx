import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import "../styles/Auth.css";

export default function Register() {
  const [email, setEmail] = useState("");
  const [firstName, setFirstName] = useState(""); // dodane
  const [lastName, setLastName] = useState("");   // dodane
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [error, setError] = useState("");
  const navigate = useNavigate();

  const handleRegister = async (e) => {
    e.preventDefault();
    setError("");

    if (password !== confirmPassword) {
      setError("Passwords do not match");
      return;
    }

    try {
      const response = await fetch('http://localhost:8000/api/users/register', { 
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          email,
          password,
          firstName,
          lastName
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        setError(data.error || 'Registration failed');
        return;
      }

      navigate('/');
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <div className="auth-container">
      <div className="auth-box">
        <div className="auth-logo">
          <img src="/logo.png" alt="FixOurCity" />
          <h1>Fix<span style={{ color: '#2a9d8f' }}>Our</span>City</h1>
        </div>
        <form className="auth-form" onSubmit={handleRegister}>
          <input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
          <input
            type="text"
            placeholder="First Name"
            value={firstName}
            onChange={(e) => setFirstName(e.target.value)}
            required
          />
          <input
            type="text"
            placeholder="Last Name"
            value={lastName}
            onChange={(e) => setLastName(e.target.value)}
            required
          />
          <input
            type="password"
            placeholder="Password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
          <input
            type="password"
            placeholder="Confirm Password"
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
            required
          />
          <button type="submit">Register</button>
          {error && <p>{error}</p>}
          <p>
            Do you have an account? <a href="/login">Login!</a>
          </p>
        </form>
      </div>
    </div>
  );
}

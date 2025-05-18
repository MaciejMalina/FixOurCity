import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import FormContainer from "../ui/FormContainer";
import "../../styles/Auth.css";
import { login } from "../../api/auth";

export default function Login() {
  const [email, setEmail]       = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading]   = useState(false);
  const [error, setError]       = useState("");
  const navigate                = useNavigate();

  const handleSubmit = async e => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const response = await login({ email, password });

      const data = await response.json().catch(() => ({}));
      if (data.token) {
        localStorage.setItem("token", data.token);
      }

      navigate("/dashboard");
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <FormContainer>
      <div className="auth-logo">
        <img src="/logo.png" alt="FixOurCity" />
        <h1>Fix<span style={{ color: '#2a9d8f' }}>Our</span>City</h1>
      </div>

      <form className="auth-form" onSubmit={handleSubmit}>
        <input
          className="auth-input"
          type="email"
          placeholder="email"
          value={email}
          onChange={e => setEmail(e.target.value)}
          required
        />
        <input
          className="auth-input"
          type="password"
          placeholder="password"
          value={password}
          onChange={e => setPassword(e.target.value)}
          required
        />
        <button
          className="auth-button"
          type="submit"
          disabled={loading}
        >
          {loading ? "Ładowanie..." : "Login"}
        </button>

        {error && <p className="auth-error">{error}</p>}

        <p className="auth-switch">
          Nie masz konta? <a href="/register">Zajerestruj się!</a>
        </p>
      </form>
    </FormContainer>
  );
}

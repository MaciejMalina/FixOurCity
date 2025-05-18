import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import FormContainer from "../ui/FormContainer";
import "../../styles/Auth.css";
import { register } from "../../api/auth";

export default function Register() {
  const [form, setForm]       = useState({ email:"", password:"", firstName:"", lastName:"" });
  const [loading, setLoading] = useState(false);
  const [error, setError]     = useState("");
  const navigate              = useNavigate();

  const handleChange = e =>
    setForm(f => ({ ...f, [e.target.name]: e.target.value }));

  const handleSubmit = async e => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      await register(form);
      navigate("/login");
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
          name="firstName"
          placeholder="Imię"
          value={form.firstName}
          onChange={handleChange}
          required
        />
        <input
          name="lastName"
          placeholder="Nazwisko"
          value={form.lastName}
          onChange={handleChange}
          required
        />
        <input
          name="email"
          type="email"
          placeholder="Email"
          value={form.email}
          onChange={handleChange}
          required
        />
        <input
          name="password"
          type="password"
          placeholder="Hasło"
          value={form.password}
          onChange={handleChange}
          required
        />
        <button type="submit" disabled={loading}>
          {loading ? "Ładowanie..." : "Zarejestruj się"}
        </button>

        {error && <p>{error}</p>}

        <p>
          Masz już konto? <a href="/login">Zaloguj się</a>
        </p>
      </form>
    </FormContainer>
  );
}

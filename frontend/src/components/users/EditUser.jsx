import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import '../../styles/EditUser.css';

export default function EditUser() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [user, setUser] = useState(null);
  const [form, setForm] = useState({ email: "", firstName: "", lastName: "", roles: [] });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    setLoading(true);
    fetch(`/api/v1/users/${id}`, {
      credentials: "include",
      headers: { Authorization: `Bearer ${localStorage.getItem("token")}` }
    })
      .then(res => {
        if (!res.ok) throw new Error("Nie znaleziono użytkownika");
        return res.json();
      })
      .then(data => {
        setUser(data);
        setForm({
          email: data.email,
          firstName: data.firstName,
          lastName: data.lastName,
          roles: data.roles || []
        });
        setError("");
      })
      .catch(e => setError(e.message))
      .finally(() => setLoading(false));
  }, [id]);

  const handleChange = e => {
    const { name, value } = e.target;
    setForm(f => ({ ...f, [name]: value }));
  };

  const handleRoleChange = e => {
    const { value, checked } = e.target;
    setForm(f => {
      let roles = [...f.roles];
      if (checked) {
        if (!roles.includes(value)) roles.push(value);
      } else {
        roles = roles.filter(r => r !== value);
      }
      return { ...f, roles };
    });
  };

  const handleSubmit = async e => {
    e.preventDefault();
    setSaving(true);
    setError("");
    try {
      const resp = await fetch(`/api/v1/users/${id}`, {
        method: "PATCH",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("token")}`
        },
        credentials: "include",
        body: JSON.stringify(form)
      });
      if (!resp.ok) {
        const { error } = await resp.json().catch(() => ({}));
        throw new Error(error || "Błąd zapisu");
      }
      navigate("/admin");
    } catch (e) {
      setError(e.message);
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div>Ładowanie...</div>;
  if (error) return <div style={{ color: "crimson" }}>{error}</div>;
  if (!user) return null;

  return (
    <div className="edit-user-container">
      <h2>Edytuj użytkownika</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label>Email:<br />
            <input
              type="email"
              name="email"
              value={form.email}
              onChange={handleChange}
              required
              style={{ width: "100%" }}
            />
          </label>
        </div>
        <div>
          <label>Imię:<br />
            <input
              type="text"
              name="firstName"
              value={form.firstName}
              onChange={handleChange}
              required
              style={{ width: "100%" }}
            />
          </label>
        </div>
        <div>
          <label>Nazwisko:<br />
            <input
              type="text"
              name="lastName"
              value={form.lastName}
              onChange={handleChange}
              required
              style={{ width: "100%" }}
            />
          </label>
        </div>
        <div>
          <label>Role:</label><br />
          <label>
            <input
              type="checkbox"
              value="ROLE_USER"
              checked={form.roles.includes("ROLE_USER")}
              onChange={handleRoleChange}
            /> Użytkownik
          </label>
          <label style={{ marginLeft: 16 }}>
            <input
              type="checkbox"
              value="ROLE_ADMIN"
              checked={form.roles.includes("ROLE_ADMIN")}
              onChange={handleRoleChange}
            /> Administrator
          </label>
        </div>
        <div style={{ marginTop: 16 }}>
          <button type="submit" disabled={saving}>
            {saving ? "Zapisywanie..." : "Zapisz"}
          </button>
          <button type="button" style={{ marginLeft: 12 }} onClick={() => navigate("/admin")}>
            Anuluj
          </button>
        </div>
        {error && <div style={{ color: "crimson", marginTop: 10 }}>{error}</div>}
      </form>
    </div>
  );
}
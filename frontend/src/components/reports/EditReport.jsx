import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";

export default function EditReport() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [report, setReport] = useState(null);
  const [form, setForm] = useState({
    title: "",
    description: "",
    categoryId: "",
    statusId: "",
    latitude: "",
    longitude: ""
  });
  const [categories, setCategories] = useState([]);
  const [statuses, setStatuses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    fetch(`/api/v1/reports/${id}`, {
      credentials: "include",
      headers: { Authorization: `Bearer ${localStorage.getItem("token")}` }
    })
      .then(res => res.json())
      .then(data => {
        setReport(data);
        setForm({
          title: data.title,
          description: data.description,
          categoryId: data.category?.id || "",
          statusId: data.status?.id || "",
          latitude: data.latitude || "",
          longitude: data.longitude || ""
        });
      })
      .catch(() => setError("Nie znaleziono zgłoszenia"));

    fetch("/api/v1/categories", { credentials: "include" })
      .then(res => res.json())
      .then(data => setCategories(data.data || []));
    fetch("/api/v1/statuses", { credentials: "include" })
      .then(res => res.json())
      .then(data => setStatuses(data.data || []));
    setLoading(false);
  }, [id]);

  const handleChange = e => {
    const { name, value } = e.target;
    setForm(f => ({ ...f, [name]: value }));
  };

  const handleSubmit = async e => {
    e.preventDefault();
    setSaving(true);
    setError("");
    try {
      const resp = await fetch(`/api/v1/reports/${id}`, {
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
  if (!report) return null;

  return (
    <div style={{ maxWidth: 600, margin: "2rem auto", background: "#fff", borderRadius: 10, padding: 24 }}>
      <h2>Edytuj zgłoszenie</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label>Tytuł:<br />
            <input
              type="text"
              name="title"
              value={form.title}
              onChange={handleChange}
              required
              style={{ width: "100%" }}
            />
          </label>
        </div>
        <div>
          <label>Opis:<br />
            <textarea
              name="description"
              value={form.description}
              onChange={handleChange}
              required
              style={{ width: "100%", minHeight: 80 }}
            />
          </label>
        </div>
        <div>
          <label>Kategoria:<br />
            <select name="categoryId" value={form.categoryId} onChange={handleChange} required>
              <option value="">-- wybierz --</option>
              {categories.map(c => (
                <option key={c.id} value={c.id}>{c.name}</option>
              ))}
            </select>
          </label>
        </div>
        <div>
          <label>Status:<br />
            <select name="statusId" value={form.statusId} onChange={handleChange} required>
              <option value="">-- wybierz --</option>
              {statuses.map(s => (
                <option key={s.id} value={s.id}>{s.label}</option>
              ))}
            </select>
          </label>
        </div>
        <div>
          <label>Szerokość geo:<br />
            <input
              type="text"
              name="latitude"
              value={form.latitude || ""}
              onChange={handleChange}
              style={{ width: "100%" }}
            />
          </label>
        </div>
        <div>
          <label>Długość geo:<br />
            <input
              type="text"
              name="longitude"
              value={form.longitude || ""}
              onChange={handleChange}
              style={{ width: "100%" }}
            />
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
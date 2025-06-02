import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

export default function AdminReportList() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    setLoading(true);
    fetch('/api/v1/reports', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("token")}`,
        Accept: "application/json"
      },
      credentials: "include"
    })
      .then(res => res.json())
      .then(data => {
        setReports(data.data || []);
        setError(null);
      })
      .catch(err => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  return (
    <div>
      <h2>Lista zgłoszeń</h2>
      {loading ? (
        <div>Ładowanie...</div>
      ) : error ? (
        <div className="error-text">{error}</div>
      ) : reports.length === 0 ? (
        <p>Brak zgłoszeń.</p>
      ) : (
        <ul className="reports-list">
          {reports.map(r => (
            <li key={r.id}>
              <strong>{r.title}</strong><br />
              <span>{r.description}</span><br />
              <small>
                {new Date(r.createdAt).toLocaleString()} | Kategoria: {r.category?.name} | Status: {r.status?.label}
              </small>
              <button style={{ marginLeft: 16 }} onClick={() => navigate(`/admin/edit-report/${r.id}`)}>
                Edytuj
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
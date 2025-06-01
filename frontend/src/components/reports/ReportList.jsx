import React, { useEffect, useState } from "react";
import { fetchReports } from "../../api/reports.js";
import "../../styles/ReportList.css";

export default function ReportList() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState(null);

  useEffect(() => {
    setLoading(true);
    fetchReports()
      .then(data => {
        setReports(data.data || []);
        setError(null);
      })
      .catch(err => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div>Ładowanie...</div>;
  if (error)   return <div className="error-text">{error}</div>;
  if (reports.length === 0) return <p>Brak zgłoszeń.</p>;

  return (
    <ul className="reports-list">
      {reports.map(r => (
        <li key={r.id}>
          <strong>{r.title}</strong><br />
          <span>{r.description}</span><br />
          <small>
            {new Date(r.createdAt).toLocaleString()} | Kategoria: {r.category?.name} | Status: {r.status?.label}
          </small>
        </li>
      ))}
    </ul>
  );
}

import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { fetchReports } from "../../api/reports.js";
import "../../styles/ReportList.css";
import SidebarMenu from "../SidebarMenu";

export default function ReportList() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState(null);
  const navigate = useNavigate();

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

  return (
    <SidebarMenu>
      {loading ? (
        <div>Ładowanie...</div>
      ) : error ? (
        <div className="error-text">{error}</div>
      ) : reports.length === 0 ? (
        <p>Brak zgłoszeń.</p>
      ) : (
        <ul className="reports-list">
          {reports.map(r => (
            <li
              key={r.id}
              style={{ cursor: "pointer" }}
              onClick={() => navigate(`/reports/${r.id}`)}
            >
              <strong>{r.title}</strong><br />
              <span>{r.description}</span><br />
              <small>
                {new Date(r.createdAt).toLocaleString()} | Kategoria: {r.category?.name} | Status: {r.status?.label}
              </small>
            </li>
          ))}
        </ul>
      )}
    </SidebarMenu>
  );
}

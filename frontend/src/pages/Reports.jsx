import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

export default function Reports() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  const fetchReports = async () => {
    setLoading(true);
    try {
      const response = await fetch("http://localhost:8000/api/reports");
      const data = await response.json();
      setReports(data);
    } catch (err) {
      console.error("Error fetching reports:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchReports();
  }, []);

  return (
    <div style={{ padding: "30px" }}>
      <h2>Lista zgłoszeń</h2>
      <button onClick={fetchReports}>🔄 Odśwież zgłoszenia</button>
      <button onClick={() => navigate("/dashboard")} style={{ marginLeft: "10px" }}>
        Powrót
      </button>
      {loading ? (
        <p>Ładowanie...</p>
      ) : (
        <ul>
          {reports.map((report) => (
            <li key={report.id}>
              <strong>{report.title}</strong> – {report.status} <br />
              Zgłoszone przez: {report.user} ({report.createdAt})
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import Loading from "../components/Loading";

export default function Reports() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  const fetchReports = async () => {
    setLoading(true);
    try {
      const response = await fetch("http://localhost:8000/api/report", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error("Unauthorized");
        }
        if (response.status === 403) {
          throw new Error("Forbidden");
        }
        throw new Error("Failed to fetch reports");
      }

      const data = await response.json();
      setReports(data);
    } catch (err) {
      console.error("Error fetching reports:", err.message);

      if (err.message === "Unauthorized") {
        localStorage.removeItem("token");
        navigate("/login");
      } else {
        setError(err);
      }
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchReports();
  }, []);

  if (loading) return <Loading />;

  if (error) throw error;
  return (
    <div style={{ padding: "30px" }}>
      <h2>Lista zgłoszeń</h2>
      <button onClick={fetchReports}>🔄 Odśwież zgłoszenia</button>
      <button onClick={() => navigate("/dashboard")} style={{ marginLeft: "10px" }}>
        Powrót
      </button>
      <ul>
        {reports.map((report) => (
          <li key={report.id}>
            <strong>{report.title}</strong> – {report.status} <br />
            Zgłoszone przez: {report.user} ({report.createdAt})
          </li>
        ))}
      </ul>
    </div>
  );
}

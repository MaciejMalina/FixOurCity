import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { fetchReports } from "../../api/reports.js";
import "../../styles/ReportList.css";
import SidebarMenu from "../SidebarMenu";
import Loading from "../ui/Loading";

export default function ReportList() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState(null);
  const [categories, setCategories] = useState([]);
  const [statuses, setStatuses] = useState([]);
  const [filters, setFilters] = useState({
    category: "",
    status: "",
    title: ""
  });
  const navigate = useNavigate();

  useEffect(() => {
    fetch("/api/v1/categories")
      .then(res => res.json())
      .then(data => setCategories(data.data || []));
    fetch("/api/v1/statuses")
      .then(res => res.json())
      .then(data => setStatuses(data.data || []));
  }, []);

  useEffect(() => {
    setLoading(true);
    let params = [];
    if (filters.category) params.push(`category=${filters.category}`);
    if (filters.status) params.push(`status=${filters.status}`);
    if (filters.title) params.push(`title=${encodeURIComponent(filters.title)}`);
    const query = params.length ? "?" + params.join("&") : "";
    fetchReports({ page: 1, limit: 20, query })
      .then(data => {
        setReports(data.data || []);
        setError(null);
      })
      .catch(err => {
        if (err.message.includes("401")) {
          setError("Twoja sesja wygasła. Zaloguj się ponownie.");
          setTimeout(() => navigate("/login"), 2000);
        } else {
          setError(err.message);
        }
      })
      .finally(() => setLoading(false));
  }, [filters, navigate]);

  const handleFilterChange = e => {
    const { name, value } = e.target;
    setFilters(f => ({ ...f, [name]: value }));
  };

  return (
    <SidebarMenu>
      <div className="report-list__filters" style={{ maxWidth: 800, margin: "1rem auto", background: "#f7f9fa", borderRadius: 8, padding: "1rem" }}>
        <form style={{ display: "flex", gap: 16, flexWrap: "wrap", alignItems: "center" }} onSubmit={e => e.preventDefault()}>
          <select name="category" value={filters.category} onChange={handleFilterChange}>
            <option value="">Wszystkie kategorie</option>
            {categories.map(c => (
              <option key={c.id} value={c.id}>{c.name}</option>
            ))}
          </select>
          <select name="status" value={filters.status} onChange={handleFilterChange}>
            <option value="">Wszystkie statusy</option>
            {statuses.map(s => (
              <option key={s.id} value={s.id}>{s.label}</option>
            ))}
          </select>
          <input
            type="text"
            name="title"
            placeholder="Szukaj po tytule"
            value={filters.title}
            onChange={handleFilterChange}
            style={{ flex: 1, minWidth: 120 }}
          />
          <button type="button" onClick={() => setFilters({ category: "", status: "", title: "" })}>
            Wyczyść filtry
          </button>
        </form>
      </div>
      {loading ? (
        <Loading />
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

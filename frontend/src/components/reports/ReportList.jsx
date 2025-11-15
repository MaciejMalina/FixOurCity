import React, { useEffect, useState, useMemo } from "react";
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
  const [statuses, setStatuses]     = useState([]);

  const [filters, setFilters] = useState({
    category: "",
    status: "",
    title: "",
    sort: "createdAt",
    order: "DESC"
  });

  const navigate = useNavigate();

  useEffect(() => {
    fetch("/api/v1/categories")
      .then((res) => res.json())
      .then((data) => setCategories(data.data || []))
      .catch(() => {});

    fetch("/api/v1/statuses")
      .then((res) => res.json())
      .then((data) => setStatuses(data.data || []))
      .catch(() => {});
  }, []);

  useEffect(() => {
    setLoading(true);
    fetchReports({ page: 1, limit: 50, query: "" })
      .then((data) => {
        setReports(data.data || []);
        setError(null);
      })
      .catch((err) => {
        if (err.message.includes("401")) {
          setError("Twoja sesja wygasła. Zaloguj się ponownie.");
          setTimeout(() => navigate("/login"), 2000);
        } else {
          setError(err.message);
        }
      })
      .finally(() => setLoading(false));
  }, [navigate]);

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters((f) => ({ ...f, [name]: value }));
  };

  const handleClearFilters = () => {
    setFilters({
      category: "",
      status: "",
      title: "",
      sort: "createdAt",
      order: "DESC"
    });
  };

  const filteredReports = useMemo(() => {
    let result = [...reports];

    if (filters.category) {
      const catId = Number(filters.category);
      result = result.filter((r) => r.category?.id === catId);
    }

    if (filters.status) {
      const stId = Number(filters.status);
      result = result.filter((r) => r.status?.id === stId);
    }

    if (filters.title.trim() !== "") {
      const q = filters.title.trim().toLowerCase();
      result = result.filter((r) =>
        r.title.toLowerCase().includes(q)
      );
    }

    result.sort((a, b) => {
      let cmp = 0;

      if (filters.sort === "createdAt") {
        const da = new Date(a.createdAt).getTime();
        const db = new Date(b.createdAt).getTime();
        cmp = da - db;
      } else if (filters.sort === "title") {
        cmp = a.title.localeCompare(b.title);
      }

      return filters.order === "ASC" ? cmp : -cmp;
    });

    return result;
  }, [reports, filters]);

  return (
    <SidebarMenu>
      <div className="reports-page">
        {/* FILTRY */}
        <div className="report-list__filters">
          <form onSubmit={(e) => e.preventDefault()}>
            <select
              name="category"
              value={filters.category}
              onChange={handleFilterChange}
            >
              <option value="">Wszystkie kategorie</option>
              {categories.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.name}
                </option>
              ))}
            </select>

            <select
              name="status"
              value={filters.status}
              onChange={handleFilterChange}
            >
              <option value="">Wszystkie statusy</option>
              {statuses.map((s) => (
                <option key={s.id} value={s.id}>
                  {s.label}
                </option>
              ))}
            </select>

            <input
              type="text"
              name="title"
              placeholder="Szukaj po tytule…"
              value={filters.title}
              onChange={handleFilterChange}
            />

            <select
              name="sort"
              value={filters.sort}
              onChange={handleFilterChange}
            >
              <option value="createdAt">Data utworzenia</option>
              <option value="title">Tytuł</option>
            </select>

            <select
              name="order"
              value={filters.order}
              onChange={handleFilterChange}
            >
              <option value="DESC">Malejąco</option>
              <option value="ASC">Rosnąco</option>
            </select>

            <button type="button" onClick={handleClearFilters}>
              Wyczyść filtry
            </button>
          </form>
        </div>

        {/* LISTA */}
        {loading ? (
          <Loading />
        ) : error ? (
          <div className="error-text">{error}</div>
        ) : filteredReports.length === 0 ? (
          <p className="report-list__status">Brak zgłoszeń.</p>
        ) : (
          <ul className="reports-list">
            {filteredReports.map((r) => (
              <li
                key={r.id}
                className="report-item"
                onClick={() => navigate(`/reports/${r.id}`)}
              >
                <div className="report-main">
                  <div className="report-title">{r.title}</div>
                  <div className="report-description">
                    {r.description}
                  </div>
                  <div className="report-meta">
                    {new Date(r.createdAt).toLocaleString()} | Kategoria:{" "}
                    {r.category?.name} | Status: {r.status?.label}
                  </div>
                </div>
              </li>
            ))}
          </ul>
        )}
      </div>
    </SidebarMenu>
  );
}

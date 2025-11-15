import React, { useEffect, useState, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import Loading from "../ui/Loading";

export default function AdminReportList() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState(null);
  const navigate = useNavigate();

  const [filters, setFilters] = useState({
    categoryId: "",
    statusId: "",
    title: "",
    sort: "createdAt",
    order: "DESC",
  });

  useEffect(() => {
    setLoading(true);
    fetch("/api/v1/reports", {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("token")}`,
        Accept: "application/json",
      },
      credentials: "include",
    })
      .then((res) => {
        if (res.status === 401) {
          setError("Twoja sesja wygasła. Zaloguj się ponownie.");
          setTimeout(() => navigate("/login"), 2000);
          throw new Error("401");
        }
        return res.json();
      })
      .then((data) => {
        setReports(data.data || []);
        setError(null);
      })
      .catch((err) => {
        if (err.message !== "401") setError(err.message);
      })
      .finally(() => setLoading(false));
  }, [navigate]);

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters((f) => ({ ...f, [name]: value }));
  };

  const categories = useMemo(() => {
    const map = new Map();
    reports.forEach((r) => {
      if (r.category?.id && !map.has(r.category.id)) {
        map.set(r.category.id, r.category);
      }
    });
    return Array.from(map.values());
  }, [reports]);

  const statuses = useMemo(() => {
    const map = new Map();
    reports.forEach((r) => {
      if (r.status?.id && !map.has(r.status.id)) {
        map.set(r.status.id, r.status);
      }
    });
    return Array.from(map.values());
  }, [reports]);

  const filteredReports = useMemo(() => {
    let result = [...reports];

    if (filters.categoryId) {
      const catId = Number(filters.categoryId);
      result = result.filter((r) => r.category?.id === catId);
    }

    if (filters.statusId) {
      const stId = Number(filters.statusId);
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

  if (loading) {
    return (
      <div className="reports-page">
        <h2>Lista zgłoszeń</h2>
        <Loading />
      </div>
    );
  }

  return (
    <div className="reports-page">
      <h2>Lista zgłoszeń</h2>

      {error && <div className="error-text">{error}</div>}

      {!error && (
        <>
          {/* FILTRY */}
          <div className="report-list__filters">
            <form onSubmit={(e) => e.preventDefault()}>
              <select
                name="categoryId"
                value={filters.categoryId}
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
                name="statusId"
                value={filters.statusId}
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
                value={filters.title}
                onChange={handleFilterChange}
                placeholder="Szukaj po tytule…"
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
            </form>
          </div>

          {/* LISTA */}
          {filteredReports.length === 0 ? (
            <p className="report-list__status">
              Brak zgłoszeń pasujących do filtrów.
            </p>
          ) : (
            <ul className="reports-list">
              {filteredReports.map((r) => (
                <li
                  key={r.id}
                  className="report-item"
                  onClick={() => navigate(`/admin/edit-report/${r.id}`)}
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

                  <div className="report-actions">
                    <button
                      className="report-edit-btn"
                      onClick={(e) => {
                        e.stopPropagation();
                        navigate(`/admin/edit-report/${r.id}`);
                      }}
                    >
                      Edytuj
                    </button>
                  </div>
                </li>
              ))}
            </ul>
          )}
        </>
      )}
    </div>
  );
}

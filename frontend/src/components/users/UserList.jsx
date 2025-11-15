import React, { useEffect, useState, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import "../../styles/UserList.css";
import Loading from "../ui/Loading";

const API = "http://localhost:8000/api/v1";

export default function UsersList() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [approvingId, setApprovingId] = useState(null);
  const [error, setError] = useState("");
  const navigate = useNavigate();

  const [filters, setFilters] = useState({
    role: "",
    approved: "",
    query: "",
    sort: "email",
    order: "ASC",
    pendingFirst: true,
  });

  useEffect(() => {
    setLoading(true);
    fetch(`${API}/users`, {
      credentials: "include",
      headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
    })
      .then((res) => {
        if (!res.ok) throw new Error("Błąd pobierania użytkowników");
        return res.json();
      })
      .then((json) => {
        setUsers(json.data || json);
        setLoading(false);
      })
      .catch((e) => {
        setError(e.message);
        setLoading(false);
      });
  }, []);

  const handleFilterChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFilters((f) => ({
      ...f,
      [name]: type === "checkbox" ? checked : value,
    }));
  };

  const sortedUsers = useMemo(() => {
    let result = [...users];

    if (filters.role) {
      result = result.filter((u) => u.roles.includes(filters.role));
    }

    if (filters.approved === "approved") {
      result = result.filter((u) => u.approved);
    } else if (filters.approved === "pending") {
      result = result.filter((u) => !u.approved);
    }

    if (filters.query.trim() !== "") {
      const q = filters.query.trim().toLowerCase();
      result = result.filter((u) => {
        const email = (u.email || "").toLowerCase();
        const fn = (u.firstName || "").toLowerCase();
        const ln = (u.lastName || "").toLowerCase();
        return (
          email.includes(q) || fn.includes(q) || ln.includes(q)
        );
      });
    }

    result.sort((a, b) => {
      if (filters.pendingFirst) {
        const aPending = !a.approved;
        const bPending = !b.approved;
        if (aPending && !bPending) return -1;
        if (!aPending && bPending) return 1;
      }

      let va = "";
      let vb = "";

      switch (filters.sort) {
        case "firstName":
          va = (a.firstName || "").toLowerCase();
          vb = (b.firstName || "").toLowerCase();
          break;
        case "lastName":
          va = (a.lastName || "").toLowerCase();
          vb = (b.lastName || "").toLowerCase();
          break;
        case "email":
        default:
          va = (a.email || "").toLowerCase();
          vb = (b.email || "").toLowerCase();
      }

      let cmp = va.localeCompare(vb);
      if (filters.order === "DESC") cmp = -cmp;
      return cmp;
    });

    return result;
  }, [users, filters]);

  async function handleApprove(id) {
    setError("");
    setApprovingId(id);
    try {
      const resp = await fetch(`${API}/users/${id}/approve`, {
        method: "PATCH",
        credentials: "include",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
          "Content-Type": "application/json",
        },
      });
      if (!resp.ok) {
        const { error } = await resp.json().catch(() => ({}));
        throw new Error(error || "Nie udało się zatwierdzić użytkownika");
      }
      const updated = await resp.json();

      setUsers((prev) =>
        prev.map((u) =>
          u.id === id
            ? {
                ...u,
                approved: updated.approved,
                approvedAt: updated.approvedAt,
              }
            : u
        )
      );
    } catch (e) {
      setError(e.message);
    } finally {
      setApprovingId(null);
    }
  }

  if (loading) return <Loading />;

  return (
    <div className="user-list-container">
      <h2>Użytkownicy</h2>

      {error && <div className="user-list-error">{error}</div>}

      {/* Pasek filtrów*/}
      <div className="report-list__filters">
        <form onSubmit={(e) => e.preventDefault()}>
          <select
            name="role"
            value={filters.role}
            onChange={handleFilterChange}
          >
            <option value="">Wszystkie role</option>
            <option value="ROLE_USER">Tylko ROLE_USER</option>
            <option value="ROLE_ADMIN">Tylko ROLE_ADMIN</option>
          </select>

          <select
            name="approved"
            value={filters.approved}
            onChange={handleFilterChange}
          >
            <option value="">Wszyscy</option>
            <option value="pending">Tylko oczekujący</option>
            <option value="approved">Tylko zatwierdzeni</option>
          </select>

          <input
            type="text"
            name="query"
            value={filters.query}
            onChange={handleFilterChange}
            placeholder="Szukaj po emailu lub nazwisku…"
          />

          <select
            name="sort"
            value={filters.sort}
            onChange={handleFilterChange}
          >
            <option value="email">Email</option>
            <option value="firstName">Imię</option>
            <option value="lastName">Nazwisko</option>
          </select>

          <select
            name="order"
            value={filters.order}
            onChange={handleFilterChange}
          >
            <option value="ASC">Rosnąco (A–Z)</option>
            <option value="DESC">Malejąco (Z–A)</option>
          </select>

          <label style={{ display: "flex", alignItems: "center", gap: 4 }}>
            <input
              type="checkbox"
              name="pendingFirst"
              checked={filters.pendingFirst}
              onChange={handleFilterChange}
            />
            Najpierw do zatwierdzenia
          </label>
        </form>
      </div>

      {sortedUsers.length === 0 ? (
        <p className="users-empty">Brak użytkowników.</p>
      ) : (
        <ul className="user-list">
          {sortedUsers.map((u) => (
            <li
              key={u.id}
              className="user-item"
              onClick={() => navigate(`/admin/edit-user/${u.id}`)}
            >
              <div className="user-main">
                <div className="user-primary-line">
                  <span className="user-email">{u.email}</span>
                  <span className="user-name">
                    {u.firstName} {u.lastName}
                  </span>
                </div>
                <div className="user-secondary-line">
                  <span className="user-roles">
                    {u.roles.join(", ")}
                  </span>

                  <span
                    className={
                      "user-approved-badge " +
                      (u.approved ? "approved" : "pending")
                    }
                  >
                    {u.approved
                      ? "Zatwierdzony"
                      : "Oczekuje na zatwierdzenie"}
                  </span>
                </div>
              </div>

              <div className="user-actions">
                {!u.approved && !u.roles.includes("ROLE_ADMIN") && (
                  <button
                    className="user-approve-btn"
                    disabled={approvingId === u.id}
                    onClick={(e) => {
                      e.stopPropagation();
                      handleApprove(u.id);
                    }}
                  >
                    {approvingId === u.id
                      ? "Zatwierdzanie..."
                      : "Zatwierdź"}
                  </button>
                )}

                <button
                  className="user-edit-btn"
                  onClick={(e) => {
                    e.stopPropagation();
                    navigate(`/admin/edit-user/${u.id}`);
                  }}
                >
                  Edytuj
                </button>
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

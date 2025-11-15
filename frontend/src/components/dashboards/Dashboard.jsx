import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { MapContainer, TileLayer, Marker, Popup } from "react-leaflet";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import "../../styles/Dashboard.css";
import Loading from "../ui/Loading";

import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

export default function Dashboard() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();
  const token = localStorage.getItem("token");

  useEffect(() => {
    const fetchReports = async () => {
      setLoading(true);
      try {
        const res = await fetch("/api/v1/reports?page=1&limit=1000", {
          credentials: "include",
          headers: {
            Accept: "application/json",
            Authorization: `Bearer ${token}`,
          },
        });
        if (!res.ok) throw new Error(`Fetch error ${res.status}`);
        const { data } = await res.json();

        const filtered = (data || []).filter(
          (r) =>
            r.status?.label === "Nowe" ||
            r.status?.label === "W trakcie realizacji"
        );
        setReports(filtered);
      } catch (e) {
        console.error("Fetch reports error:", e);
        setError(e);
        if (e.message.includes("401")) {
          localStorage.clear();
          navigate("/login");
        }
      } finally {
        setLoading(false);
      }
    };

    fetchReports();
  }, [navigate, token]);

  if (loading) return <Loading />;
  if (error)
    return <div className="dashboard-error">Coś poszło nie tak :(</div>;

  const recent = reports.slice(0, 5);

  return (
    <div className="dashboard-page">
      <aside className="dashboard-page__left">
        <div className="dashboard-page__logo">
          <img src="/logo.png" alt="FixOurCity" />
        </div>

        <div className="dashboard-page__welcome">
          <h1>FixOurCity</h1>
        </div>

        <div className="dashboard-page__recent">
          <h2>Ostatnie zgłoszenia</h2>
          {recent.length === 0 ? (
            <p className="dashboard-page__recent-empty">Brak zgłoszeń.</p>
          ) : (
            <ul>
              {recent.map((r) => (
                <li
                  key={r.id}
                  onClick={() => navigate(`/reports/${r.id}`)}
                  title={r.title}
                >
                  <strong>{r.title}</strong>
                  <span>{r.status?.label}</span>
                </li>
              ))}
            </ul>
          )}
        </div>

        <div className="dashboard-page__actions">
          <button onClick={() => navigate("/reports")}>Lista zgłoszeń</button>
          <button onClick={() => navigate("/reports/new")}>
            Dodaj nowe zgłoszenie
          </button>
          <button onClick={() => navigate("/admin")}>Panel administratora</button>
        </div>

        <button
          className="dashboard-page__logout"
          onClick={() => {
            localStorage.clear();
            navigate("/login");
          }}
        >
          Wyloguj
        </button>
      </aside>

      <main className="dashboard-page__right">
        <header className="dashboard-page__header">
          <h2>Mapa zgłoszeń</h2>
        </header>

        <section className="dashboard-page__map-card">
          <MapContainer
            center={[50.06465, 19.94498]}
            zoom={12}
            className="dashboard-page__map-leaflet"
          >
            <TileLayer
              attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/">CARTO</a>'
              url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
            />
            {reports.map(
              (r) =>
                r.latitude &&
                r.longitude && (
                  <Marker
                    key={r.id}
                    position={[
                      parseFloat(r.latitude),
                      parseFloat(r.longitude),
                    ]}
                  >
                    <Popup>
                      <div className="dashboard-popup">
                        <strong>{r.title}</strong>
                        <span>{r.description?.slice(0, 80)}…</span>
                        <button
                          className="dashboard-popup__btn"
                          onClick={() => navigate(`/reports/${r.id}`)}
                        >
                          Szczegóły
                        </button>
                      </div>
                    </Popup>
                  </Marker>
                )
            )}
          </MapContainer>
        </section>
      </main>
    </div>
  );
}

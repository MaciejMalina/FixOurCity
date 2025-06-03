import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { MapContainer, TileLayer, Marker, Popup } from "react-leaflet";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import "../../styles/ReportDetails.css";
import Loading from "../ui/Loading";

import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

export default function ReportDetails() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [report, setReport] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    setLoading(true);
    fetch(`/api/v1/reports/${id}`, {
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
        if (!res.ok) throw new Error("Nie znaleziono zgłoszenia");
        return res.json();
      })
      .then((data) => {
        setReport(data);
        setError("");
      })
      .catch((e) => {
        if (e.message !== "401") setError(e.message);
      })
      .finally(() => setLoading(false));
  }, [id, navigate]);

  if (loading) return <Loading />;
  if (error) return (
    <div style={{ textAlign: "center", margin: "2rem", color: "crimson" }}>
      {error} <br />
      <button onClick={() => navigate("/reports")}>Powrót do listy</button>
    </div>
  );
  if (!report) return null;

  return (
    <div className="report-details-container">
      <div className="back-button-wrapper">
        <button
          type="button"
          className="back-button"
          onClick={() => navigate(-1)}>
          ← Wróć
        </button>
      </div>
      <h1>{report.title}</h1>
      <div className="details-meta">
        <span className="details-category">{report.category?.name}</span>
        <span className={`details-status details-status--${report.status?.label.replace(/\s/g, '-').toLowerCase()}`}>
          {report.status?.label}
        </span>
        <span className="details-date">{new Date(report.createdAt).toLocaleString()}</span>
      </div>
      <div className="details-description">{report.description}</div>
      {report.images && report.images.length > 0 && (
        <div className="details-images">
          {report.images.map(img => (
            <img key={img.id} src={img.url} alt="Załącznik" />
          ))}
        </div>
      )}
      {report.latitude && report.longitude && (
        <div className="details-map">
          <MapContainer
            center={[parseFloat(report.latitude), parseFloat(report.longitude)]}
            zoom={15}
            style={{ width: "100%", height: "250px", borderRadius: "10px" }}
            scrollWheelZoom={false}
          >
            <TileLayer
              attribution='&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <Marker position={[parseFloat(report.latitude), parseFloat(report.longitude)]}>
              <Popup>{report.title}</Popup>
            </Marker>
          </MapContainer>
        </div>
      )}
      {report.comments && report.comments.length > 0 && (
        <div className="details-comments">
          <h3>Komentarze</h3>
          <ul>
            {report.comments.map(c => (
              <li key={c.id}>
                <strong>{c.author}</strong> ({new Date(c.createdAt).toLocaleString()}):<br />
                {c.content}
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}
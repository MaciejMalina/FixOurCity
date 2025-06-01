// src/components/ReportForm.jsx

import React, { useState, useRef } from "react";
import { MapContainer, TileLayer, Marker, useMapEvents } from "react-leaflet";
import "leaflet/dist/leaflet.css";
import L from "leaflet";

// Fix default Marker icon paths for react-leaflet
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl:
    "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png",
  iconUrl:
    "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png",
  shadowUrl:
    "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png",
});

// Component to handle map clicks and set a marker
function LocationSelector({ position, setPosition }) {
  useMapEvents({
    click(e) {
      setPosition([e.latlng.lat, e.latlng.lng]);
    },
  });
  return position ? <Marker position={position} /> : null;
}

export default function ReportForm() {
  // Form state
  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");
  const [imageFile, setImageFile] = useState(null);
  const [imagePreview, setImagePreview] = useState(null);
  const [position, setPosition] = useState(null);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState(null);
  const [successMsg, setSuccessMsg] = useState(null);

  const fileInputRef = useRef();

  // Handle file selection and preview
  const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    setImageFile(file);
    const reader = new FileReader();
    reader.onloadend = () => {
      setImagePreview(reader.result);
    };
    reader.readAsDataURL(file);
  };

  // Handle form submission
  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setSuccessMsg(null);

    if (!title.trim() || !description.trim()) {
      setError("Tytuł i opis są wymagane.");
      return;
    }
    if (!position) {
      setError("Proszę wybrać lokalizację na mapie.");
      return;
    }
    setSubmitting(true);

    try {
      // 1) Utworzenie nowego reportu
      const reportPayload = {
        title: title.trim(),
        description: description.trim(),
      };
      const reportRes = await fetch("/api/v1/reports", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(reportPayload),
      });
      if (!reportRes.ok) {
        const data = await reportRes.json();
        throw new Error(data.error || "Błąd tworzenia zgłoszenia");
      }
      const createdReport = await reportRes.json();
      const reportId = createdReport.id;

      // 2) Jeśli wybrano plik, to symulujemy „upload” – tu przykładowo
      // wysyłamy Base64 do endpointu, który spodziewa się URL-a.  
      // W realnej aplikacji należałoby wysłać plik do storage i pobrać URL.
      if (imagePreview) {
        // W tym przykładzie zakładamy, że backend: POST /api/v1/images przyjmuje { reportId, url }
        const imagePayload = {
          reportId,
          url: imagePreview,
        };
        const imageRes = await fetch("/api/v1/images", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(imagePayload),
        });
        if (!imageRes.ok) {
          const data = await imageRes.json();
          console.warn("Obraz nie został zapisany:", data.error);
        }
      }

      // 3) Zapisanie współrzędnych w backendzie – zakładamy, że report ma endpoint PATCH na koordynaty
      // Jeżeli w backendzie nie ma kolumn na lat/lng, to ten krok można pominąć lub zachować tylko frontowy marker.
      await fetch(`/api/v1/reports/${reportId}`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ latitude: position[0], longitude: position[1] }),
      });

      setSuccessMsg("Zgłoszenie zostało utworzone pomyślnie!");
      // Wyczyszczenie formularza
      setTitle("");
      setDescription("");
      setImageFile(null);
      setImagePreview(null);
      setPosition(null);
      fileInputRef.current.value = "";
    } catch (err) {
      console.error(err);
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div
      style={{
        maxWidth: "900px",
        margin: "20px auto",
        background: "#f0f0f0",
        borderRadius: "8px",
        padding: "20px",
      }}
    >
      <form onSubmit={handleSubmit}>
        {/* Sekcja wgrania zdjęcia i opis */}
        <div style={{ display: "flex", gap: "16px", marginBottom: "20px" }}>
          <div
            style={{
              flex: 1,
              border: "2px dashed #555",
              borderRadius: "8px",
              position: "relative",
              padding: "10px",
              textAlign: "center",
              background: "#fff",
            }}
          >
            <input
              type="file"
              accept="image/*"
              style={{
                opacity: 0,
                width: "100%",
                height: "100%",
                position: "absolute",
                top: 0,
                left: 0,
                cursor: "pointer",
              }}
              onChange={handleFileChange}
              ref={fileInputRef}
            />
            {imagePreview ? (
              <img
                src={imagePreview}
                alt="Podgląd"
                style={{
                  maxWidth: "100%",
                  maxHeight: "200px",
                  objectFit: "cover",
                }}
              />
            ) : (
              <div style={{ padding: "40px 0" }}>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  width="48"
                  height="48"
                  style={{ color: "#555" }}
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M4 16v1a1 1 0 001 1h3m10-2v1a1 1 0 001 1h3m0-4V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8m16 0H4m5-4l3-3 3 3m-3-3v12"
                  />
                </svg>
                <p style={{ color: "#555", marginTop: "8px" }}>
                  Załącz zdjęcie
                </p>
              </div>
            )}
          </div>
          <div
            style={{
              flex: 1,
              background: "#fff",
              borderRadius: "8px",
              padding: "16px",
              boxShadow: "0 2px 4px rgba(0,0,0,0.1)",
              fontSize: "14px",
              lineHeight: "1.6",
              color: "#333",
            }}
          >
            <div style={{ marginTop: "10px" }}>
              <label
                htmlFor="title"
                style={{ display: "block", fontWeight: "bold", marginBottom: "4px" }}
              >
                Tytuł:
              </label>
              <input
                type="text"
                id="title"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                style={{
                  width: "100%",
                  padding: "8px",
                  border: "1px solid #ccc",
                  borderRadius: "4px",
                  marginBottom: "12px",
                }}
                placeholder="Krótki tytuł zgłoszenia"
                required
              />
              <label
                htmlFor="description"
                style={{ display: "block", fontWeight: "bold", marginBottom: "4px" }}
              >
                Opis:
              </label>
              <textarea
                id="description"
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                style={{
                  width: "100%",
                  height: "100px",
                  padding: "8px",
                  border: "1px solid #ccc",
                  borderRadius: "4px",
                  resize: "vertical",
                }}
                placeholder="Szczegółowy opis problemu"
                required
              />
            </div>
          </div>
        </div>

        {/* Sekcja mapy */}
        <div
          style={{
            width: "100%",
            height: "400px",
            borderRadius: "8px",
            overflow: "hidden",
            marginBottom: "20px",
          }}
        >
          <MapContainer
            center={[50.06465, 19.94498]} // domyślnie Kraków
            zoom={13}
            style={{ width: "100%", height: "100%" }}
          >
            <TileLayer
              attribution='&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <LocationSelector position={position} setPosition={setPosition} />
          </MapContainer>
          <p style={{ marginTop: "8px", fontSize: "14px", color: "#555" }}>
            Kliknij na mapie, aby zaznaczyć lokalizację problemu.
          </p>
        </div>

        {error && (
          <div style={{ color: "crimson", marginBottom: "12px" }}>{error}</div>
        )}
        {successMsg && (
          <div style={{ color: "green", marginBottom: "12px" }}>{successMsg}</div>
        )}

        {/* Przycisk Wyślij */}
        <div style={{ textAlign: "center" }}>
          <button
            type="submit"
            disabled={submitting}
            style={{
              background: "#2c9c87",
              color: "#fff",
              padding: "14px 40px",
              fontSize: "18px",
              border: "none",
              borderRadius: "8px",
              cursor: submitting ? "not-allowed" : "pointer",
            }}
          >
            {submitting ? "Trwa wysyłanie..." : "Wyślij"}
          </button>
        </div>
      </form>
    </div>
  );
}

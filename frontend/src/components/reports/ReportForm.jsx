// frontend/src/components/ReportForm.jsx

import React, { useState, useEffect, useRef } from "react";
import { MapContainer, TileLayer, Marker, useMapEvents } from "react-leaflet";
import "leaflet/dist/leaflet.css";
import L from "leaflet";
import "../../styles/ReportForm.css"; // <- importujemy osobny plik CSS

// Konfiguracja ikon Leaflet
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl:
    "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png",
  iconUrl:
    "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png",
  shadowUrl:
    "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png",
});

/**
 * Komponent pomocniczy do zaznaczania lokalizacji na mapie
 */
function LocationSelector({ position, setPosition }) {
  useMapEvents({
    click(e) {
      setPosition([e.latlng.lat, e.latlng.lng]);
    },
  });
  return position ? <Marker position={position} /> : null;
}

export default function ReportForm() {
  // -- STANY FORMULARZA --
  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");
  const [categories, setCategories] = useState([]); // pobrane z backendu
  const [statuses, setStatuses] = useState([]);     // pobrane z backendu
  const [selectedCategory, setSelectedCategory] = useState("");
  const [selectedStatus, setSelectedStatus]     = useState("");

  const [imageFile, setImageFile]       = useState(null);
  const [imagePreview, setImagePreview] = useState(null);
  const [position, setPosition]         = useState(null);

  const [submitting, setSubmitting]   = useState(false);
  const [error, setError]             = useState(null);
  const [successMsg, setSuccessMsg]   = useState(null);

  const fileInputRef = useRef();

  // **PO ZAMONTOWANIU**: pobieramy kategorie i statusy
  useEffect(() => {
    // --- POBIERANIE KATEGORII ---
    fetch("/api/v1/categories?page=1&limit=100")
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then((data) => {
        setCategories(data.data);
        if (data.data.length > 0) {
          setSelectedCategory(data.data[0].id.toString());
        }
      })
      .catch(() => console.warn("Nie udało się pobrać kategorii"));

    // --- POBIERANIE STATUSÓW ---
    fetch("/api/v1/statuses?page=1&limit=100")
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then((data) => {
        setStatuses(data.data);
        if (data.data.length > 0) {
          setSelectedStatus(data.data[0].id.toString());
        }
      })
      .catch(() => console.warn("Nie udało się pobrać statusów"));
  }, []);

  // Obsługa zmiany pliku (zapis pliku + generowanie podglądu)
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

  // Obsługa submita całego formularza
  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setSuccessMsg(null);

    // --- WALIDACJA ---
    if (!title.trim() || !description.trim()) {
      setError("Tytuł i opis są wymagane.");
      return;
    }
    if (!selectedCategory) {
      setError("Proszę wybrać kategorię.");
      return;
    }
    if (!selectedStatus) {
      setError("Proszę wybrać status.");
      return;
    }
    if (!position) {
      setError("Proszę wskazać lokalizację na mapie.");
      return;
    }

    setSubmitting(true);
    try {
      // 1) Tworzymy raport
      const reportPayload = {
        title:       title.trim(),
        description: description.trim(),
        categoryId:  parseInt(selectedCategory, 10),
        statusId:    parseInt(selectedStatus, 10),
        latitude:    position[0],
        longitude:   position[1],
      };
      const reportRes = await fetch("/api/v1/reports", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(reportPayload),
      });
      if (!reportRes.ok) {
        let errMsg = `Błąd serwera (${reportRes.status})`;
        try {
          const data = await reportRes.json();
          if (data.error) errMsg = data.error;
        } catch {}
        throw new Error(errMsg);
      }
      const createdReport = await reportRes.json();
      const reportId = createdReport.id;

      // 2) Dodanie zdjęcia (jeżeli wybrano)
      if (imagePreview) {
        // Uwaga: w prawdziwej aplikacji upload pliku lepiej robić przez multipart/form-data.
        const imagePayload = {
          reportId,
          url: imagePreview
        };
        const imgRes = await fetch("/api/v1/images", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(imagePayload),
        });
        if (!imgRes.ok) {
          console.warn("Nie udało się dodać obrazu:", await imgRes.text());
        }
      }

      // Sukces
      setSuccessMsg("Zgłoszenie zostało utworzone pomyślnie!");
      // Czyszczenie formularza
      setTitle("");
      setDescription("");
      setImageFile(null);
      setImagePreview(null);
      setPosition(null);
      fileInputRef.current.value = "";
      if (categories.length > 0) setSelectedCategory(categories[0].id.toString());
      if (statuses.length > 0) setSelectedStatus(statuses[0].id.toString());
    } catch (err) {
      console.error(err);
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="report-form-container">
      <h2 className="form-heading">Dodaj nowe zgłoszenie</h2>

      <form onSubmit={handleSubmit} className="report-form">
        {/* ----------------- UPLOAD ZDJĘCIA + DANE OPISOWE ----------------- */}
        <div className="upload-and-info-wrapper">
          {/* --- lewa kolumna: upload zdjęcia --- */}
          <div className="photo-upload-box">
            <input
              type="file"
              accept="image/*"
              className="photo-upload-input"
              onChange={handleFileChange}
              ref={fileInputRef}
            />
            {imagePreview ? (
              <img
                src={imagePreview}
                alt="Podgląd"
                className="image-preview"
              />
            ) : (
              <div className="upload-placeholder">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  width="48"
                  height="48"
                  className="upload-icon"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M4 16v1a1 1 0 001 1h3m10-2v1a1 1 0 001 1h3m0-4V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8m16 0H4m5-4l3-3 3 3m-3-3v12"
                  />
                </svg>
                <p className="upload-text">Załącz zdjęcie</p>
              </div>
            )}
          </div>

          {/* --- prawa kolumna: opis, tytuł, opis, kategoria, status --- */}
          <div className="description-box">
            <h3 className="description-heading">Opis</h3>
            <p className="description-text">
              Podaj szczegółowe informacje o problemie. Im więcej szczegółów,
              tym szybciej służby mogą zareagować. Możesz wpisać lokalizację,
              czas zdarzenia, i inne istotne uwagi.
            </p>

            <div className="form-field">
              <label htmlFor="title" className="field-label">
                Tytuł:
              </label>
              <input
                type="text"
                id="title"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                className="field-input"
                placeholder="Krótki tytuł zgłoszenia"
                required
              />

              <label htmlFor="description" className="field-label">
                Opis:
              </label>
              <textarea
                id="description"
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                className="field-textarea"
                placeholder="Szczegółowy opis problemu"
                required
              />

              <label htmlFor="category" className="field-label">
                Kategoria:
              </label>
              <select
                id="category"
                value={selectedCategory}
                onChange={(e) => setSelectedCategory(e.target.value)}
                className="field-select"
              >
                <option value="">-- Wybierz kategorię --</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.id.toString()}>
                    {c.name}
                  </option>
                ))}
              </select>

              <label htmlFor="status" className="field-label">
                Status:
              </label>
              <select
                id="status"
                value={selectedStatus}
                onChange={(e) => setSelectedStatus(e.target.value)}
                className="field-select"
              >
                <option value="">-- Wybierz status --</option>
                {statuses.map((s) => (
                  <option key={s.id} value={s.id.toString()}>
                    {s.label}
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>

        {/* ----------------- MAPA ----------------- */}
        <div className="map-wrapper">
          <MapContainer
            center={[50.06465, 19.94498]}
            zoom={13}
            className="leaflet-map"
          >
            <TileLayer
              attribution='&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <LocationSelector position={position} setPosition={setPosition} />
          </MapContainer>
          <p className="map-instruction">
            Kliknij na mapie, aby zaznaczyć współrzędne problemu.
          </p>
        </div>

        {/* ----------------- KOMUNIKATY BŁĘDÓW / SUKCESU ----------------- */}
        {error && <div className="error-text">{error}</div>}
        {successMsg && <div className="success-text">{successMsg}</div>}

        {/* ----------------- PRZYCISK WYŚLIJ ----------------- */}
        <div className="submit-container">
          <button
            type="submit"
            disabled={submitting}
            className="submit-button"
          >
            {submitting ? "Trwa wysyłanie..." : "Wyślij"}
          </button>
        </div>
      </form>
    </div>
  );
}

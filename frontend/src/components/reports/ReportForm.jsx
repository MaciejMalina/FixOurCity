import React, { useEffect, useState, useRef } from "react";
import { MapContainer, TileLayer, Marker, useMapEvents } from "react-leaflet";
import { useNavigate } from "react-router-dom";
import "leaflet/dist/leaflet.css";
import L from "leaflet";
import "../../styles/ReportForm.css";

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl:
    "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png",
  iconUrl:
    "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png",
  shadowUrl:
    "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png",
});

function LocationSelector({ position, setPosition }) {
  useMapEvents({
    click(e) {
      setPosition([e.latlng.lat, e.latlng.lng]);
    },
  });
  return position ? <Marker position={position} /> : null;
}

export default function ReportForm({ onSuccess }) {
  const navigate = useNavigate();
  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");
  const [categories, setCategories] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState("");
  const [, setImageFile] = useState(null);
  const [imagePreview, setImagePreview] = useState(null);
  const [position, setPosition] = useState([50.06465, 19.94498]);

  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState(null);
  const [successMsg, setSuccessMsg] = useState(null);

  const fileInputRef = useRef();

  useEffect(() => {
    fetch("/api/v1/categories?page=1&limit=100")
      .then((res) => {
        if (!res.ok) throw new Error("Błąd pobierania kategorii");
        return res.json();
      })
      .then((data) => {
        setCategories(data.data);
        if (data.data.length > 0) {
          setSelectedCategory(data.data[0].id.toString());
        }
      })
      .catch(() => setError("Nie udało się pobrać kategorii"));
  }, []);

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

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setSuccessMsg(null);
    setSubmitting(true);

    if (!title || !description || !selectedCategory || !position) {
      setError("Wszystkie pola są wymagane.");
      setSubmitting(false);
      return;
    }

    try {
      const reportPayload = {
        title: title.trim(),
        description: description.trim(),
        categoryId: parseInt(selectedCategory, 10),
        statusId: 1,
        latitude: position[0],
        longitude: position[1],
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
        } catch { //
          }
        throw new Error(errMsg);
      }
      const createdReport = await reportRes.json();
      const reportId = createdReport.id;

      if (imagePreview) {
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

      setSuccessMsg("Zgłoszenie zostało utworzone pomyślnie!");
      setTitle("");
      setDescription("");
      setImageFile(null);
      setImagePreview(null);
      setPosition([50.06465, 19.94498]);
      fileInputRef.current.value = "";
      if (categories.length > 0) setSelectedCategory(categories[0].id.toString());
      if (onSuccess) onSuccess();
    } catch (err) {
      console.error(err);
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="report-form-container">
      <div className="back-button-wrapper">
        <button
          type="button"
          className="back-button"
          onClick={() => navigate(-1)}>
          ← Wróć
        </button>
      </div>
      <h2 className="form-heading">Dodaj nowe zgłoszenie</h2>
      <form onSubmit={handleSubmit} className="report-form">
        <div className="upload-and-info-wrapper">
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
                <span className="upload-icon" role="img" aria-label="upload">⬆️</span>
                <div className="upload-text">Załącz zdjęcie</div>
              </div>
            )}
          </div>
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
                required
              >
                <option value="">-- Wybierz kategorię --</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.id.toString()}>
                    {c.name}
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>
        <div className="map-wrapper">
          <MapContainer
            center={position}
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
        {error && <div className="error-text">{error}</div>}
        {successMsg && <div className="success-text">{successMsg}</div>}
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

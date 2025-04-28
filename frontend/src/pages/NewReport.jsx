import React, { useState } from "react";
import { useNavigate } from "react-router-dom";

export default function NewReport() {
  const [title, setTitle] = useState("");
  const [content, setContent] = useState("");
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    const user = JSON.parse(localStorage.getItem("user"));

    try {
      const response = await fetch("http://localhost:8000/api/reports", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
        body: JSON.stringify({
          title,
          content,
          userId: user?.id,
        }),
      });

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error("Unauthorized");
        }
        if (response.status === 403) {
          throw new Error("Forbidden");
        }
        throw new Error("Failed to create report");
      }

      navigate("/dashboard");
    } catch (err) {
      console.error("Error creating report:", err.message);

      if (err.message === "Unauthorized") {
        localStorage.removeItem("token");
        navigate("/login");
      } else {
        setError(err.message);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ padding: "30px" }}>
      <h2>Dodaj zgłoszenie</h2>
      <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", width: "400px", gap: "15px" }}>
        <input
          type="text"
          placeholder="Tytuł zgłoszenia"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          required
        />
        <textarea
          placeholder="Opis zgłoszenia"
          value={content}
          onChange={(e) => setContent(e.target.value)}
          rows="5"
          required
        />
        <button type="submit" disabled={loading}>
          {loading ? "Wysyłanie..." : "Dodaj"}
        </button>
        {error && <p style={{ color: "red" }}>{error}</p>}
      </form>
      <button onClick={() => navigate("/dashboard")} style={{ marginTop: "20px" }}>
        Powrót
      </button>
    </div>
  );
}

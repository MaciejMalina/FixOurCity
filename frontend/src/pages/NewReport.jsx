import React, { useState } from "react";
import { useNavigate } from "react-router-dom";

export default function NewReport() {
  const [title, setTitle] = useState("");
  const [content, setContent] = useState("");
  const [error, setError] = useState("");
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");

    const user = JSON.parse(localStorage.getItem("user"));

    try {
      const response = await fetch("http://localhost:8000/api/reports", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
        body: JSON.stringify({
          title,
          content,
          userId: user.id,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        setError(data.error || "Failed to create report");
        return;
      }

      navigate("/dashboard");
    } catch
    {
        setError("Something went wrong");
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
        <button type="submit">Dodaj</button>
        {error && <p style={{ color: "red" }}>{error}</p>}
      </form>
      <button onClick={() => navigate("/dashboard")} style={{ marginTop: "20px" }}>
        Powrót
      </button>
    </div>
  );
}

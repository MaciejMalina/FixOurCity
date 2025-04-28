import React from "react";
import { useNavigate } from "react-router-dom";

export default function Forbidden() {
  const navigate = useNavigate();

  return (
    <div style={{ textAlign: "center", marginTop: "100px" }}>
      <h1>403 - Brak dostępu</h1>
      <p>Nie masz uprawnień, aby zobaczyć tę stronę.</p>
      <button onClick={() => navigate("/dashboard")} style={{ marginTop: "20px" }}>
        Wróć do Dashboard
      </button>
    </div>
  );
}

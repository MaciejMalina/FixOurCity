import React from "react";
import { useNavigate } from "react-router-dom";

export default function InternalServerError() {
  const navigate = useNavigate();

  return (
    <div className="error-page">
      <h1>500 - Internal Server Error</h1>
      <p>Something went wrong on our side. Please try again later.</p>
      <button onClick={() => navigate("/dashboard")}>Go to Dashboard</button>
    </div>
  );
}

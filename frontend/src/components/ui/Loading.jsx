import React from "react";
import "../../styles/Loading.css";

export default function Loading() {
  return (
    <div className="loading-container">
      <div className="spinner"></div>
      <p>Ładowanie...</p>
    </div>
  );
}
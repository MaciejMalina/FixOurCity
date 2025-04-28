import React from "react";
import { useNavigate } from "react-router-dom";
import "../styles/NotFound.css";

export default function NotFound() {
  const navigate = useNavigate();

  const handleGoHome = () => {
    const token = localStorage.getItem("token");
    if (token) {
      navigate("/dashboard");
    } else {
      navigate("/login");
    }
  };

  return (
    <div className="notfound-container">
      <div className="notfound-content">
        <h1>Error 404</h1>
        <div className="notfound-image" />
        <h2>Look like you're lost</h2>
        <p>The page you are looking for is not available!</p>
        <button className="home-button" onClick={handleGoHome}>
          Go to Home
        </button>
      </div>
    </div>
  );
}

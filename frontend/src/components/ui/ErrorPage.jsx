import React from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/ErrorPage.css';

export default function ErrorPage({ status, title, message, backTo = '/' }) {
  const navigate = useNavigate();
  return (
    <div className="error-page">
      <h1>{status}</h1>
      <h2>{title}</h2>
      <p>{message}</p>
      <button onClick={() => navigate(backTo)}>Wróć</button>
    </div>
  );
}

import React from 'react';
import { Link } from 'react-router-dom';
import '../../styles/ErrorPage.css';

export default function ErrorPage({ status, title, message, backTo }) {
  return (
    <div className="error-page">
      <div className="error-page__container">
        <div className="error-page__status">{status}</div>
        <div className="error-page__title">{title}</div>
        <div className="error-page__message">{message}</div>
        <Link to={backTo} className="error-page__back">
          Powrót do strony głównej
        </Link>
      </div>
    </div>
  );
}

import React from 'react';
import '../../styles/ErrorMessage.css';

export default function ErrorMessage({ message, onRetry }) {
  return (
    <div className="error-message">
      <p className="error-message__text">Wystąpił błąd: <strong>{message}</strong></p>
      {onRetry && (
        <button
          className="error-message__button"
          onClick={onRetry}
        >
          Spróbuj ponownie
        </button>
      )}
    </div>
  );
}

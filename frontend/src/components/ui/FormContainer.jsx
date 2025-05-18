import React from 'react';

export default function FormContainer({ children }) {
  return (
    <div className="auth-container">
      <div className="auth-box">
        {children}
      </div>
    </div>
  );
}

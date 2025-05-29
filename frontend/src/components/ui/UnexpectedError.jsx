import React from 'react';

export default function UnexpectedError({ error, resetErrorBoundary }) {
  return (
    <div style={{ padding: '2rem', textAlign: 'center' }}>
      <h1>Ups… Coś poszło nie tak</h1>
      <pre style={{ color: 'red' }}>{error.message}</pre>
      <button onClick={resetErrorBoundary}>Spróbuj ponownie</button>
    </div>
  );
}

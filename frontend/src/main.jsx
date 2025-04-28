import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter, useLocation } from "react-router-dom";
import App from "./App";
import ErrorBoundary from "./components/ErrorBoundary"; 
import "./index.css";

// Nowa pomocnicza funkcja:
function AppWithErrorBoundary() {
  const location = useLocation();

  return (
    <ErrorBoundary key={location.pathname}>
      <App />
    </ErrorBoundary>
  );
}

ReactDOM.createRoot(document.getElementById("root")).render(
  <React.StrictMode>
    <BrowserRouter>
      <AppWithErrorBoundary />
    </BrowserRouter>
  </React.StrictMode>
);

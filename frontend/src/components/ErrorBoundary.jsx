import React from "react";
import { useNavigate } from "react-router-dom";

export default class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, errorMessage: "" };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, errorMessage: error.message || "Something went wrong." };
  }

  componentDidCatch(error, errorInfo) {
    console.error("Uncaught error:", error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return <ErrorDisplay message={this.state.errorMessage} />;
    }

    return this.props.children;
  }
}

function ErrorDisplay({ message }) {
  const navigate = useNavigate();

  return (
    <div style={{ textAlign: "center", marginTop: "2rem" }}>
      <div style={{
        display: "inline-block",
        padding: "1.5rem 2rem",
        backgroundColor: "#ffe0e0",
        color: "#b00020",
        borderRadius: "8px",
        boxShadow: "0 0 10px rgba(0,0,0,0.1)"
      }}>
        <h2>Oops!</h2>
        <p>{message}</p>
        <button
          onClick={() => navigate("/dashboard")}
          style={{
            marginTop: "1rem",
            padding: "0.5rem 1.5rem",
            backgroundColor: "#b00020",
            color: "white",
            border: "none",
            borderRadius: "4px",
            cursor: "pointer"
          }}
        >
          Go back to Dashboard
        </button>
      </div>
    </div>
  );
}

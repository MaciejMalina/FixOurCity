import React from "react";
import { Routes, Route } from "react-router-dom";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Dashboard from "./pages/Dashboard";
import UserDetails from "./pages/UserDetails";
import Reports from "./pages/Reports";
import NewReport from "./pages/NewReport";


function App() {
  return (
    <Routes>
      <Route path="/" element={<Login />} />
      <Route path="/register" element={<Register />} />
      <Route path="/dashboard" element={<Dashboard />} />
      <Route path="/users/:id" element={<UserDetails />} />
      <Route path="/reports" element={<Reports />} />
      <Route path="/new-report" element={<NewReport />} />
    </Routes>
  );
}

export default App;

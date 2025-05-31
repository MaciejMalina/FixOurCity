import { Routes, Route, Navigate } from 'react-router-dom';

import Login           from './components/auth/Login';
import Register        from './components/auth/Register';
import Dashboard       from './components/dashboards/Dashboard';
import AdminDashboard  from './components/dashboards/AdminDashboard';
import ReportsList     from './components/reports/ReportList';
import ReportForm      from './components/reports/ReportForm';
import NotFound        from './components/NotFound';
import AccessDenied    from './components/AccessDenied';

function PrivateRoute({ children }) {
  const token = localStorage.getItem('token');
  return token
    ? children
    : <Navigate to="/access-denied" replace />;
}

export default function App() {
  return (
      <Routes>
        <Route path="/login"    element={<Login />} />
        <Route path="/register" element={<Register />} />

        <Route path="/dashboard" element={<PrivateRoute><Dashboard/></PrivateRoute>} />
        <Route path="/reports"   element={<PrivateRoute><ReportsList/></PrivateRoute>} />
        <Route path="/new-report"element={<PrivateRoute><ReportForm/></PrivateRoute>} />
        <Route path="/admin"     element={<PrivateRoute><AdminDashboard/></PrivateRoute>} />

        <Route path="/access-denied" element={<AccessDenied />} />
        <Route path="*"              element={<NotFound />} />
      </Routes>
  );
}

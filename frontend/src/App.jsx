import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';

import Login           from './components/auth/Login';
import Register        from './components/auth/Register';
import Dashboard       from './components/dashboards/Dashboard';
import AdminDashboard  from './components/dashboards/AdminDashboard';
import ReportsList     from './components/reports/ReportList';
import ReportForm      from './components/reports/ReportForm';
import ReportDetails   from './components/reports/ReportDetails';
import NotFound        from './components/NotFound';
import AccessDenied    from './components/AccessDenied';
import EditUser        from './components/users/EditUser';

function PrivateRoute({ children }) {
  const token = localStorage.getItem('token');
  return token
    ? children
    : <Navigate to="/access-denied" replace />;
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login"    element={<Login />} />
        <Route path="/register" element={<Register />} />

        <Route path="/dashboard" element={<PrivateRoute><Dashboard/></PrivateRoute>} />
        <Route path="/reports"   element={<PrivateRoute><ReportsList/></PrivateRoute>} />
        <Route path="/new-report"element={<PrivateRoute><ReportForm/></PrivateRoute>} />
        <Route path="/admin"     element={<PrivateRoute><AdminDashboard/></PrivateRoute>} />
        <Route path="/reports/:id" element={<PrivateRoute><ReportDetails/></PrivateRoute>} />
        <Route path="/admin/edit-user/:id" element={<PrivateRoute><EditUser/></PrivateRoute>} />

        <Route path="/access-denied" element={<AccessDenied />} />
        <Route path="*"              element={<NotFound />} />
      </Routes>
    </BrowserRouter>
  );
}

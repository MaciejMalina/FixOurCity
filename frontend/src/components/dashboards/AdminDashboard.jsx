import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import ReportsList from '../reports/ReportList';
import UsersList from '../users/UserList';
import '../../styles/AdminDashboard.css';

export default function AdminDashboard() {
  const [tab, setTab] = useState('list');
  const [user, setUser] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (!token) {
      navigate('/access-denied');
      return;
    }
    fetch('/api/v1/auth/me', {
      headers: { Authorization: `Bearer ${token}` },
      credentials: 'include'
    })
      .then(res => res.json())
      .then(data => {
        if (!data.roles || !data.roles.includes('ROLE_ADMIN')) {
          navigate('/access-denied');
        } else {
          setUser(data);
        }
      })
      .catch(() => navigate('/access-denied'));
  }, [navigate]);

  if (!user) return null;

  return (
    <div className="admin">
      <aside className="admin__sidebar">
        <button onClick={() => setTab('list')} className={tab === 'list' ? 'active' : ''}>
          Lista zgłoszeń
        </button>
        <button onClick={() => setTab('users')} className={tab === 'users' ? 'active' : ''}>
          Użytkownicy
        </button>
      </aside>
      <main className="admin__content">
        {tab === 'list' && <ReportsList adminMode />}
        {tab === 'users' && <UsersList />}
      </main>
    </div>
  );
}

import React, { useState } from 'react';
import ReportsList from '../reports/ReportList';
import ReportForm    from '../reports/ReportForm';
import '../../styles/AdminDashboard.css';

export default function AdminDashboard() {
  const [tab, setTab] = useState('list');

  return (
    <div className="admin">
      <aside className="admin__sidebar">
        <button onClick={() => setTab('list')} className={tab==='list'?'active':''}>
          Lista zgłoszeń
        </button>
        <button onClick={() => setTab('new')} className={tab==='new'?'active':''}>
          Dodaj zgłoszenie
        </button>
      </aside>
      <main className="admin__content">
        {tab === 'list' ? <ReportsList /> : <ReportForm onSuccess={() => setTab('list')} />}
      </main>
    </div>
  );
}

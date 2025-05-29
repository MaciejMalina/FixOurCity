import React, { useEffect, useState } from 'react';
import { fetchReports } from '../api/reports';
import Loading      from './ui/Loading';
import ErrorMessage from './ui/ErrorMessage';
import '../styles/ReportList.css';

export default function ReportsList() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error,   setError]   = useState(null);

  useEffect(() => {
    let mounted = true;
    setLoading(true);
    fetchReports({ page:1, limit:50 })
      .then(({ data }) => mounted && setReports(data))
      .catch(err=> mounted && setError(err))
      .finally(()=> mounted && setLoading(false));
    return () => { mounted = false; };
  }, []);

  if (loading) return <Loading />;
  if (error)   return <ErrorMessage message={error.message} />;

  if (reports.length === 0) {
    return <p>Brak zgłoszeń.</p>;
  }

  return (
    <ul className="reports-list">
      {reports.map(r => (
        <li key={r.id}>
          <strong>{r.title}</strong><br/>
          {new Date(r.createdAt).toLocaleString()}
        </li>
      ))}
    </ul>
  );
}

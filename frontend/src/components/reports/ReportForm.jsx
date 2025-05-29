import React, { useState } from 'react';
import { createReport } from '../../api/reports';
import Loading      from '../ui/Loading';
import ErrorMessage from '../ui/ErrorMessage';
import '../../styles/ReportForm.css';

export default function ReportForm({ onSuccess }) {
  const [title, setTitle] = useState('');
  const [desc,  setDesc]  = useState('');
  const [loading, setLoading] = useState(false);
  const [error,   setError]   = useState(null);

  const handleSubmit = async e => {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      await createReport({ title, description: desc });
      setTitle(''); setDesc('');
      onSuccess();
    } catch (err) {
      setError(err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form className="report-form" onSubmit={handleSubmit}>
      <label> Tytuł:
        <input value={title} onChange={e=>setTitle(e.target.value)} required />
      </label>
      <label> Opis:
        <textarea value={desc} onChange={e=>setDesc(e.target.value)} required />
      </label>
      {error && <ErrorMessage message={error.message} />}
      <button type="submit" disabled={loading}>
        {loading ? 'Wysyłanie…' : 'Dodaj zgłoszenie'}
      </button>
    </form>
  );
}

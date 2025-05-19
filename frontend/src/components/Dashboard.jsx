import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '../styles/Dashboard.css';
import Loading from "./ui/Loading";

import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

export default function Dashboard() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading]   = useState(true);
  const [error, setError]       = useState(null);
  const navigate                = useNavigate();
  const token                   = localStorage.getItem('token');

  useEffect(() => {
    const fetchReports = async () => {
      setLoading(true);
      try {
        const res = await fetch(
          'http://localhost:8000/api/v1/reports?page=1&limit=5', 
          { headers: { Authorization: `Bearer ${token}` } }
        );
        if (!res.ok) throw new Error(`Fetch error ${res.status}`);
        const { data } = await res.json();
        setReports(data);
      } catch (e) {
        console.error('Fetch reports error:', e);
        setError(e);
        if (e.message.includes('401')) {
          localStorage.clear();
          navigate('/login');
        }
      } finally {
        setLoading(false);
      }
    };

    fetchReports();
  }, [navigate, token]);

  const handleLogout = async () => {
    try {
      await fetch('http://localhost:8000/api/v1/auth/logout', {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
      });
    } catch (e) {
      console.error('Logout error:', e);
    }
    localStorage.clear();
    navigate('/login');
  };

  if (loading) return <Loading />;
  if (error)    return <div className="dashboard-error">Coś poszło nie tak :(</div>;

  return (
    <div className="dashboard">
      <aside className="dashboard__sidebar">
        <div className="logo" onClick={() => navigate('/')}>
          <img src="/logo.png" alt="FixOurCity" />
        </div>
        <nav>
          <ul>
            <li onClick={() => navigate('/reports')}>
              Lista zgłoszeń
              <ul className="dashboard__recent">
                {reports.map(r => (
                  <li key={r.id} onClick={() => navigate(`/reports/${r.id}`)}>
                    • {r.title || `#${r.id}`}
                  </li>
                ))}
              </ul>
            </li>
            <li onClick={() => navigate('/new-report')}>Dodaj nowe zgłoszenie</li>
            <li onClick={() => navigate('/admin')}>Panel administratora</li>
            <li onClick={() => navigate('/settings')}>Ustawienia</li>
          </ul>
        </nav>
        <button className="dashboard__logout" onClick={handleLogout}>
          Wyloguj
        </button>
      </aside>

      <main className="dashboard__map">
        <MapContainer center={[50.06465, 19.94498]} zoom={12}>
          <TileLayer
            attribution='&copy; <a href="https://www.openstreetmap.org/">OSM</a>'
            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
          />
          {reports.map(r => (
            r.location && (
              <Marker
                key={r.id}
                position={[r.location.lat, r.location.lng]}
              >
                <Popup>
                  <strong>{r.title}</strong><br/>
                  {r.description?.slice(0, 50)}…
                  <br/>
                  <button onClick={() => navigate(`/reports/${r.id}`)}>
                    Szczegóły
                  </button>
                </Popup>
              </Marker>
            )
          ))}
        </MapContainer>
      </main>
    </div>
  );
}

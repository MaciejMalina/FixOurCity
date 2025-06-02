import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '../../styles/Dashboard.css';
import Loading from "../ui/Loading";
import SidebarMenu from "../SidebarMenu";

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
          '/api/v1/reports?page=1&limit=1000',
          {
            credentials: 'include',
            headers: {
              'Accept': 'application/json',
              'Authorization': `Bearer ${token}`
            }
          }
        );
        if (!res.ok) throw new Error(`Fetch error ${res.status}`);
        const { data } = await res.json();
        const filtered = data.filter(r =>
          r.status?.label === "Nowe" || r.status?.label === "W trakcie realizacji"
        );
        setReports(filtered);
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

  if (loading) return <Loading />;
  if (error)    return <div className="dashboard-error">Coś poszło nie tak :(</div>;

  return (
    <SidebarMenu reports={reports}>
      <MapContainer center={[50.06465, 19.94498]} zoom={12}>
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/">OSM</a>'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        {reports.map(r => (
          r.latitude && r.longitude && (
            <Marker
              key={r.id}
              position={[r.latitude, r.longitude]}
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
    </SidebarMenu>
  );
}

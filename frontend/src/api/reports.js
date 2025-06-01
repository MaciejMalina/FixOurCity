const API = "http://localhost:8000/api/v1";

export async function fetchReports({ page = 1, limit = 20 } = {}) {
  const resp = await fetch(`${API}/reports?page=${page}&limit=${limit}`, {
    headers: {
      Authorization: `Bearer ${localStorage.getItem("token")}`,
      Accept: "application/json"
    },
    credentials: "include"
  });
  if (!resp.ok) throw new Error(`Błąd pobierania zgłoszeń (${resp.status})`);
  return resp.json();
}

export async function createReport(payload) {
  const resp = await fetch(`${API}/reports`, {
    method: "POST",
    headers: {
      Authorization: `Bearer ${localStorage.getItem("token")}`,
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify(payload)
  });
  if (!resp.ok) {
    const { error } = await resp.json().catch(() => ({}));
    throw new Error(error || `Błąd dodawania zgłoszenia (${resp.status})`);
  }
  return resp.json();
}
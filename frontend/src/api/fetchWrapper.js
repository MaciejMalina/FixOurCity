export async function apiFetch(url, opts = {}) {
  const resp = await fetch(url, {
    credentials: 'include',
    ...opts
  });
  if (resp.status === 401) {
    window.location.href = '/access-denied';
    throw new Error('Brak dostępu (401)');
  }
  if (resp.status === 403) {
    window.location.href = '/access-denied';
    throw new Error('Brak uprawnień (403)');
  }
  if (!resp.ok) {
    const { error } = await resp.json().catch(() => ({}));
    throw new Error(error || `Błąd serwera: ${resp.status}`);
  }
  return resp.json();
}

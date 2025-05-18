const API = 'http://localhost:8000/api/v1';

export async function register(data) {
  const resp = await fetch(`${API}/auth/register`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify(data),
  });
  if (!resp.ok) {
    const err = await resp.json().catch(() => ({}));
    throw new Error(err.error || 'Registration failed');
  }
  return resp.json();
}

export async function login(credentials) {
  const resp = await fetch(`${API}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify(credentials),
  });
  if (!resp.ok) {
    const err = await resp.json().catch(() => ({}));
    throw new Error(err.error || 'Login failed');
  }
  return resp.json();
}

export async function refreshToken() {
  const resp = await fetch(`${API}/auth/token/refresh`, {
    method: 'POST',
    credentials: 'include',
  });
  if (!resp.ok) {
    throw new Error('Could not refresh token');
  }
  return resp.json();
}

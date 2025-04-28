export async function apiFetch(url, options = {}) {
  const token = localStorage.getItem("token");

  const headers = {
    "Content-Type": "application/json",
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...options.headers,
  };

  try {
    const response = await fetch(url, {
      ...options,
      headers,
    });

    if (response.status === 401) {
      localStorage.removeItem("token");
      window.location.href = "/login";
      throw new Error("Sesja wygasła. Zaloguj się ponownie.");
    }

    if (response.status === 403) {
      window.location.href = "/forbidden";
      throw new Error("Brak dostępu.");
    }

    if (response.status === 404) {
      throw new Error("Nie znaleziono zasobu.");
    }

    if (response.status === 422) {
      const errorData = await response.json();
      throw new Error(errorData.error || "Błąd walidacji.");
    }

    if (response.status >= 500) {
      throw new Error("Wewnętrzny błąd serwera.");
    }

    if (response.status === 204) {
      return null;
    }

    const data = await response.json();
    return data;

  } catch (error) {
    console.error("API error:", error.message);
    throw error;
  }
}

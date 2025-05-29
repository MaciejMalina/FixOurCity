import ErrorPage from './ui/ErrorPage';

export default function NotFound() {
  return (
    <ErrorPage
      status="404"
      title="Strona nie istnieje"
      message="Sprawdź adres lub wróć do strony głównej."
      backTo="/"
    />
  );
}

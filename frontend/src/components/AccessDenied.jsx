import ErrorPage from './ui/ErrorPage';

export default function AccessDenied() {
  return (
    <ErrorPage
      status="403"
      title="Brak dostępu"
      message="Nie masz uprawnień lub musisz się zalogować."
      backTo="/login"
    />
  );
}

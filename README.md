# FixOurCity

Application for reporting and managing city problems (e.g. potholes, illegal dumps, lighting failures). It allows residents to report problems, add photos, and the administration to manage reports.

---

## Architecture diagram

```
+-------------------+        REST API        +-------------------+         SQL/RabbitMQ
|    Frontend       |  <----------------->  |     Backend       | <-----------------+
|  (React + Vite)   |                      |   (Symfony 7)     |                   |
+-------------------+                      +-------------------+                   |
        |                                         |                                |
        |----------------- HTTP(S) ---------------|                                |
        |                                         |                                |
        |<----------- WebSocket ------------------|                                |
        |                                         |                                |
        |                                         |---+----> PostgreSQL (DB)       |
        |                                         |   |                            |
        |                                         |   +----> RabbitMQ              |
```

---

## Startup instructions

### Requirements

- Docker + Docker Compose
- Node.js 18+
- Composer

### 1. Starting the application
Just run the .bat script
```terminal
(with docker running in the background)
start start.bat
```
Frontend available by default at [http://localhost:5173](http://localhost:5173)

### 3. Access to developer tools

- **pgAdmin**: [http://localhost:5050](http://localhost:5050)
- **RabbitMQ UI**: [http://localhost:15672](http://localhost:15672)
- **Swagger (API docs)**: [http://localhost:8000/api/docs](http://localhost:8000/api/docs)

---

## Technologies used and justification

### Backend

- **Symfony 7** – fast development, security, extensive ecosystem.
- **Doctrine ORM** – convenient entity mapping to database, migrations, relations.
- **Messenger + RabbitMQ** – support for queues and asynchronous tasks (e.g. sending emails).
- **JWT + HttpOnly Cookies** – secure authentication and authorization.
- **PostgreSQL** – efficient, scalable relational database.

### Frontend

- **React 18+ (Vite)** – modern, fast framework for building websites.
- **React Router** – client-side routing.
- **React Query** – cache management and data fetching.

- **React-Leaflet** – a map to the location of tickets.

- **CSS Modules / own design system** – consistent, responsive look.

### DevOps

- **Docker** – easy startup and isolation of environments.

- **Docker Compose** – quick start of services (base, queues).

**Why such a choice?**

I focus on security, efficiency and ease of development. Symfony and React are proven technologies, and RabbitMQ allows for scaling and asynchronous tasks.

---

## Key functionalities

- Registration and login (JWT, refresh tokens, roles)
- Adding, browsing (commenting on them in the future)
- Uploading photos to tickets
- Administration panel (managing tickets, users)
- Role system (user, admin)
- Pagination, filtering, sorting tickets
- Task queuing (e.g. email notifications)
- API documentation (Swagger)
- Unit and integration tests

---

## ERD diagram

![ERD](ERD.png)

---

## Authors

- Maciej Malina

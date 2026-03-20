# Pontszámító - első kör

Ebben a körben a projekt két részből áll:

- `backend/`: Composer alapú PHP API
- `frontend/`: Vue 3 SPA (Vite)

## Indítás

### 1) Backend API

```bash
cd backend
composer install
composer start
```

Elérhető endpoint:

- `GET http://127.0.0.1:8000/api/subjects`

### 2) Frontend

```bash
cd frontend
npm install
npm run dev
```

Alapértelmezett API URL:

- `http://localhost:8000`

Ha mást használsz, hozd létre a `frontend/.env` fájlt:

```bash
VITE_API_BASE_URL=http://127.0.0.1:8000
```

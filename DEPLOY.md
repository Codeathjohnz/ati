# Deploying to a VPS with Dokploy

This repo ships as three containers, built from source by Dokploy on every deploy:

| Service    | Built from          | Serves |
|------------|----------------------|--------|
| `mysql`    | official `mysql:8.0` image | the database, on a persisted volume |
| `backend`  | [backend/Dockerfile](backend/Dockerfile) | the Laravel API (nginx + php-fpm) |
| `frontend` | [frontend/Dockerfile](frontend/Dockerfile) | the React SPA (static files via nginx) |

`docker-compose.yml` at the repo root wires all three together.

## 1. Push this repo to GitHub

Already done if you're reading this from the repo. Dokploy pulls straight from
the GitHub URL you give it — no image registry needed.

## 2. Create the Compose application in Dokploy

1. In Dokploy: **Create Project** → **Add Service** → **Compose**.
2. Point it at this GitHub repo (`Codeathjohnz/ati`), branch `main`, and set
   the **Compose Path** to `docker-compose.yml` (repo root).
3. Open the **Environment** tab and paste in the contents of
   [.env.example](.env.example), filled in for real:
   - `APP_KEY` — generate once locally: `cd backend && php artisan key:generate --show`
   - `APP_URL` / `FRONTEND_URL` — the two domains you'll assign in step 4
   - `DB_ROOT_PASSWORD` / `DB_PASSWORD` — pick real passwords
   - `ADMIN_USERNAME` / `ADMIN_EMAIL` / `ADMIN_PASSWORD` — the login for the
     one bootstrap administrator account seeded on first boot. **Change
     `ADMIN_PASSWORD` from the default** — this is a live, public site now.
   - `VITE_API_BASE_URL` — see the domain note below; this one must also be
     set as a **build argument** on the `frontend` service specifically
     (Dokploy's per-service "Build Args" field), because Vite bakes it into
     the JS bundle at build time — an env var alone never reaches the
     browser.

## 3. Domains — recommended: one subdomain per service

Give the backend and frontend their own subdomains, e.g.:

- `app.your-domain.com` → `frontend` service, container port `80`
- `api.your-domain.com` → `backend` service, container port `80`

Set these in Dokploy's **Domains** tab for the Compose app (pick the service
+ port per domain), and let Dokploy issue Let's Encrypt certs for both.

Then:
- `APP_URL=https://api.your-domain.com`
- `FRONTEND_URL=https://app.your-domain.com`
- `VITE_API_BASE_URL=https://api.your-domain.com/api/v1`

(A single shared domain with path-based routing — `your-domain.com/api/*` →
backend, everything else → frontend — also works and avoids CORS entirely;
it just needs a path-prefix rule in Dokploy's domain config instead. If you
go that route, leave `VITE_API_BASE_URL=/api/v1` as-is.)

## 4. Deploy

Click **Deploy**. On first boot the backend container automatically:
1. waits for MySQL to accept connections,
2. runs `php artisan migrate --force`,
3. runs `php artisan db:seed --force` — this only creates the one admin
   account plus reference data (required document types, Caraga-region
   locations); it never inserts fake trainees. It's safe to run on every
   redeploy — this is why the seeder is `updateOrCreate`-idempotent.

Log in at `https://app.your-domain.com` with the admin credentials you set
in step 2, then create real Project Officer and Trainee accounts through
Admin → User Accounts — nothing else is pre-populated.

## Updating a live deployment

Push to `main`, then hit **Deploy** again in Dokploy (or turn on
auto-deploy-on-push in the Compose app's Git settings). Migrations run again
automatically; existing data in the `ati_mysql_data` volume is untouched.

## Local Docker test (optional, before pushing to the VPS)

```bash
cp .env.example .env   # fill in APP_KEY at minimum
docker compose up --build
```

Frontend: http://localhost (published via a reverse proxy in production —
for a bare local test, add `ports: ["8080:80"]` under each service, or use
`docker compose port frontend 80`).

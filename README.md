<!-- Improved compatibility of back to top link: See: https://github.com/othneildrew/Best-README-Template/pull/73 -->
<a id="readme-top"></a>

<!-- PROJECT SHIELDS -->
[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![project_license][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/monatemedia/assessme">
    <img src="images/logo.png" alt="Logo" height="80">
  </a>

<h3 align="center">AssessMe</h3>

  <p align="center">
    A self-hosted, AI-assisted technical screening tool. Create assessments, administer candidates, and grade responses — with AI marking, plagiarism detection, and full Docker deployment.
    <br />
    <a href="https://github.com/monatemedia/assessme"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://assessme.monatemedia.com">Staging</a>
    <!-- &middot;
    <a href="https://assessme.co.za">Production</a> -->
    &middot;
    <a href="https://github.com/monatemedia/assessme/issues/new?labels=bug&template=bug-report---.md">Report Bug</a>
    &middot;
    <a href="https://github.com/monatemedia/assessme/issues/new?labels=enhancement&template=feature-request---.md">Request Feature</a>
  </p>
</div>

<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li><a href="#about-the-project">About The Project</a>
      <ul><li><a href="#built-with">Built With</a></li></ul>
    </li>
    <li><a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#how-it-works">How It Works</a></li>
        <li><a href="#first-time-setup">First Time Setup</a></li>
        <li><a href="#daily-development-commands">Daily Development Commands</a></li>
        <li><a href="#running-artisan-commands">Running Artisan Commands</a></li>
        <li><a href="#destroying-and-rebuilding">Destroying and Rebuilding</a></li>
        <li><a href="#local-ports">Local Ports</a></li>
        <li><a href="#superuser-configuration">Superuser Configuration</a></li>
        <li><a href="#connecting-to-the-database-locally">Connecting to the Database</a></li>
        <li><a href="#file-watching-and-hot-reload">File Watching and Hot Reload</a></li>
        <li><a href="#troubleshooting">Troubleshooting</a></li>
      </ul>
    </li>
    <li><a href="#deployment">Deployment</a>
      <ul>
        <li><a href="#environments">Environments</a></li>
        <li><a href="#required-github-secrets">Required GitHub Secrets</a></li>
        <li><a href="#cicd-flow">CI/CD Flow</a></li>
        <li><a href="#gitflow-branching-model">GitFlow Branching Model</a></li>
        <li><a href="#release-workflow">Release Workflow</a></li>
        <li><a href="#server-maintenance">Server Maintenance</a></li>
      </ul>
    </li>
    <li><a href="#port-reference">Port Reference</a></li>
    <li><a href="#user-administration-with-tinker">User Administration with Tinker</a></li>
    <li><a href="#roadmap">Roadmap</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="#acknowledgments">Acknowledgments</a></li>
  </ol>
</details>

---

## About The Project

[![Product Screenshot][product-screenshot]](https://assessme.monatemedia.com)

AssessMe was born from a real problem encountered during a job application process: AI plagiarism detection tools produce binary pass/fail outcomes that penalise candidates who use AI responsibly as a thinking tool, rather than measuring the depth of understanding behind their answers.

AssessMe solves this by treating AI usage as an informational metric — a percentage visible to the examiner to inform judgement, not to trigger automatic disqualification.

**Key features:**

- Examiners create assessments with multiple question types (multiple choice single and multiple answer, free text, code)
- Candidates receive a unique UUID access link — no account registration required
- GPT-4o marks free-text and code answers against an examiner-defined answer key, returning a numeric score, confidence level, and qualitative feedback
- AI plagiarism detection runs on submission, returning a per-answer and overall usage percentage
- All AI jobs are queued as background jobs via Redis — no blocking HTTP requests
- Examiners can override AI scores and export results to PDF
- Full Docker deployment behind an existing Nginx reverse proxy with zero proxy configuration changes required
- Assessments can be created via the UI or imported via JSON file

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Built With

[![Laravel][Laravel.com]][Laravel-url]
[![Filament][Filament.com]][Filament-url]
[![Livewire][Livewire.com]][Livewire-url]
[![Postgres][Postgresql.org]][Postgresql-url]
[![Redis][Redis.io]][Redis-url]
[![Docker][Docker]][Docker-url]
[![GitHub-Actions][GitHub-Actions]][GitHub-Actions-url]
[![GitHub][GitHub.com]][GitHub.com-url]

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Getting Started

> [!NOTE]
> AssessMe is **completely self-contained**. The only external dependency is [Docker Desktop](https://www.docker.com/products/docker-desktop/). No local PHP, Node.js, Composer, PostgreSQL, or Redis installation is required.

### How It Works

The local stack runs five containers:

```
docker-compose.yml + docker-compose.local.yml
│
├── assessme-web      Laravel 13 + Filament 5 (Apache, PHP 8.3)
├── assessme-queue    Queue worker — processes AI marking jobs
├── assessme-vite     Vite dev server — hot module replacement
├── assessme-db       PostgreSQL 18 (pgvector/pgvector:pg18-trixie)
└── assessme-redis    Redis — queue driver and cache
```

On every boot, `docker-entrypoint.sh` automatically:
1. Waits for PostgreSQL and Redis to be ready
2. Clears and rebuilds all Laravel caches
3. Runs `php artisan migrate --force` — safe to run repeatedly, skips already-applied migrations
4. Creates the superuser if no users exist (credentials from `.env`)
5. Starts Apache

**You never need to run migrations or create users manually.**

---

### First Time Setup

**Step 1 — Clone the repository**

```bash
git clone https://github.com/monatemedia/assessme.git
cd assessme
```

**Step 2 — Create your local environment file**

```bash
cp .env.local .env
```

**Step 3 — Add your OpenAI API key to `.env`**

```env
OPENAI_API_KEY=your-key-here
```

> [!TIP]
> All other values in `.env.local` are pre-configured for local Docker development. You do not need to change anything else to get started.

**Step 4 — Build and start the stack**

```bash
docker compose -f docker-compose.yml -f docker-compose.local.yml up -d --build
```

The `--build` flag is only needed on first run or after `Dockerfile` changes. Subsequent starts do not need it.

**Step 5 — Open the app**

| URL | Description |
|---|---|
| `http://localhost:8001` | Cover page |
| `http://localhost:8001/admin/login` | Examiner dashboard login |
| `http://localhost:5173` | Vite HMR dev server |

**Step 6 — Log in**

| Field | Value |
|---|---|
| Email | `admin@assessme.local` |
| Password | `password` |

> [!WARNING]
> These are local development credentials only. Never use them in staging or production.

---

### Daily Development Commands

**Start the stack**

```bash
docker compose -f docker-compose.yml -f docker-compose.local.yml up -d
```

**Stop the stack (preserves data)**

```bash
docker compose -f docker-compose.yml -f docker-compose.local.yml down
```

**Restart a single container**

```bash
docker compose -f docker-compose.yml -f docker-compose.local.yml restart assessme-web
```

**View logs**

```bash
# All containers
docker compose -f docker-compose.yml -f docker-compose.local.yml logs

# Single container, follow mode
docker logs -f assessme-web
docker logs -f assessme-queue
docker logs -f assessme-vite
```

**Check container status**

```bash
docker compose -f docker-compose.yml -f docker-compose.local.yml ps
```

---

### Running Artisan Commands

All Artisan commands run inside the `assessme-web` container:

```bash
# Open an interactive shell inside the container
docker exec -it assessme-web bash

# Or run a single command directly
docker exec assessme-web php artisan <command>
```

**Common commands:**

```bash
# Run migrations manually
docker exec assessme-web php artisan migrate

# Roll back last migration
docker exec assessme-web php artisan migrate:rollback

# Fresh migration with seeders
docker exec assessme-web php artisan migrate:fresh --seed

# Clear all caches
docker exec assessme-web php artisan optimize:clear

# Open Tinker (interactive Laravel console)
docker exec -it assessme-web php artisan tinker

# List all routes
docker exec assessme-web php artisan route:list
```

> [!NOTE]
> On Windows with Git Bash, prefix commands with `winpty` if you get path errors:
> `winpty docker exec -it assessme-web php artisan tinker`
> Alternatively, run commands from WSL to avoid Git Bash path translation issues.

---

### Destroying and Rebuilding

**Stop and remove containers but keep data volumes**

```bash
docker compose -f docker-compose.yml -f docker-compose.local.yml down
```

**Stop and remove containers AND data volumes (full reset)**

```bash
docker compose -f docker-compose.yml -f docker-compose.local.yml down -v
```

> [!WARNING]
> The `-v` flag permanently deletes the PostgreSQL and Redis data volumes. All database data will be lost. On next `up`, migrations and the superuser seeder will run fresh automatically.

**Rebuild the image after Dockerfile changes**

```bash
docker compose -f docker-compose.yml -f docker-compose.local.yml up -d --build
```

**Nuclear option — remove everything Docker-related on your machine**

```bash
# Removes all stopped containers, unused images, networks, and build cache
# This can reclaim several GB of disk space
docker system prune -af --volumes
```

> [!WARNING]
> `docker system prune -af --volumes` removes ALL unused Docker resources across ALL projects on your machine, not just AssessMe. Make sure other projects are running before executing this if you want to preserve their volumes.

---

### Local Ports

| Service | Host Port | Internal Port | Note |
|---|---|---|---|
| Web (Apache) | `8001` | `80` | `http://localhost:8001` |
| Vite HMR | `5173` | `5173` | Hot module replacement |
| PostgreSQL | `5436` | `5432` | Avoids XAMPP's `5432` on Windows |
| Redis | `6390` | `6379` | |

---

### Superuser Configuration

The superuser is created automatically on first boot if no users exist. Configure credentials in `.env`:

```env
SUPERUSER_NAME=Admin
SUPERUSER_EMAIL=admin@assessme.local
SUPERUSER_PASSWORD=password
```

The seeder is **idempotent** — if a user already exists, it skips creation silently. To reset the superuser, either use Tinker or do a full volume reset (`down -v`).

**Change superuser details via Tinker:**

```bash
docker exec -it assessme-web php artisan tinker
```

```php
$user = \App\Models\User::first();
$user->update([
    'name'     => 'New Name',
    'email'    => 'new@email.com',
    'password' => bcrypt('new-password'),
]);
```

---

### Connecting to the Database Locally

The PostgreSQL database is exposed on port `5436`. Connect with any PostgreSQL client (TablePlus, DBeaver, pgAdmin):

| Field | Value |
|---|---|
| Host | `localhost` |
| Port | `5436` |
| Database | `assessme_db` |
| Username | `assessme_user` |
| Password | `secret` |

> [!NOTE]
> Port `5436` is intentional — it avoids conflicting with a local PostgreSQL installation (e.g. XAMPP) which typically runs on `5432`.

---

### File Watching and Hot Reload

Source code is volume-mounted into the container — file changes on your host are instantly reflected without rebuilding the image.

The `assessme-vite` container runs `npm run dev` automatically and watches:
- `resources/css/` — CSS changes
- `resources/js/` — JavaScript changes
- `resources/views/` — Blade template changes (triggers full reload)

Changes to PHP files (controllers, models, routes) are picked up immediately by Apache without any restart needed.

> [!TIP]
> If hot reload stops working, restart the Vite container:
> ```bash
> docker compose -f docker-compose.yml -f docker-compose.local.yml restart assessme-vite
> ```

---

### Troubleshooting

<details>
<summary><strong>Container stuck and won't stop</strong></summary>

This can happen on Docker Desktop for Windows. Force remove the container:

```bash
docker rm -f assessme-web
```

If that fails, restart Docker Desktop from the system tray, then bring the stack back up.

</details>

<details>
<summary><strong>Permission denied on storage or bootstrap/cache</strong></summary>

The volume mount can cause ownership issues on Windows. Fix permissions inside the container from WSL:

```bash
docker exec assessme-web chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker exec assessme-web chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

</details>

<details>
<summary><strong>Login not working after a restart</strong></summary>

Data volumes are pinned by name (`assessme-db-data`, `assessme-redis-data`) and persist across restarts. Check logs to confirm migrations and superuser seeder ran:

```bash
docker logs assessme-web | grep -E "Migration|Superuser|error"
```

If the volume was deleted (e.g. after `down -v`), migrations and superuser creation run automatically on next boot.

</details>

<details>
<summary><strong>VIRTUAL_HOST warning on every command</strong></summary>

```
level=warning msg="The \"VIRTUAL_HOST\" variable is not set. Defaulting to a blank string."
```

This warning is harmless. `VIRTUAL_HOST` is used by the Nginx proxy on the VPS and is intentionally empty in local development.

</details>

<details>
<summary><strong>Vite not reflecting changes</strong></summary>

1. Check Vite is running: `docker logs assessme-vite`
2. Ensure your Blade templates include the Vite directive: `@vite(['resources/css/app.css', 'resources/js/app.js'])`
3. Restart Vite: `docker compose -f docker-compose.yml -f docker-compose.local.yml restart assessme-vite`

</details>

<details>
<summary><strong>npm or Node commands needed inside the container</strong></summary>

The `assessme-web` container (Apache/PHP) does not include Node or npm. Run Node commands in the dedicated Vite container:

```bash
docker exec -it assessme-vite sh
npm install some-package
```

</details>

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Deployment

### DNS Setup

Before deploying, point your domain or subdomain to your VPS by adding an `A` record in your DNS/Nameserver settings (e.g. Cloudflare):

| Type | Name | Points to | TTL | Description |
|---|---|---|---|---|
| `A` | `assessme` | `12.34.567.890` | `14400` | Subdomain — use your app name and VPS IP |
| `A` | `@` | `12.34.567.890` | `14400` | Root domain — use `@` and your VPS IP |

> [!NOTE]
> If you mapped the application as a subdomain (e.g. `assessme.monatemedia.com`), check back after a few minutes and you should see the Nginx `403 Forbidden` message. This confirms Nginx is receiving the request but doesn't yet know how to route it — which is correct at this stage. See [docker-engine-on-linux](https://github.com/monatemedia/docker-engine-on-linux) to get Nginx set up on your VPS.
>
> If you mapped the application as a root domain (e.g. `assessme.co.za`) and have just purchased the domain, DNS propagation can take between a few minutes and 48 hours to be consistent everywhere.

---

### Environments

| Environment | URL | Trigger |
|---|---|---|
| Staging | `https://assessme.monatemedia.com` | Push to `release/*` branch |
| Production | `https://assessme.co.za` | Push tag `v*` to `main` |

Before deploying to your VPS, ensure that you have created the folders where the files will land on the remote server when the CI/CD pipeline runs.

```bash
# Create staging folder
mkdir -p ~/assessme-staging

# Create production folder
mkdir -p ~/assessme

```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Required GitHub Secrets

Configure these in your GitHub repository under **Settings > Secrets and variables > Actions**.

> Ports are stored as GitHub Secrets to make the app portable across servers. To move to a new server, update the secrets — no code changes required.

#### Staging Secrets

| Secret | Description | Example |
|---|---|---|
| `PAT` | GitHub Personal Access Token (GHCR push) — shared across environments | `ghp_xxxxxxxxxxxx` |
| `STAGING_SSH_HOST` | Staging server IP | `12.34.567.890` |
| `STAGING_SSH_USER` | SSH username | `edward` |
| `STAGING_SSH_KEY` | SSH private key for staging server | `-----BEGIN OPENSSH PRIVATE KEY-----` |
| `STAGING_WORK_DIR` | Project directory on staging server | `/home/edward/assessme-staging` |
| `STAGING_APP_KEY` | Run `docker exec assessme-web php artisan key:generate --show` on local machine to generate key | `base64:YOUR_GENERATED_KEY` |
| `STAGING_SUPERUSER_NAME` | Superuser name for first login | `Your Name` |
| `STAGING_SUPERUSER_EMAIL` | Superuser email for first login | `email@domain.com` |
| `STAGING_SUPERUSER_PASSWORD` | Superuser password for first login | `SuperSecretPassword` |
| `STAGING_APP_URL` | Staging app URL | `assessme.monatemedia.com` |
| `STAGING_MAIL_FROM_ADDRESS` | Staging mail from address | `noreply@domain.com` |
| `STAGING_DB_USERNAME` | PostgreSQL username | `assessme_user` |
| `STAGING_DB_PASSWORD` | PostgreSQL password | `a-strong-password` |
| `STAGING_OPENAI_API_KEY` | OpenAI API key | `sk-xxxxxxxxxxxx` |
| `STAGING_DOCKER_WEB_PORT` | Host port for web container | `8250` |
| `STAGING_DOCKER_POSTGRES_PORT` | Host port for PostgreSQL | `5475` |
| `STAGING_DOCKER_REDIS_PORT` | Host port for Redis | `6400` |

#### Production Secrets

| Secret | Description | Example |
|---|---|---|
| `PRODUCTION_SSH_HOST` | Production server IP | `12.34.567.890` |
| `PRODUCTION_SSH_USER` | SSH username | `edward` |
| `PRODUCTION_SSH_KEY` | SSH private key for production server | `-----BEGIN OPENSSH PRIVATE KEY-----` |
| `PRODUCTION_WORK_DIR` | Project directory on production server | `/home/edward/assessme` |
| `PRODUCTION_APP_KEY` | Run `docker exec assessme-web php artisan key:generate --show` on local machine to generate key | `base64:YOUR_GENERATED_KEY` |
| `PRODUCTION_SUPERUSER_NAME` | Superuser name for first login | `Your Name` |
| `PRODUCTION_SUPERUSER_EMAIL` | Superuser email for first login | `email@domain.com` |
| `PRODUCTION_SUPERUSER_PASSWORD` | Superuser password for first login | `SuperSecretPassword` |
| `PRODUCTION_APP_URL` | Production app URL | `assessme.co.za` |
| `PRODUCTION_MAIL_FROM_ADDRESS` | Production mail from address | `noreply@domain.com` |
| `PRODUCTION_DB_USERNAME` | PostgreSQL username | `assessme_user` |
| `PRODUCTION_DB_PASSWORD` | PostgreSQL password | `a-strong-password` |
| `PRODUCTION_OPENAI_API_KEY` | OpenAI API key | `sk-xxxxxxxxxxxx` |
| `PRODUCTION_DOCKER_WEB_PORT` | Host port for web container | `8260` |
| `PRODUCTION_DOCKER_POSTGRES_PORT` | Host port for PostgreSQL | `5485` |
| `PRODUCTION_DOCKER_REDIS_PORT` | Host port for Redis | `6410` |

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### CI/CD Flow

```
Push to release/*
    → GitHub Actions builds Docker image
    → Tags image :staging and :v{version}
    → Pushes to ghcr.io/monatemedia/assessme
    → Creates WORK_DIR on VPS if it does not exist (with correct permissions)
    → SSH into VPS
    → Runs assessme-init (migrations)
    → Starts assessme-web and assessme-queue
    → Health check on https://assessme.monatemedia.com

Push tag v* to main
    → GitHub Actions builds Docker image
    → Tags image :production and :v{version}
    → Pushes to ghcr.io/monatemedia/assessme
    → Creates WORK_DIR on VPS if it does not exist (with correct permissions)
    → SSH into VPS
    → Runs deploy-prod.sh (migrations + restart)
    → Health check on https://assessme.co.za
    → Creates GitHub Release
```

#### Automatic Deployments

| Trigger | Environment | Docker Tags |
|---|---|---|
| Push to `release/*` | Staging | `:staging`, `:v1.0.0` |
| Push to `main` | Production | `:production`, `:v1.0.0` |
| Push tag `v1.0.0` | Production | `:production`, `:v1.0.0` |
| Local dev | Development | `:dev` |

#### Workflow Location

`.github/workflows/docker-publish.yml`

#### Deployment Permissions

The CI/CD pipeline automatically handles directory creation and permissions:

1. Creates `WORK_DIR` with correct `storage` and `bootstrap/cache` structure if it does not exist
2. Deploys new Docker image
3. Starts containers
4. Fixes permissions inside the container
5. Performs external health check

**Manual permission fix (if needed):**

```bash
docker exec assessme-web chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker exec assessme-web chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### GitFlow Branching Model

This project follows the GitFlow branching strategy with automated CI/CD deployments. The goal is to keep `main` always production-ready while using `dev` as an integration branch. All work happens in short-lived branches that are deleted after merge.

#### Core Branches

**`main`** — always production-ready. Automatically deploys to production when code is pushed or a version tag is created.

**`dev`** — integration branch. Features and fixes merge here before going to production. Used for local development on Docker Desktop.

#### Short-Lived Branches

- **`feature/<n>`** — new functionality. Created from `dev`, merged back into `dev`.
- **`bugfix/<n>`** — bug fixes. Created from `dev`, merged back into `dev`.
- **`release/<version>`** — staging branch. Created from `dev`, merged into both `main` and `dev`. **Auto-deploys to staging.**
- **`hotfix/<n>`** — urgent production fixes. Created from `main`, merged back into both `main` and `dev`.

#### Summary of Branch Sources

- `feature/*` → from `dev`, merge into `dev`
- `bugfix/*` → from `dev`, merge into `dev`
- `release/*` → from `dev`, merge into `main` + `dev` — **auto-deploys to staging**
- `hotfix/*` → from `main`, merge into `main` + `dev` — **auto-deploys to production**

#### Version Management

Semantic versioning: `MAJOR.MINOR.PATCH`

- **MAJOR** — breaking changes
- **MINOR** — new features (backwards compatible)
- **PATCH** — bug fixes and hotfixes

Docker images are tagged with `:staging`, `:production`, `:v1.0.0`, and `:git-sha`.

**Creating tags — always use annotated tags:**

```bash
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin main --follow-tags
```

❌ `git tag v1.0.0` — lightweight tag, never use this

✅ `git tag -a v1.0.0 -m "message"` — annotated tag, always use this

**Checking current version on server:**

```bash
cat .env | grep IMAGE_TAG
docker compose ps
docker images ghcr.io/monatemedia/assessme
```

**Rolling back to a previous version:**

```bash
ssh edward@your-server
cd /home/edward/assessme
sed -i 's/^IMAGE_TAG=.*/IMAGE_TAG=v1.0.0/' .env
docker compose down
docker compose up -d
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Release Workflow

#### Deploy to Staging

```bash
git checkout dev
git pull origin dev
git checkout -b release/1.0.0
# Bump versions in composer.json, package.json
git add . && git commit -m "Bump version to 1.0.0"
git push origin release/1.0.0
# Staging deployment fires automatically
```

Test thoroughly at `https://assessme.monatemedia.com`.

#### Promote to Production

```bash
# Step 1: Merge to main
git checkout main
git pull origin main
git merge release/1.0.0
git push origin main

# Step 2: Tag the release — THIS TRIGGERS PRODUCTION DEPLOY
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin main --follow-tags

# Step 3: Merge back to dev
git checkout dev
git pull origin dev
git merge release/1.0.0
git push origin dev

# Step 4: Clean up
git branch -d release/1.0.0
git push origin --delete release/1.0.0
```

After deployment, visit `https://github.com/monatemedia/assessme/releases/tag/v1.0.0` to add release notes.

#### Hotfix

```bash
git checkout main
git pull origin main
git checkout -b hotfix/fix-description
# Fix the bug, commit
git checkout main
git merge hotfix/fix-description
git push origin main
git tag -a v1.0.1 -m "Hotfix 1.0.1: fix description"
git push origin v1.0.1
git checkout dev
git merge hotfix/fix-description
git push origin dev
git branch -d hotfix/fix-description
git push origin --delete hotfix/fix-description
```

#### Feature Branch

```bash
git checkout dev
git pull origin dev
git checkout -b feature/<name>
# work, commit
git push origin feature/<name>
# Open Pull Request into dev
```

#### Undo Last Commit

```bash
# Keep changes staged
git reset --soft HEAD~1

# Keep changes unstaged
git reset --mixed HEAD~1

# Discard all changes
git reset --hard HEAD~1

# Match remote exactly
git reset --hard origin/dev
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Server Maintenance

Run periodically or after major releases to prevent disk space issues:

```bash
# Remove stopped containers
docker container prune -f

# Clean unused images and build cache (can reclaim several GB)
docker system prune -af --volumes

# Vacuum systemd journal (keep last 50MB)
sudo journalctl --vacuum-size=50M

# Truncate large log files
sudo truncate -s 0 /var/log/syslog
sudo truncate -s 0 /var/log/kern.log
sudo truncate -s 0 /var/log/auth.log
sudo rm -f /var/log/*.gz /var/log/*.[0-9]

# Clear package cache
sudo apt-get clean

# Check disk usage
df -h /

# Check inode usage (important for many small files)
df -i /
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Port Reference

### VPS — Staging

| Service | Host Port |
|---|---|
| Web | `8250` |
| PostgreSQL | `5475` |
| Redis | `6400` |

### VPS — Production

| Service | Host Port |
|---|---|
| Web | `8260` |
| PostgreSQL | `5485` |
| Redis | `6410` |

### Local Docker Desktop

| Service | Host Port | Note |
|---|---|---|
| Web | `8001` | `http://localhost:8001` |
| PostgreSQL | `5436` | Avoids XAMPP's `5432` on Windows host |
| Redis | `6390` | |

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## User Administration with Tinker

AssessMe uses Laravel Tinker for database administration. No Adminer is included.

### 1. Access Tinker

```bash
docker exec -it assessme-web /bin/bash
php artisan tinker
```

### 2. Find a User

```php
// Find by email
>>> $user = App\Models\User::where('email', 'user@example.com')->first();

// Inspect
>>> dd($user);
```

### 3. Create a User

```php
>>> App\Models\User::create([
    'name' => 'Examiner Name',
    'email' => 'examiner@example.com',
    'password' => bcrypt('secure-password'),
    'role' => 'examiner' // options: superuser, examiner
]);
```

### 4. Update a User

```php
>>> $user = App\Models\User::where('email', 'examiner@example.com')->first();
>>> $user->update(['role' => 'superuser']);
```

### 5. Delete a User

```php
// By ID (recommended — get the ID from dd($user) first)
>>> App\Models\User::destroy(3);
=> 1

// Verify deletion
>>> App\Models\User::where('email', 'user@example.com')->first();
=> null
```

### 6. Exit

```bash
>>> exit
root@...:/var/www/html# exit
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Roadmap

## Roadmap

- [x] Infrastructure
  - [x] Laravel 13 + Filament 5 scaffolded
  - [x] Docker local development stack (web, queue, Vite, PostgreSQL, Redis)
  - [x] Auto-migrations and superuser seeder on container boot
  - [x] Volume-mounted source code for live file editing without rebuilds
  - [x] GitFlow branching model established (main, dev, feature/*, release/*, hotfix/*)
  - [x] GitHub Actions CI/CD pipeline (staging on release/*, production on version tag)
  - [x] Staging deployed to VPS behind Nginx reverse proxy with Let's Encrypt SSL
  - [x] Cover page with dark navy theme, all CTAs pointing to Filament admin login
  - [x] PRD committed to repository at `docs/prd/assessme-prd-v1.0.md`
- [ ] MVP
  - [ ] Authentication (Superuser, Examiner, Candidate via UUID link)
  - [ ] Assessment creation via UI and JSON import
  - [ ] Multiple Choice (single answer), Multiple Choice (multiple answer with negative marking)
  - [ ] Free Text and Code question types
  - [ ] Time limiting (closing datetime, from first login, none)
  - [ ] Auto-save candidate answers
  - [ ] AI marking via GPT-4o (queued background jobs via Redis)
  - [ ] AI plagiarism detection on submission
  - [ ] Examiner score override
  - [ ] Results dashboard with PDF export (Spatie)
  - [ ] In-app bell notifications
  - [ ] Email notifications (log driver in MVP — ready for Phase 2 provider)
- [ ] Phase 2
  - [ ] Multi-tenancy (Filament 5 native `HasTenants`)
  - [ ] External email delivery (Mailgun / Postmark / SES)
  - [ ] Real-time candidate monitoring (Laravel Echo + Pusher/Soketi)
  - [ ] Advanced question types (Matching Pairs, Ordering, Hotspot, File Upload)
  - [ ] AI answer key pre-flight review
  - [ ] AI-powered chat assistant with pgvector semantic search
  - [ ] Post-assessment candidate feedback (Likert scale)
  - [ ] Assessment analytics dashboard

See the [open issues](https://github.com/monatemedia/assessme/issues) for a full list of proposed features and known issues.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Contributing

We use the GitFlow Branching Model. To make a contribution, please fork the repo and create a pull request targeting the `dev` branch. You can also <a href="https://github.com/monatemedia/assessme/issues/new?labels=bug&template=bug-report---.md">report a bug</a> or <a href="https://github.com/monatemedia/assessme/issues/new?labels=enhancement&template=feature-request---.md">request a feature</a>.

1. Fork the project
2. Create your branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request into `dev`

**Best practices:**
1. Always create release branches from `dev` — never from feature branches
2. Test thoroughly on staging before merging to `main`
3. Use semantic versioning for all releases
4. Tag releases immediately after merging to `main`
5. Keep release notes in GitHub Releases
6. Never push directly to `main` — always use pull requests
7. Clean up branches after merging
8. Monitor GitHub Actions for deployment status
9. Keep `.env` files secure — never commit them

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Top Contributors

<a href="https://github.com/monatemedia/assessme/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=monatemedia/assessme" alt="contrib.rocks image" />
</a>

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## License

All rights reserved. See `LICENSE.txt` for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Contact

Edward Lebogang Baitsewe — [@MonateMedia](https://twitter.com/MonateMedia) — edward@monatemedia.com

Portfolio: [https://edward.monatemedia.com](https://edward.monatemedia.com)

Project: [https://github.com/monatemedia/assessme](https://github.com/monatemedia/assessme)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Acknowledgments

- [Best README Template](https://github.com/othneildrew/Best-README-Template)
- [Filament](https://filamentphp.com)
- [nginxproxy/nginx-proxy](https://github.com/nginx-proxy/nginx-proxy)
- [Spatie Laravel PDF](https://github.com/spatie/laravel-pdf)
- [OpenAI API](https://platform.openai.com)
- [contrib.rocks](https://contrib.rocks)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- MARKDOWN LINKS & IMAGES -->
[contributors-shield]: https://img.shields.io/github/contributors/monatemedia/assessme.svg?style=for-the-badge
[contributors-url]: https://github.com/monatemedia/assessme/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/monatemedia/assessme.svg?style=for-the-badge
[forks-url]: https://github.com/monatemedia/assessme/network/members
[stars-shield]: https://img.shields.io/github/stars/monatemedia/assessme.svg?style=for-the-badge
[stars-url]: https://github.com/monatemedia/assessme/stargazers
[issues-shield]: https://img.shields.io/github/issues/monatemedia/assessme.svg?style=for-the-badge
[issues-url]: https://github.com/monatemedia/assessme/issues
[license-shield]: https://img.shields.io/github/license/monatemedia/assessme.svg?style=for-the-badge
[license-url]: https://github.com/monatemedia/assessme/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/edwardbaitsewe
[product-screenshot]: images/screenshot.png
[Laravel.com]: https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
[Filament.com]: https://img.shields.io/badge/Filament-FDAE4B?style=for-the-badge&logo=filament&logoColor=white
[Filament-url]: https://filamentphp.com
[Livewire.com]: https://img.shields.io/badge/Livewire-4E56A6?style=for-the-badge&logo=livewire&logoColor=white
[Livewire-url]: https://livewire.laravel.com
[Postgresql.org]: https://img.shields.io/badge/postgres-%23316192.svg?style=for-the-badge&logo=postgresql&logoColor=white
[Postgresql-url]: https://www.postgresql.org
[Redis.io]: https://img.shields.io/badge/redis-%23DD0031.svg?style=for-the-badge&logo=redis&logoColor=white
[Redis-url]: https://redis.io
[Docker]: https://img.shields.io/badge/docker-%230db7ed.svg?style=for-the-badge&logo=docker&logoColor=white
[Docker-url]: https://www.docker.com
[GitHub-Actions]: https://img.shields.io/badge/github%20actions-%232671E5.svg?style=for-the-badge&logo=githubactions&logoColor=white
[GitHub-Actions-url]: https://github.com/features/actions
[GitHub.com]: https://img.shields.io/badge/github-%23121011.svg?style=for-the-badge&logo=github&logoColor=white
[GitHub.com-url]: https://github.com

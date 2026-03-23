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
    &middot;
    <a href="https://assessme.co.za">Production</a>
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
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#local-development-docker-desktop">Local Development</a></li>
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

[![Product Screenshot][product-screenshot]](https://assessme.co.za)

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

### Prerequisites

- Docker Desktop installed and running
- Git
- A GitHub account with access to `monatemedia/assessme`
- An OpenAI API key with GPT-4o access

### Local Development (Docker Desktop)

1. Clone the repository:

```bash
git clone https://github.com/monatemedia/assessme.git
cd assessme
```

2. Copy the local environment file:

```bash
cp .env.local .env
```

3. Add your OpenAI API key to `.env`:

```env
OPENAI_API_KEY=your-key-here
```

4. Start the local stack:

```bash
docker compose -f docker-compose.yml -f docker-compose.local.yml up -d
```

5. The app is available at `http://localhost:8001`

6. Run migrations on first run:

```bash
docker exec assessme-web php artisan migrate
```

7. Create a superuser via Tinker:

```bash
docker exec -it assessme-web php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role' => 'superuser'
]);
```

#### Test Credentials

| Attribute | Value | Note |
|---|---|---|
| **Email** | `admin@example.com` | Local development only |
| **Password** | `password` | Never use in staging or production |

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Deployment

### Environments

| Environment | URL | Trigger |
|---|---|---|
| Staging | `https://assessme.monatemedia.com` | Push to `release/*` branch |
| Production | `https://assessme.co.za` | Push tag `v*` to `main` |

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Required GitHub Secrets

Configure these in your GitHub repository under **Settings > Secrets and variables > Actions**.

> Ports are stored as GitHub Secrets to make the app portable across servers. To move to a new server, update the secrets — no code changes required.

#### Staging Secrets

| Secret | Description | Example |
|---|---|---|
| `PAT` | GitHub Personal Access Token (GHCR push) | `ghp_xxxxxxxxxxxx` |
| `SSH_HOST` | VPS IP address | `82.29.190.105` |
| `SSH_USER` | SSH username | `edward` |
| `SSH_PRIVATE_KEY` | SSH private key for VPS | `-----BEGIN OPENSSH PRIVATE KEY-----` |
| `WORK_DIR` | Project directory on VPS | `/home/edward/assessme` |
| `STAGING_DOCKER_WEB_PORT` | Host port for web container | `8250` |
| `STAGING_DOCKER_POSTGRES_PORT` | Host port for PostgreSQL | `5475` |
| `STAGING_DOCKER_REDIS_PORT` | Host port for Redis | `6400` |

#### Production Secrets

| Secret | Description | Example |
|---|---|---|
| `PRODUCTION_SSH_KEY` | SSH private key for production server | `-----BEGIN OPENSSH PRIVATE KEY-----` |
| `PRODUCTION_HOST` | Production server IP | `82.29.190.105` |
| `PRODUCTION_USER` | SSH username | `edward` |
| `PRODUCTION_WORK_DIR` | Project directory on production server | `/home/edward/assessme` |
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

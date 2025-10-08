# 🐳 Docker Configuration - SIMPLIFIED

## 📁 Current Structure (Simplified)

### ✅ **Active Files (USE THESE):**

-   `../Dockerfile` - Ultra simple Dockerfile
-   `../docker-compose.simple.yml` - Main docker compose file
-   `../scripts/setup-simple.sh` - Quick setup script
-   `mysql/` - Database initialization (if exists)

### ❌ **Deprecated Files (CAN BE REMOVED):**

-   `nginx.conf` - ❌ Not needed (webdevops handles this)
-   `default.conf` - ❌ Not needed
-   `php-dev.ini` - ❌ Not needed (no Xdebug)
-   `php-prod.ini` - ❌ Not needed
-   `start.sh` - ❌ Not needed (webdevos handles startup)
-   `supervisord.conf` - ❌ Deprecated

## 🚀 How to Use

```bash
# Quick setup (recommended)
./scripts/setup-simple.sh

# Manual setup
docker-compose -f docker-compose.simple.yml up --build -d
```

## 🧹 Cleanup

To remove deprecated files:

```bash
./scripts/cleanup-docker.sh
```

## 🏗️ Architecture

-   **Image:** `webdevops/php-nginx:8.3-alpine` (includes PHP + Nginx)
-   **Services:** App + MySQL + phpMyAdmin
-   **No Xdebug:** Faster builds and simpler setup
-   **No Redis:** Uses database for cache/sessions

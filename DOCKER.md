# 🐳 Docker Setup - Pokemon API

Esta guía te ayuda a ejecutar la Pokemon API usando Docker para un entorno de desarrollo consistente.

## 📋 Prerrequisitos

-   [Docker](https://www.docker.com/get-started) instalado
-   [Docker Compose](https://docs.docker.com/compose/install/) instalado
-   Git para clonar el repositorio

## 🚀 Inicio Rápido

### Opción 1: Script Automático (Recomendado)

**Windows:**

```cmd
scripts\setup-docker.bat
```

**Linux/Mac:**

```bash
chmod +x scripts/setup-docker.sh
./scripts/setup-docker.sh
```

### Opción 2: Manual

1. **Clonar y configurar:**

    ```bash
    git clone <https://github.com/itdyaingenieria/api-rest-pokemon.git>
    cd api-rest-pokemon
    cp .env.docker .env
    ```

2. **Iniciar servicios:**

    ```bash
    docker-compose up -d
    ```

3. **Configurar Laravel:**
    ```bash
    docker-compose exec app composer install
    docker-compose exec app php artisan key:generate
    docker-compose exec app php artisan jwt:secret
    docker-compose exec app php artisan migrate
    ```

## 🔗 Servicios Disponibles

| Servicio       | URL                   | Descripción              |
| -------------- | --------------------- | ------------------------ |
| **API**        | http://localhost:8000 | Pokemon API Backend      |
| **phpMyAdmin** | http://localhost:8080 | Gestión de base de datos |
| **MySQL**      | localhost:3306        | Base de datos            |
| **Redis**      | localhost:6379        | Cache y sesiones         |

## 🗃️ Credenciales de Base de Datos

-   **Host:** localhost (desde host) / db (desde contenedores)
-   **Puerto:** 3306
-   **Database:** pokemon_api
-   **Usuario:** pokemon_user
-   **Password:** pokemon_password
-   **Root Password:** root_password

## 📋 Comandos Útiles

### Gestión de Contenedores

```bash
# Iniciar todos los servicios
docker-compose up -d

# Ver logs en tiempo real
docker-compose logs -f app

# Parar todos los servicios
docker-compose down

# Rebuild completo
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Comandos Laravel

```bash
# Acceder al contenedor de la aplicación
docker-compose exec app bash

# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Limpiar cachés
docker-compose exec app php artisan cache:clear

# Ver rutas
docker-compose exec app php artisan route:list

# Acceder a Tinker
docker-compose exec app php artisan tinker
```

### Base de Datos

```bash
# Backup de la base de datos
docker-compose exec db mysqldump -u pokemon_user -ppokemon_password pokemon_api > backup.sql

# Restaurar backup
docker-compose exec -i db mysql -u pokemon_user -ppokemon_password pokemon_api < backup.sql

# Conectar a MySQL directamente
docker-compose exec db mysql -u pokemon_user -ppokemon_password pokemon_api
```

## 🏗️ Arquitectura del Stack

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Laravel App   │    │     MySQL       │    │     Redis       │
│   (PHP 8.3)     │◄──►│   (Database)    │    │    (Cache)      │
│   Port: 8000    │    │   Port: 3306    │    │  Port: 6379     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │
         ▼
┌─────────────────┐
│   phpMyAdmin    │
│  (DB Manager)   │
│   Port: 8080    │
└─────────────────┘
```

## 🔧 Configuración de Desarrollo

### Xdebug (Debugging)

El contenedor incluye Xdebug configurado para desarrollo:

```php
// En tu IDE, configura:
Host: localhost
Port: 9003
Path mappings: ./api-rest -> /var/www/html
```

### Variables de Entorno

Edita `.env` para personalizar:

```env
# Configuración de la aplicación
APP_ENV=local
APP_DEBUG=true

# Base de datos (ya configurada para Docker)
DB_HOST=db
DB_DATABASE=pokemon_api

# Cache Redis (ya configurado para Docker)
REDIS_HOST=redis
CACHE_DRIVER=redis
```

## 🚀 Despliegue en Producción

Para producción, cambia en `docker-compose.yml`:

```yaml
# En el servicio app:
build:
    target: production # Cambiar de 'development' a 'production'

environment:
    - APP_ENV=production
    - APP_DEBUG=false
```

## 🛠️ Solución de Problemas

### Puerto ya en uso

```bash
# Verificar puertos en uso
netstat -an | grep :8000
# O cambiar el puerto en docker-compose.yml
```

### Problemas de permisos

```bash
# Arreglar permisos de storage
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 755 storage bootstrap/cache
```

### Base de datos no conecta

```bash
# Verificar que el contenedor MySQL esté corriendo
docker-compose ps
docker-compose logs db

# Recrear el volumen de base de datos
docker-compose down -v
docker-compose up -d
```

### Limpiar todo y empezar de nuevo

```bash
# ⚠️  CUIDADO: Esto borra TODOS los datos
docker-compose down -v
docker system prune -a
./scripts/setup-docker.sh  # o setup-docker.bat en Windows
```

## 🎯 Endpoints de Testing

Una vez que todo esté funcionando:

```bash
# Health check
curl http://localhost:8000/api/status

# Lista de Pokemon
curl http://localhost:8000/api/pokemon?limit=5

# Registro de usuario
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"Password123","password_confirmation":"Password123"}'
```

## 💡 Beneficios de esta Configuración Docker

-   ✅ **Entorno idéntico** para todo el equipo
-   ✅ **Setup en minutos** en cualquier máquina
-   ✅ **Aislamiento completo** del sistema host
-   ✅ **Escalabilidad** fácil para múltiples instancias
-   ✅ **Desarrollo y producción** con la misma configuración
-   ✅ **Cache Redis** para mejor performance
-   ✅ **phpMyAdmin** incluido para gestión de DB

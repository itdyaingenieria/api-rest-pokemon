# 📝 Scripts de Despliegue - Pokemon API

Esta documentación explica todos los scripts disponibles para el despliegue y mantenimiento de la Pokemon API.

## 🚀 Scripts Principales

### `./scripts/setup-simple.sh` (⭐ Recomendado)

**Uso principal para desarrollo rápido**

```bash
./scripts/setup-simple.sh
```

**Qué hace:**

-   ✅ Para contenedores existentes
-   ✅ Limpia cache Docker
-   ✅ Build sin cache (25-30 segundos)
-   ✅ Inicia todos los servicios
-   ✅ Ejecuta migraciones
-   ✅ Configura Laravel automáticamente
-   ✅ Limpia cache de Laravel

**Tiempo estimado:** 30-60 segundos  
**Servicios incluidos:** App, MySQL, phpMyAdmin, Redis

---

### `./scripts/test-with-dummy-redis.sh`

**Para debug y verificación completa**

```bash
./scripts/test-with-dummy-redis.sh
```

**Qué hace:**

-   🔍 Verifica configuraciones internas
-   🧪 Testa conexiones Redis y Database
-   📊 Muestra configuraciones de cache/session
-   🌐 Prueba endpoints de API
-   📋 Muestra logs detallados

**Cuándo usarlo:** Cuando hay problemas o para debug

---

### `./scripts/cleanup-docker.sh`

**Limpieza de archivos Docker antiguos**

```bash
./scripts/cleanup-docker.sh
```

**Qué hace:**

-   🗑️ Elimina archivos Docker deprecados
-   📁 Limpia configuraciones antiguas
-   🧹 Modo interactivo (pregunta antes de eliminar)

**Cuándo usarlo:** Para limpiar el proyecto de archivos antiguos

---

## 📋 Servicios Desplegados

| Servicio        | Puerto | URL                   | Descripción   |
| --------------- | ------ | --------------------- | ------------- |
| **API Laravel** | 8000   | http://localhost:8000 | API principal |
| **MySQL**       | 3307   | localhost:3307        | Base de datos |
| **phpMyAdmin**  | 8080   | http://localhost:8080 | Gestión DB    |
| **Redis**       | 6379   | localhost:6379        | Cache (dummy) |

## 🔧 Comandos Útiles Post-Deploy

### Gestión de servicios:

```bash
# Ver estado
docker-compose -f docker-compose.simple.yml ps

# Ver logs en tiempo real
docker-compose -f docker-compose.simple.yml logs -f app

# Reiniciar servicios
docker-compose -f docker-compose.simple.yml restart

# Parar todo
docker-compose -f docker-compose.simple.yml down
```

### Acceso a contenedores:

```bash
# Entrar al contenedor de la app
docker-compose -f docker-compose.simple.yml exec app bash

# Ejecutar comandos Laravel
docker-compose -f docker-compose.simple.yml exec app php artisan migrate:status
docker-compose -f docker-compose.simple.yml exec app php artisan route:list
```

### Base de datos:

```bash
# Backup de BD
docker-compose -f docker-compose.simple.yml exec db mysqldump -u pokemon_user -ppokemon_password pokemon_api > backup.sql

# Restaurar BD
docker-compose -f docker-compose.simple.yml exec -T db mysql -u pokemon_user -ppokemon_password pokemon_api < backup.sql
```

## 🧪 Tests de Verificación

### Test básico de API:

```bash
curl http://localhost:8000/api/status
```

### Test de autenticación:

```bash
# Registro
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123"}'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

### Test de Pokémon:

```bash
# Listar Pokémon
curl http://localhost:8000/api/pokemon

# Pokémon específico
curl http://localhost:8000/api/pokemon/pikachu
```

## 🆘 Resolución de Problemas

### Problema: Puerto ocupado

```bash
# Cambiar puerto en docker-compose.simple.yml
ports:
  - "8001:80"  # Cambiar 8000 por 8001
```

### Problema: Permisos de scripts

```bash
# Linux/Mac - dar permisos de ejecución
chmod +x scripts/*.sh
```

### Problema: Contenedor no inicia

```bash
# Ver logs detallados
docker-compose -f docker-compose.simple.yml logs app

# Rebuild completo
docker-compose -f docker-compose.simple.yml down --volumes
./scripts/setup-simple.sh
```

### Problema: Base de datos no conecta

```bash
# Verificar que MySQL esté corriendo
docker-compose -f docker-compose.simple.yml ps

# Ver logs de MySQL
docker-compose -f docker-compose.simple.yml logs db
```

## 📊 Comparación de Métodos

| Método                     | Tiempo | Uso Recomendado      | Nivel        |
| -------------------------- | ------ | -------------------- | ------------ |
| `setup-simple.sh`          | 30s    | Desarrollo diario    | Principiante |
| `test-with-dummy-redis.sh` | 60s    | Debug/Testing        | Intermedio   |
| Manual Docker              | 5min+  | Configuración custom | Avanzado     |

---

## 🎯 Flujo de Trabajo Recomendado

1. **Primera vez:**

    ```bash
    git clone <repo>
    cd api-rest-pokemon
    ./scripts/setup-simple.sh
    ```

2. **Desarrollo diario:**

    ```bash
    ./scripts/setup-simple.sh  # Si hay cambios
    # O simplemente:
    docker-compose -f docker-compose.simple.yml up -d
    ```

3. **Problemas/Debug:**

    ```bash
    ./scripts/test-with-dummy-redis.sh
    ```

4. **Limpieza periódica:**
    ```bash
    ./scripts/cleanup-docker.sh
    ```

**¡Happy coding! 🚀**

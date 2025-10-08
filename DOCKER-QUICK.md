# 🚀 POKEMON API - Inicio Rápido

## ⚡ Setup Ultra Rápido (30 segundos)

```bash
# Comando único - configuración completa
./scripts/setup-simple.sh
```

**¡Eso es todo!** 🎉

## 📋 Servicios incluidos:

-   ✅ **Pokemon API** - http://localhost:8000
-   ✅ **MySQL Database** - localhost:3307
-   ✅ **phpMyAdmin** - http://localhost:8080
-   ✅ **Redis** - localhost:6379 (soporte interno)

## 🧪 Verificar que funciona:

```bash
# Test rápido de la API
curl http://localhost:8000/api/status

# O abrir en navegador:
# http://localhost:8000/api/status
```

## 📁 Estructura de archivos optimizada:

```
📦 api-rest-pokemon/
├── 🐳 docker-compose.simple.yml    # Configuración ultra simple
├── 🐳 Dockerfile                   # Imagen optimizada (25s build)
├── ⚙️ .env.docker                  # Configuración para contenedor
└── 📁 scripts/
    ├── 🚀 setup-simple.sh          # Setup automático
    ├── 🧪 test-with-dummy-redis.sh  # Test completo
    └── 🧹 cleanup-docker.sh        # Limpieza
```

## 🔧 Comandos útiles:

### Gestión básica:

```bash
# Ver logs
docker-compose -f docker-compose.simple.yml logs -f app

# Reiniciar servicios
docker-compose -f docker-compose.simple.yml restart

# Parar todo
docker-compose -f docker-compose.simple.yml down
```

### Acceso al contenedor:

```bash
# Entrar al contenedor de la app
docker-compose -f docker-compose.simple.yml exec app bash

# Ejecutar comandos artisan
docker-compose -f docker-compose.simple.yml exec app php artisan migrate:status
```

### Limpieza completa:

```bash
# Limpiar archivos Docker antiguos
./scripts/cleanup-docker.sh

# Rebuild completo (si hay problemas)
docker-compose -f docker-compose.simple.yml down --volumes
./scripts/setup-simple.sh
```

## 🎯 Características optimizadas:

-   ⚡ **Build súper rápido**: ~25 segundos (vs 5+ minutos antes)
-   🗄️ **Database automática**: Migraciones y seeds incluidos
-   🔐 **Configuración segura**: JWT y APP_KEY autogenerados
-   📦 **Sin dependencias**: Solo Docker necesario
-   🧹 **Sin Redis**: Todo en database para simplicidad
-   🔄 **Auto-restart**: Servicios configurados para reinicio automático

## 🆘 Resolución de problemas:

### Si el puerto 8000 está ocupado:

```bash
# Cambiar puerto en docker-compose.simple.yml:
ports:
  - "8001:80"  # Usar puerto 8001
```

### Si MySQL puerto 3307 está ocupado:

```bash
# Cambiar puerto MySQL:
ports:
  - "3308:3306"  # Usar puerto 3308
```

### Si hay problemas de permisos:

```bash
# En Linux/Mac, asegurar permisos de ejecución:
chmod +x scripts/*.sh
```

---

**⚡ Tiempo total de setup: 30-60 segundos**  
**🚀 De 0 a API funcionando en menos de 1 minuto**

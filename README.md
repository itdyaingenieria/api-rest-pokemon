<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## API Pokémon - Backend Laravel

Esta aplicación Laravel proporciona una API backend robusta que sirve como proxy a la PokeAPI, implementando autenticación, gestión de favoritos, y siguiendo las mejores prácticas de PokeAPI.

### 🚀 Características

-   **Proxy PokeAPI** - Proxy optimizado a https://pokeapi.co/ siguiendo las mejores prácticas oficiales
-   **Autenticación JWT** - Autenticación de usuario segura con aplicación de sesión única
-   **Favoritos Pokémon** - Guardar y gestionar Pokémon favoritos con relaciones de usuario
-   **Caché Avanzado** - Estrategia de caché agresiva para respetar la política de uso justo de PokeAPI
-   **Búsqueda y Filtrado** - Buscar Pokémon por nombre, filtrar por tipo
-   **Gestión de Contraseñas** - Flujo completo de restablecimiento de contraseña con notificaciones por correo
-   **Listo para MySQL** - Todas las migraciones optimizadas para base de datos MySQL

### 📋 Endpoints de la API

#### Autenticación

-   `POST /api/auth/register` - Registro de usuario con reglas de contraseña fuertes
-   `POST /api/auth/login` - Inicio de sesión de usuario con token JWT
-   `POST /api/auth/logout` - Cierre de sesión seguro
-   `GET /api/auth/me` - Obtener información del usuario autenticado
-   `POST /api/auth/refresh` - Refrescar token JWT
-   `POST /api/auth/password/forgot` - Solicitar restablecimiento de contraseña
-   `POST /api/auth/password/reset` - Restablecer contraseña con token

#### Pokémon (Proxy PokeAPI)

-   `GET /api/pokemon` - Listar Pokémon (soporta `?search=`, `?limit=`, `?offset=`)
-   `GET /api/pokemon/{id}` - Obtener datos detallados de Pokémon (por ID o nombre)
-   `GET /api/pokemon/type/{type}` - Obtener Pokémon por tipo

#### Favoritos (Protegido)

-   `GET /api/favorites` - Listar Pokémon favoritos del usuario
-   `POST /api/favorites` - Agregar Pokémon a favoritos
-   `POST /api/favorites/batch` - Agregar múltiples Pokémon a favoritos
-   `DELETE /api/favorites/{id}` - Eliminar favorito

### ⚙️ Mejores Prácticas de PokeAPI Implementadas

Siguiendo la [documentación de PokeAPI](https://pokeapi.co/docs/v2) y la [guía de mejores prácticas de Zuplo](https://zuplo.com/learning-center/pokeapi):

1. **Caché Agresivo** - TTL de caché de 1-4 horas para reducir llamadas a la API
2. **Paginación** - Manejo adecuado de offset/limit (máximo 100 por solicitud)
3. **Manejo de Errores** - Manejo integral de errores con códigos de estado HTTP apropiados
4. **Respeto por Límites de Tasa** - Retrasos incorporados y lógica de reintento
5. **Estructura de Datos Optimizada** - Formato de respuesta limpio y amigable para el frontend
6. **Funcionalidad de Búsqueda** - Búsqueda local eficiente con caché
7. **Solicitudes Concurrentes** - Soporte para obtención por lotes de Pokémon

### 🛠️ Configuración e Instalación

#### 🐳 Opción A: Docker (Recomendado - Configuración Ultra Rápida)

```bash
git clone <https://github.com/itdyaingenieria/api-rest-pokemon.git>
cd api-rest-pokemon

# Despliegue súper simple (Linux/Mac/WSL)
./scripts/setup-simple.sh

# Windows PowerShell
.\scripts\setup-simple.sh
```

**✅ ¡Listo en menos de 1 minuto!**

🌐 **API**: http://localhost:8000  
🗄️ **phpMyAdmin**: http://localhost:8080  
🧪 **Test**: `curl http://localhost:8000/api/status`

📖 **Guías disponibles:**  
├── [DOCKER-QUICK.md](DOCKER-QUICK.md) - Inicio rápido  
├── [SCRIPTS.md](SCRIPTS.md) - Todos los scripts explicados  
└── [DOCKER.md](DOCKER.md) - Configuración avanzada

#### 💻 Opción B: Instalación Local

1. **Clonar e instalar dependencias**

    ```bash
    git clone <https://github.com/itdyaingenieria/api-rest-pokemon.git>
    cd api-rest-pokemon
    composer install
    ```

2. **Configuración del entorno**

    ```bash
    cp .env.example .env
    php artisan key:generate     # Generar clave de encriptación Laravel (REQUERIDO)
    php artisan jwt:secret       # Generar clave secreta JWT
    php artisan config:clear     # Limpiar caché de configuración
    ```

3. **Configuración de la base de datos**

    ```bash
    # Configura tu base de datos MySQL en .env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=tu_base_de_datos
    DB_USERNAME=tu_usuario
    DB_PASSWORD=tu_contraseña

    # Ejecutar migraciones
    php artisan migrate
    ```

4. **Configuración de PokeAPI (Opcional)**

    ```bash
    # La configuración predeterminada funciona sin modificaciones
    POKEAPI_CACHE_LIST_TTL=60      # Caché de listas por 1 hora
    POKEAPI_CACHE_DETAIL_TTL=120   # Caché de detalles por 2 horas
    POKEAPI_MAX_LIMIT=100          # Máximo de elementos por solicitud
    ```

5. **Iniciar el servidor de desarrollo**
    ```bash
    php artisan serve
    # La API estará disponible en http://localhost:8000
    ```

### 📚 Uso con Postman

1. Importa la colección desde `docs/pokemon.postman_collection.json`
2. Establece la variable `baseUrl` con tu endpoint de API (por defecto: `http://127.0.0.1:8000`)
3. Registra un nuevo usuario o inicia sesión para obtener el token de autenticación
4. El token se establecerá automáticamente para los endpoints protegidos

### 🗃️ Esquema de Base de Datos

La aplicación crea las siguientes tablas principales:

-   `users` - Cuentas de usuario con autenticación
-   `favorites` - Pokémon favoritos del usuario (id, nombre, imagen, descripción)
-   `password_reset_tokens` - Tokens de restablecimiento de contraseña
-   `personal_access_tokens` - Tokens de API (Laravel Sanctum)

### 🔧 Notas de Desarrollo

-   **Laravel 12** - Utiliza las últimas características de Laravel y registro de middleware
-   **Optimizado para MySQL** - Todas las migraciones específicas de PostgreSQL han sido eliminadas/neutralizadas
-   **Autenticación JWT** - Utiliza el paquete `php-open-source-saver/jwt-auth`
-   **Sesión Única** - Los usuarios solo pueden tener una sesión activa a la vez
-   **Response Trait** - Formato de respuesta API consistente en todos los endpoints

## Patrocinadores de Laravel

Nos gustaría extender nuestro agradecimiento a los siguientes patrocinadores por financiar el desarrollo de Laravel. Si estás interesado en convertirte en patrocinador, por favor visita el [programa Laravel Partners](https://partners.laravel.com).

### Socios Premium

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[WebReinvent](https://webreinvent.com/)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Jump24](https://jump24.co.uk)**
-   **[Redberry](https://redberry.international/laravel/)**
-   **[Active Logic](https://activelogic.com)**
-   **[byte5](https://byte5.de)**
-   **[OP.GG](https://op.gg)**

## Contribuciones

¡Gracias por considerar contribuir al framework Laravel! La guía de contribución puede encontrarse en la [documentación de Laravel](https://laravel.com/docs/contributions).

## Código de Conducta

Para asegurar que la comunidad de Laravel sea acogedora para todos, por favor revisa y cumple con el [Código de Conducta](https://laravel.com/docs/contributions#code-of-conduct).

## Vulnerabilidades de Seguridad

Si descubres una vulnerabilidad de seguridad en Laravel, por favor envía un correo electrónico a Taylor Otwell vía [taylor@laravel.com](mailto:taylor@laravel.com). Todas las vulnerabilidades de seguridad serán atendidas prontamente.

## Licencia

El framework Laravel es software de código abierto licenciado bajo la [licencia MIT](https://opensource.org/licenses/MIT).

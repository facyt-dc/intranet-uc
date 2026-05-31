# Intranet UC - Departamento de Computación

Sistema de gestión administrativa para el Departamento de Computación de la Universidad de Carabobo (FACYT). Este sistema centraliza procesos internos mediante una arquitectura modular escalable.

Actualmente incluye los siguientes módulos:

- **Core:** Gestión de Usuarios, Autenticación y Roles.
- **Modulo de Agenda de consejos:** Gestión de consejos de departamento, puntos de discusión y sistema de votación en tiempo real.
- **Funcionalidad de documentos**
- **Modulo de tesistas**

## Stack Tecnológico

- **Backend:** Laravel 11, PHP 8.3+, MySQL.
- **Frontend:** React, Inertia.js, Material UI.
- **Arquitectura:** Modular (nwidart/laravel-modules).
- **Otros:** Mailpit se puede utilizar para pruebas de correo.

## Documentación Técnica

Para entender la profundidad del sistema, consulta las siguientes guías en la carpeta `docs/`:

- 🏗 **[Arquitectura Modular](docs/ARCHITECTURE.md):** Entiende cómo coexisten el Core y los Módulos sin acoplamiento fuerte.
- 🎨 **[Frontend e i18n](docs/FRONTEND_GUIDE.md):** Cómo manejamos las vistas de Inertia y las traducciones dinámicas dentro de los módulos.
- 🔐 **[Base de Datos y Permisos](docs/DATABASE_ROLES.md):** Esquema global y manejo de roles con Spatie.
- 👨‍💻 **[Manual de Módulos](docs/MODULES_COOKBOOK.md):** Guía para crear nuevos módulos o migrar funcionalidades antiguas.

## Requerimientos

- PHP 8.3 o superior (Extensiones: `pdo_mysql`, `openssl`, `intl`).
- Node.js 20+.
- MySQL 8.0+.
- Composer.

## Instalación en Local

1. **Clonar el repositorio**

    ```bash
    git clone <repo-url>
    cd Intranet-UC
    ```

2. **Instalar dependencias**

    ```bash
    composer install
    npm install
    ```

3. **Configurar entorno**
   Copia el archivo `.env.example` a `.env` y configura tu base de datos:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Base de Datos y Seeders**
   Ejecuta las migraciones y los seeders (esto ejecutará tanto los datos del Core como los de los Módulos agregados a seeder principal):

    ```bash
    php artisan migrate --seed
    ```

5. **Iniciar servicios**
   Necesitarás dos terminales:

    ```bash
    # Terminal 1 (Servidor Laravel)
    php artisan serve

    # Terminal 2 (Vite Frontend)
    npm run dev
    ```

6. **Worker de Colas (Importante para notificaciones)**
   Para que el sistema de notificaciones por correo funcione, el worker debe estar activo:
    ```bash
    php artisan queue:work
    ```

## Licencia

The Laravel framework is open-sourced software licensed under the MIT license.


## README Docker (WIP)

# PHP Docker Development Environment

## Installation

### Requirements

- Docker

### Environment Variables

Copy the example environment file and customize it as needed:

```bash
cp .env.example .env
```

### Setup

Build the Docker images:

```bash
docker compose build
```

## Usage

### Start Containers

Start all services in the background:

```bash
docker compose up
```

Access the application at `http://localhost:8000`.

### Composer Installation
To install Composer dependencies, run the following command:

```bash
docker compose exec app composer install
```

### Key Generation
Generate the application key:

```bash
docker compose exec app php artisan key:generate
```

### Database Migrations and Seeding
Run the migrations and seed the database:

```bash
docker compose exec app php artisan migrate --seed
```

### Running Composer Commands

To run Composer commands inside the running PHP container, for example, to dump the autoload files, use:

```bash
docker compose exec app composer dump-autoload
```

### Adding New NPM Packages

If you need to add new NPM packages, you should restart the Node container to ensure that the new dependencies are installed correctly. You can do this with the following command:

```bash
docker compose restart node
```
# Intranet

## Setup

### Requirements

- **Docker** (Docker Desktop or Docker Engine)

### Production

- Copy the example environment file for the Docker containers and customize it as needed:

```bash
cp .env.example .env
```

- Copy the example environment file for the application and customize it as needed:

```bash
cp src/.env.example src/.env
```

- Build the Docker images:

```bash
docker compose -f docker-compose.prod.yaml build
```

- Start containers in detached mode:

```bash
docker compose -f docker-compose.prod.yaml up -d
```

- Generate the application key:

```bash
docker compose -f docker-compose.prod.yaml exec app php artisan key:generate
```

- Run the database migrations and seeders:

```bash
docker compose -f docker-compose.prod.yaml exec app php artisan migrate --seed
```

- Access the application at `http://localhost:8000`.

### Development

- Copy the example environment file for the Docker containers and customize it as needed:

```bash
cp .env.example .env
```

- Copy the example environment file for the application and customize it as needed:

```bash
cp src/.env.example src/.env
```

- Build the Docker images:

```bash
docker compose -f docker-compose.dev.yaml build
```

- Start containers in detached mode:

```bash
docker compose -f docker-compose.dev.yaml up -d
```

- Install the PHP dependencies:

```bash
docker compose -f docker-compose.dev.yaml exec app composer install
```

- Generate the application key:

```bash
docker compose -f docker-compose.dev.yaml exec app php artisan key:generate
```

- Run the database migrations and seeders:

```bash
docker compose -f docker-compose.dev.yaml exec app php artisan migrate --seed
```

- Access the application at `http://localhost:8000` and the Vite development server at `http://localhost:5173`.
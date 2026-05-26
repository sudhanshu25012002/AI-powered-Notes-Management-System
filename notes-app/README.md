# AI-Powered Notes Management System

A Laravel 12 REST API + Blade frontend for managing notes with **AI-powered semantic search** and **automatic summaries** using Google Gemini.

---

## Features

- ✅ Full CRUD for notes (title, content, tags)
- ✅ **Semantic search** using Gemini `gemini-embedding-001` embeddings + cosine similarity
- ✅ **AI summaries** using Gemini `gemini-2.5-flash`
- ✅ Redis caching (summaries cached 24h, list cache auto-invalidated)
- ✅ Swagger / OpenAPI documentation at `/api/documentation`
- ✅ Blade frontend with Alpine.js (create, view, edit, list + inline search)
- ✅ Soft deletes
- ✅ Docker support (PHP-FPM + Nginx + MySQL + Redis)
- ✅ 30 tests (unit + feature), 81 assertions

---

## Requirements

| Tool     | Version  |
|----------|----------|
| PHP      | 8.2+     |
| Composer | 2.x      |
| MySQL    | 8.0+     |
| Redis    | 7.x      |
| Laravel  | 12.x     |

---

## Quick Start (Local with Valet)

```bash
# 1. Clone and enter the app directory
cd notes-app

# 2. Install PHP dependencies
composer install

# 3. Copy and configure environment
cp .env.example .env
php artisan key:generate

# 4. Add your Gemini API key
# Edit .env: GEMINI_API_KEY=your_key_here
# Get one free at: https://aistudio.google.com/app/apikey

# 5. Create the database
mysql -u root -p -e "CREATE DATABASE notes_db;"

# 6. Run migrations and seed sample notes
php artisan migrate --seed

# 7. Link with Laravel Valet (Or use PHP built-in server)
# If using Valet:
valet link notes-app
# Visit: http://notes-app.test

# If running locally without Valet:
php artisan serve
# Visit: http://127.0.0.1:8000
```

---

## Docker Setup

```bash
# 1. Copy environment file
cp .env.example .env
# Edit .env to set GEMINI_API_KEY

# 2. Build the containers
docker compose build

# 3. Start all services
docker compose up -d

# 4. Set directory permissions (fixes 500 error on Blade view compilation inside container)
chmod -R 777 storage bootstrap/cache

# 5. Run migrations and seed database inside container
docker compose exec app php artisan migrate --seed

# App available at: http://localhost:8000
# API Docs available at: http://localhost:8000/api/documentation
```

### Stopping the Services
* **Stop and remove containers & networks** (retains database volume storage):
  ```bash
  docker compose down
  ```
* **Pause / Stop services** (without removing container instances):
  ```bash
  docker compose stop
  ```

---

## Environment Variables

| Variable                     | Description                         | Default                                  |
|------------------------------|-------------------------------------|------------------------------------------|
| `GEMINI_API_KEY`             | **Required.** Your Gemini API key   | —                                        |
| `GEMINI_CHAT_MODEL`          | Model for summaries                 | `gemini-2.5-flash`                       |
| `GEMINI_EMBEDDING_MODEL`     | Model for embeddings                | `gemini-embedding-001`                   |
| `DB_DATABASE`                | MySQL database name                 | `notes_db`                               |
| `REDIS_HOST`                 | Redis host                          | `127.0.0.1` (local) / `redis` (Docker)  |
| `L5_SWAGGER_GENERATE_ALWAYS` | Auto-regenerate Swagger docs        | `true`                                   |

---

## API Endpoints

### Notes CRUD

| Method   | Endpoint          | Description             |
|----------|-------------------|-------------------------|
| `GET`    | `/api/notes`      | List notes (paginated)  |
| `POST`   | `/api/notes`      | Create a note           |
| `GET`    | `/api/notes/{id}` | Get a single note       |
| `PUT`    | `/api/notes/{id}` | Update a note           |
| `DELETE` | `/api/notes/{id}` | Soft delete a note      |

### AI Endpoints

| Method | Endpoint                      | Description                              |
|--------|-------------------------------|------------------------------------------|
| `GET`  | `/api/search?q={query}`       | Semantic search using Gemini embeddings  |
| `POST` | `/api/notes/{id}/summary`     | Generate or retrieve cached AI summary   |

### Swagger UI

```
http://notes-app.test/api/documentation
```

---

## Web UI Routes

| Route              | View         |
|--------------------|--------------|
| `/notes`           | Notes list   |
| `/notes/create`    | Create form  |
| `/notes/{id}`      | Note detail  |
| `/notes/{id}/edit` | Edit form    |

---

## Architecture

```
app/
├── Http/Controllers/
│   ├── Api/
│   │   ├── NoteController.php        # CRUD endpoints
│   │   ├── SearchController.php      # Semantic search
│   │   └── SummaryController.php     # AI summaries
│   ├── Web/
│   │   └── NoteWebController.php     # Blade view routes
│   └── Controller.php               # Swagger base annotations
├── Services/
│   ├── GeminiService.php             # Gemini API client (embed + summarise)
│   ├── EmbeddingService.php          # Generate & persist embeddings
│   ├── SemanticSearchService.php     # Cosine similarity ranking
│   ├── SummaryService.php            # AI summary with 24h Redis cache
│   └── NoteService.php              # CRUD + cache invalidation logic
├── Jobs/
│   └── GenerateEmbeddingJob.php      # Queue-ready embedding job
├── Helpers/
│   └── VectorMath.php               # Pure PHP cosine similarity
├── Models/
│   └── Note.php                     # Eloquent model + search scopes
└── Exceptions/
    └── GeminiException.php
```

---

## Running Tests

```bash
# Create the test database first
mysql -u root -p -e "CREATE DATABASE notes_db_test; GRANT ALL ON notes_db_test.* TO 'notes_user'@'localhost';"

# Run all tests
./vendor/bin/phpunit --configuration phpunit.xml --testdox

# Unit tests only
./vendor/bin/phpunit --testsuite Unit

# Feature tests only
./vendor/bin/phpunit --testsuite Feature
```

**Result:** 30 tests, 81 assertions, 0 failures.

---

## How Semantic Search Works

1. User submits a query (e.g. "healthy eating habits")
2. Query is embedded via Gemini `gemini-embedding-001` → 768-dimension float vector
3. All notes with stored embeddings are fetched from MySQL
4. **Cosine similarity** computed between query and each note vector
5. Notes with score ≥ 0.45 returned, sorted by score descending (max 10)
6. Results include `similarity_score` field

**Embeddings** are generated automatically on note create/update via `GenerateEmbeddingJob` and stored as JSON in a `LONGTEXT` column.

---

## How AI Summaries Work

1. `POST /api/notes/{id}/summary`
2. Cache key: `note_summary_{id}` checked in Redis
3. **Cache hit** → return cached summary (`"cached": true`)
4. **Cache miss** → call Gemini Flash → store in Redis for 24 hours
5. Cache invalidated automatically on note **update** or **delete**

---

## License

MIT

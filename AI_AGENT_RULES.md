# AI AGENT RULES & CONTEXT GUIDE
## AI-Powered Notes Management System
### Read This First — Every Agent / Model Must Read This Before Doing Anything

---

> This file exists so that any AI model (Antigravity, Claude, GPT, Gemini, etc.)
> that gets added to this project immediately understands:
> - what we are building
> - what decisions have already been made
> - what tools and resources are available
> - what rules must be followed
> - what has already been done
> - what still needs to be done

---

# SECTION 1 — PROJECT OVERVIEW

## What We Are Building

An **AI-Powered Notes Management System** built with Laravel 12.

Core features:
- Full CRUD for notes (create, read, update, delete)
- Semantic search using real vector embeddings
- AI-generated summaries for individual notes
- Redis caching layer
- Simple Blade + Alpine.js frontend
- Swagger API documentation
- Docker containerization
- PHPUnit test suite

## What This Is NOT

- Not a social app
- Not a multi-user authenticated system
- Not a complex frontend SPA
- Not a production-grade vector database setup

The goal is a **clean, professional, backend-focused assignment submission**.

---

# SECTION 2 — LOCKED TECHNICAL DECISIONS

These decisions are FINAL. Do not change them. Do not propose alternatives.

| Decision | Value |
|---|---|
| Framework | Laravel 12 |
| PHP Version | 8.3 |
| Database | MySQL 8.0 |
| AI Provider | Google Gemini (only option — user has Gemini API keys) |
| Embedding Model | `text-embedding-004` (768 float dimensions) |
| Summary Model | `gemini-1.5-flash` |
| Embedding Storage | `LONGTEXT` column (stored as stringified JSON) |
| Vector Search | Pure PHP cosine similarity (no pgvector, no external vector DB) |
| Cache | Redis 7 |
| Frontend | Blade + Alpine.js + Tailwind CSS v3 via CDN |
| Containerization | Docker Compose (4 services: app, nginx, mysql, redis) |
| API Documentation | `darkaonline/l5-swagger` package |
| Testing | PHPUnit (built into Laravel) |
| Queue Mode | `QUEUE_CONNECTION=sync` |
| HTTP Client | Laravel `Http` facade (NOT raw Guzzle) |

---

# SECTION 3 — WHAT DOCUMENTS EXIST IN THIS PROJECT

Read all of these before starting any work:

| File | Purpose |
|---|---|
| `AGENT_WORKING_PLAN.md` | Primary agent working plan: dev order, structure, progress tracker |
| `AI_AGENT_RULES.md` | This file — rules and context for all agents |
| `Project_Rules_Guide.md` | 28 project rules covering architecture, API, security, testing, etc. |
| `notes-app-implementation-plan.md` | Full 11-phase implementation plan with actual code snippets |
| `rough-plan.md` | Condensed phase summary |
| `Pre_Development_Setup_Guide.md` | Pre-dev decisions and workflow guide |
| `Final_Submission_Creation_Guide.md` | Final submission checklist and reviewer flow |
| `AI_Notes_Assignment_Checklist.md` | Full submission checklist (18 sections) |
| `PHP_AI_Notes_App_Assignment.pdf` | Original assignment brief from the evaluator |

**Mandatory reading order for a new agent:**
1. This file (`AI_AGENT_RULES.md`)
2. `AGENT_WORKING_PLAN.md`
3. `Project_Rules_Guide.md`

---

# SECTION 4 — AVAILABLE TOOLS (For Antigravity IDE Agent)

The primary AI agent on this project runs inside **Antigravity IDE** on Linux.

## File System Tools
- `view_file` — read any file in the workspace
- `write_to_file` — create new files
- `replace_file_content` — edit existing files (single contiguous block)
- `multi_replace_file_content` — edit multiple non-adjacent sections of a file
- `list_dir` — list directory contents
- `grep_search` — regex/literal search across files

## Terminal Tools
- `run_command` — run shell commands (bash, composer, php artisan, docker, etc.)
- `manage_task` — manage background tasks (list, kill, status, send input)

## Research Tools
- `search_web` — search the web
- `read_url_content` — fetch and read a URL as markdown
- `browser_subagent` — open browser, interact with web pages, record sessions

## Image Tools
- `generate_image` — generate images from text prompts

## Planning Tools
- `ask_question` — ask the user multiple-choice questions
- `ask_permission` — request permissions for file/command access

## Skills Available (Plugins)
- `modern-web-guidance` — search for modern HTML/CSS/JS best practices
- `chrome-devtools` — browser debugging via Chrome DevTools MCP
- `a11y-debugging` — accessibility debugging
- `android-cli` — Android development tasks
- `chrome-extensions` — Chrome Extension development

## What the Agent CANNOT Do
- Cannot run interactive long-lived commands without backgrounding them
- Cannot use npm/pip/curl without user approval per-command
- Cannot access files outside the workspace without permission

---

# SECTION 5 — PROJECT FILE STRUCTURE

This is the target structure to build:

```
notes-app/                              ← Laravel root (to be created)
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── NoteController.php          ← CRUD (thin, delegates to NoteService)
│   │   │   │   ├── SearchController.php         ← Semantic search
│   │   │   │   └── SummaryController.php        ← AI summary
│   │   │   └── Web/
│   │   │       └── NoteWebController.php        ← Blade pages
│   │   └── Requests/
│   │       ├── StoreNoteRequest.php
│   │       └── UpdateNoteRequest.php
│   ├── Jobs/
│   │   └── GenerateEmbeddingJob.php             ← dispatched on create/update
│   ├── Models/
│   │   └── Note.php
│   ├── Services/
│   │   ├── GeminiService.php                    ← ALL Gemini API calls
│   │   ├── EmbeddingService.php                 ← generate + store embeddings
│   │   ├── SemanticSearchService.php            ← cosine similarity search
│   │   ├── NoteService.php                      ← CRUD business logic + cache
│   │   └── SummaryService.php                   ← summary + cache
│   ├── Helpers/
│   │   └── VectorMath.php                       ← pure PHP cosine similarity
│   └── Exceptions/
│       └── GeminiException.php
├── resources/views/
│   ├── layouts/app.blade.php
│   └── notes/
│       ├── index.blade.php
│       ├── create.blade.php
│       ├── edit.blade.php
│       └── show.blade.php
├── routes/
│   ├── api.php
│   └── web.php
├── database/
│   ├── migrations/xxxx_create_notes_table.php
│   └── seeders/NoteSeeder.php
├── tests/
│   ├── Unit/
│   │   ├── VectorMathTest.php
│   │   └── GeminiServiceTest.php
│   └── Feature/
│       ├── NotesCrudTest.php
│       ├── SemanticSearchTest.php
│       └── SummaryTest.php
├── docker/
│   ├── Dockerfile
│   └── nginx.conf
├── docker-compose.yml
├── .env.example
└── README.md
```

---

# SECTION 6 — DATABASE SCHEMA

```sql
CREATE TABLE notes (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    content     LONGTEXT NOT NULL,
    embedding   LONGTEXT NULL,        -- stringified JSON float array (768 values)
    tags        JSON NULL,            -- array of strings e.g. ["work", "ideas"]
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP NULL        -- soft deletes
);
```

### Model Rules
- `$fillable` = `['title', 'content', 'tags']`
- `$hidden` = `['embedding', 'deleted_at']` — NEVER expose embedding in API
- `$casts` = `['tags' => 'array']`
- Embedding decoded manually via accessor (LONGTEXT → json_decode → array)
- Uses `SoftDeletes` trait

---

# SECTION 7 — API SPECIFICATION

## All Endpoints

```
POST    /api/notes                      Create note           → 201
GET     /api/notes?page=1&limit=10      List notes            → 200
GET     /api/notes/{id}                 Get single note       → 200 / 404
PUT     /api/notes/{id}                 Update note           → 200 / 404 / 422
DELETE  /api/notes/{id}                 Soft delete           → 204 / 404

POST    /api/notes/{id}/summary         AI summary            → 200 / 404
GET     /api/search?q={query}           Semantic search       → 200 / 422
```

## Unified Response Envelope

Every API response must match this structure exactly:

```json
// Success (single item)
{
  "success": true,
  "message": "Note created successfully",
  "data": { "id": 1, "title": "...", "content": "...", "tags": [], "created_at": "...", "updated_at": "..." }
}

// Success (paginated list)
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 45,
    "last_page": 5,
    "from": 1,
    "to": 10
  }
}

// Validation Error
{
  "success": false,
  "message": "Validation failed",
  "errors": { "title": ["The title field is required."] }
}

// Not Found
{
  "success": false,
  "message": "Note not found"
}
```

## HTTP Status Codes

| Code | When |
|---|---|
| 201 | Note created |
| 200 | Successful GET / PUT / POST (summary/search) |
| 204 | Note deleted |
| 422 | Validation failure |
| 404 | Note not found |
| 429 | Rate limit exceeded |
| 500 | Server error |

---

# SECTION 8 — GEMINI INTEGRATION

## Embedding Generation

```
URL:    POST https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent?key={KEY}
Input:  title + "\n\n" + content  (truncated to 8000 chars)
Output: 768 floats → JSON stringify → store in LONGTEXT column
```

## Summary Generation

```
URL:    POST https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={KEY}
Prompt: "Summarize the following note in 2-3 concise sentences, capturing only the key
         points. Do not add any preamble like 'This note says...'. Just provide the
         summary directly.\n\nNote:\n{content}"
Config: maxOutputTokens: 150, temperature: 0.3
```

## Error Handling Rules

1. HTTP 429 → sleep 2 seconds → retry once
2. Still fails → throw `GeminiException`
3. Embedding failure in `EmbeddingService` → catch `GeminiException` → `Log::warning()` → continue (note saved without embedding)
4. Summary failure in `SummaryService` → return JSON error response to client
5. NEVER expose raw Gemini error body to API consumers

---

# SECTION 9 — SEMANTIC SEARCH FLOW

```
1. Validate query (min:2, max:500)
2. Call GeminiService::generateEmbedding($query)
3. Load all notes WHERE embedding IS NOT NULL
4. foreach note: json_decode embedding → VectorMath::cosineSimilarity(query_vec, note_vec)
5. Filter: keep only scores >= 0.60
6. Sort: descending by similarity score
7. Limit: top 10 results
8. Return each note with added similarity_score field
```

### VectorMath::cosineSimilarity()
```
similarity = (A · B) / (||A|| × ||B||)

- A · B  = sum of element-wise products (dot product)
- ||A||  = sqrt(sum of squares of A)
- ||B||  = sqrt(sum of squares of B)
- Returns float between -1 and 1
- For text embeddings, >= 0.60 means meaningful similarity
```

---

# SECTION 10 — CACHING RULES

| Cache Key | TTL | Data | Invalidated When |
|---|---|---|---|
| `note_summary_{id}` | 86400s (24h) | AI summary text | Note updated or deleted |
| `notes_page_{page}_{limit}` | 300s (5min) | Paginated list + meta | Any note created / updated / deleted |

### Implementation
```php
// Store summary
Cache::remember("note_summary_{$id}", 86400, fn() => $gemini->generateSummary($note->content));

// Invalidate on update/delete
Cache::forget("note_summary_{$id}");
// Pattern delete list cache via Redis directly
$keys = Redis::connection()->keys('notes_page_*');
if (!empty($keys)) Redis::connection()->del($keys);
```

---

# SECTION 11 — SECURITY RULES

- Rate limit ALL API routes: `throttle:60,1`
- Rate limit AI endpoints additionally: `throttle:30,1`
- `$fillable` on Note model (mass assignment protection)
- All validation via Form Requests
- Gemini API key in `.env` only — never hardcoded
- No `.env` committed to git
- No raw stack traces in API responses

---

# SECTION 12 — TESTING RULES

- ALL Gemini API calls must be **mocked** in tests — no real HTTP calls
- Use `Http::fake()` for GeminiService
- Use `RefreshDatabase` trait for Feature tests
- Use SQLite in-memory for test database (set in `phpunit.xml`)
- Test classes that must exist:
  - `tests/Unit/VectorMathTest.php`
  - `tests/Unit/GeminiServiceTest.php`
  - `tests/Feature/NotesCrudTest.php`
  - `tests/Feature/SemanticSearchTest.php`
  - `tests/Feature/SummaryTest.php`

---

# SECTION 13 — DOCKER SETUP

4 services in `docker-compose.yml`:

| Service | Image | Role |
|---|---|---|
| `app` | Custom PHP 8.3-FPM Dockerfile | Runs Laravel |
| `nginx` | nginx:alpine | Reverse proxy to app |
| `db` | mysql:8.0 | Database |
| `redis` | redis:7-alpine | Cache |

Reviewer command to start everything:
```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
```

App available at: `http://localhost:8000`
Swagger at: `http://localhost:8000/api/documentation`

---

# SECTION 14 — WHAT IS ALREADY DONE

| Status | Item |
|---|---|
| ✅ Done | Project planning |
| ✅ Done | Architecture decisions |
| ✅ Done | Tech stack finalization |
| ✅ Done | AI provider decision (Gemini) |
| ✅ Done | All project rule documents |
| ✅ Done | Agent working plan |
| ❌ Not started | Laravel app code |
| ❌ Not started | Database migration |
| ❌ Not started | CRUD APIs |
| ❌ Not started | Gemini integration |
| ❌ Not started | Semantic search |
| ❌ Not started | Redis caching |
| ❌ Not started | Blade frontend |
| ❌ Not started | Tests |
| ❌ Not started | Docker setup |
| ❌ Not started | Swagger docs |
| ❌ Not started | README |

---

# SECTION 15 — STRICT RULES FOR ALL AGENTS

## Rules You MUST Follow

1. **Read `AGENT_WORKING_PLAN.md` progress tracker** before starting — check what's done
2. **Follow the development order** in `AGENT_WORKING_PLAN.md` — don't skip phases
3. **Controllers stay thin** — no logic, only delegate to services
4. **Never call Gemini directly** from a controller or anywhere except `GeminiService`
5. **Always use Form Requests** — never `$request->validate()` inside controller methods
6. **Always use the unified response envelope** — no custom response formats
7. **Never expose `embedding` field** in any API response — it must be in `$hidden`
8. **Embedding failures are non-fatal** — log warning, continue saving the note
9. **All AI calls must be mocked in tests** — `Http::fake()` always
10. **Use `GenerateEmbeddingJob::dispatch($note)`** even with sync queue — keep arch scalable
11. **Update `AGENT_WORKING_PLAN.md` progress tracker** after completing each phase
12. **Codebase and Architectural Alignment**: When making any modifications or adding features, always align with the existing codebase structure, services pattern ("Thin Controller, Fat Service"), caching drivers, exception structures, and the guidelines laid out in `Project_Rules_Guide.md` and this document.
13. **Git Commits and Submission Permission**: Do not commit any code or submit/push the project work without explicit user permission. Always ask the user before running any Git commits, pushes, or submission commands.

## Rules You MUST NOT Break

- ❌ No business logic in controllers
- ❌ No direct Gemini calls outside `GeminiService`
- ❌ No `$request->validate()` in controllers
- ❌ No exposing `embedding` in API responses
- ❌ No `LIKE %query%` as "semantic search"
- ❌ No real Gemini API calls in tests
- ❌ No committing `.env` or API keys
- ❌ No React/Vue/Next.js for frontend
- ❌ No exposing raw stack traces in API errors
- ❌ No changing the unified response envelope structure

---

# SECTION 16 — ENVIRONMENT VARIABLES REFERENCE

```env
APP_NAME="Notes App"
APP_ENV=local
APP_KEY=                          # generated by artisan
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db                        # use 'db' for Docker, '127.0.0.1' for local
DB_PORT=3306
DB_DATABASE=notes_db
DB_USERNAME=notes_user
DB_PASSWORD=secret

REDIS_HOST=redis                  # use 'redis' for Docker, '127.0.0.1' for local
REDIS_PASSWORD=null
REDIS_PORT=6379

GEMINI_API_KEY=                   # user's Gemini API key — NEVER commit this
GEMINI_EMBEDDING_MODEL=text-embedding-004
GEMINI_CHAT_MODEL=gemini-1.5-flash
GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta

CACHE_DRIVER=redis
QUEUE_CONNECTION=sync
SESSION_DRIVER=file

L5_SWAGGER_GENERATE_ALWAYS=true
```

---

# SECTION 17 — PRIORITY ORDER

If time or scope is limited, prioritize in this exact order:

1. CRUD APIs (core requirement)
2. AI Summary endpoint
3. Semantic search
4. Swagger documentation
5. Docker setup
6. README
7. Frontend UI
8. Tests
9. Advanced caching polish

---

# FINAL PRINCIPLE

> The goal is NOT the most complex project.
> The goal IS the most clean, complete, and professional implementation.
>
> Backend quality > Frontend polish
> Working features > Theoretical perfection
> Clean architecture > Clever tricks

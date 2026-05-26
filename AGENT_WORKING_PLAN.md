# AGENT WORKING PLAN
## AI-Powered Notes Management System
### How I (Antigravity) Will Build This Project

> This file is written by the AI agent for my own reference.
> It captures every decision, constraint, and step I will follow throughout development.
> I will update this file as the project progresses.

---

## Locked Decisions (Non-Negotiable)

| Decision | Value |
|---|---|
| Framework | Laravel 12 |
| Database | MySQL 8.0 |
| AI Provider | Google Gemini |
| Embedding Model | `text-embedding-004` (768 dimensions) |
| Summary Model | `gemini-1.5-flash` |
| Embedding Storage | `LONGTEXT` column (stringified JSON array) |
| Cache Layer | Redis 7 |
| Frontend | Blade + Alpine.js + Tailwind CSS v3 (CDN) |
| Containerization | Docker Compose (app + nginx + mysql + redis) |
| API Docs | `darkaonline/l5-swagger` |
| Testing | PHPUnit (Laravel built-in) |
| Queue Mode | `QUEUE_CONNECTION=sync` (Jobs dispatched, run synchronously) |
| Rate Limit (general) | `throttle:60,1` |
| Rate Limit (AI endpoints) | `throttle:30,1` |

---

## Architecture Rules I Will Follow

### Controller Rule
Controllers must be thin. They only:
1. Receive request
2. Delegate to service
3. Return response

I will NEVER put business logic, AI calls, or caching inside controllers.

### Service Layer Rule
All logic lives in `app/Services/`:
- `GeminiService` — all Gemini API calls
- `EmbeddingService` — generate + store embeddings
- `SemanticSearchService` — cosine similarity search
- `NoteService` — note business logic (create, update, delete with cache invalidation)
- `SummaryService` — summary generation + cache

### Validation Rule
Always use Form Requests. Never `$request->validate()` inside controller methods.

### Response Rule
Every API response follows this exact envelope — no exceptions:

```json
// Success
{ "success": true, "message": "...", "data": {} }

// Error
{ "success": false, "message": "...", "errors": {} }

// Paginated
{ "success": true, "data": [], "meta": { "current_page": 1, "per_page": 10, "total": 45, "last_page": 5 } }
```

### AI Failure Rule
Gemini failures must NEVER break the app.
- Embedding failure → log warning, note saved without embedding (falls back to keyword search)
- Summary failure → return clean JSON error, don't expose raw Gemini error

### Job Rule
Embedding generation uses `GenerateEmbeddingJob::dispatch($note)`.
Even with `QUEUE_CONNECTION=sync`, this keeps architecture scalable.

---

## Project File Structure I Will Create

```
notes-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── NoteController.php
│   │   │   │   ├── SearchController.php
│   │   │   │   └── SummaryController.php
│   │   │   └── Web/
│   │   │       └── NoteWebController.php
│   │   └── Requests/
│   │       ├── StoreNoteRequest.php
│   │       └── UpdateNoteRequest.php
│   ├── Jobs/
│   │   └── GenerateEmbeddingJob.php
│   ├── Models/
│   │   └── Note.php
│   ├── Services/
│   │   ├── GeminiService.php
│   │   ├── EmbeddingService.php
│   │   ├── SemanticSearchService.php
│   │   ├── NoteService.php
│   │   └── SummaryService.php
│   ├── Helpers/
│   │   └── VectorMath.php
│   └── Exceptions/
│       └── GeminiException.php
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php
│   └── notes/
│       ├── index.blade.php
│       ├── create.blade.php
│       ├── edit.blade.php
│       └── show.blade.php
├── routes/
│   ├── api.php
│   └── web.php
├── database/
│   ├── migrations/
│   │   └── xxxx_create_notes_table.php
│   └── seeders/
│       └── NoteSeeder.php
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

## API Endpoints I Will Build

```
# CRUD
POST    /api/notes                    Create note (201)
GET     /api/notes?page=1&limit=10   List notes paginated (200)
GET     /api/notes/{id}              Get single note (200 / 404)
PUT     /api/notes/{id}              Update note (200 / 404 / 422)
DELETE  /api/notes/{id}              Soft delete (204 / 404)

# AI Features
POST    /api/notes/{id}/summary      Generate AI summary (200 / 404)
GET     /api/search?q={query}        Semantic search (200 / 422)

# Web UI (Blade)
GET     /notes                       Notes listing page
GET     /notes/create                Create form
GET     /notes/{id}                  Show note + AI summary button
GET     /notes/{id}/edit             Edit form
```

---

## Database Schema

```
notes table:
- id              BIGINT UNSIGNED PK AUTO_INCREMENT
- title           VARCHAR(255) NOT NULL
- content         LONGTEXT NOT NULL
- embedding       LONGTEXT NULL          ← stringified JSON float array
- tags            JSON NULL              ← array of strings
- created_at      TIMESTAMP
- updated_at      TIMESTAMP
- deleted_at      TIMESTAMP NULL         ← soft deletes
```

**Hidden from API:** `embedding`, `deleted_at`
**Casts:** `tags → array`, `embedding → array` (via custom accessor)

---

## Caching Strategy

| What | Key Pattern | TTL | Invalidated On |
|---|---|---|---|
| AI Summary | `note_summary_{id}` | 24 hours | note update / delete |
| Notes list | `notes_page_{page}_{limit}` | 5 minutes | any note create / update / delete |

---

## Gemini API Integration Details

### Embedding Endpoint
```
POST https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent?key={API_KEY}
```
Input: `title + "\n\n" + content` (truncated to 8000 chars)
Output: 768 floats → JSON stringify → store in `embedding` LONGTEXT column

### Summary Endpoint
```
POST https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={API_KEY}
```
Prompt: `"Summarize the following note in 2-3 concise sentences, capturing only the key points. Do not add any preamble. Just provide the summary directly.\n\nNote:\n{content}"`
Config: `maxOutputTokens: 150`, `temperature: 0.3`

### Error Handling
- 429 → sleep(2) → retry once
- Still fails → throw `GeminiException`
- Caught in service → log warning, graceful fallback

---

## Semantic Search Logic

1. Generate query embedding via Gemini
2. Load all notes WHERE `embedding IS NOT NULL`
3. JSON decode each embedding
4. Compute cosine similarity (pure PHP via `VectorMath::cosineSimilarity()`)
5. Filter: `score >= 0.60`
6. Sort: descending by score
7. Take top 10
8. Return with `similarity_score` field in each result

**Known limitation (will document in README):** O(n) full scan. Fine at demo scale. Production would use pgvector.

---

## My Development Order

I will follow this exact order — no skipping, no jumping ahead:

| Step | Task | Notes |
|---|---|---|
| 1 | Laravel 12 install | `composer create-project` inside workspace |
| 2 | Package installation | l5-swagger, predis/predis, mockery (dev) |
| 3 | `.env` + `config/services.php` | Gemini config block |
| 4 | Docker skeleton | Dockerfile + nginx.conf + docker-compose.yml |
| 5 | Migration + Model | notes table, Note.php with casts, hidden, scopeSearch |
| 6 | Seeder | 10 diverse sample notes (no embeddings yet) |
| 7 | CRUD APIs | NoteController + Form Requests + routes/api.php |
| 8 | API Resources | NoteResource for consistent output |
| 9 | Swagger annotations | All CRUD endpoints documented |
| 10 | GeminiService | Embedding + Summary methods |
| 11 | GeminiException | Custom exception class |
| 12 | EmbeddingService | generateAndStore() |
| 13 | GenerateEmbeddingJob | Dispatched from NoteService |
| 14 | NoteService | Create/update/delete with embedding job + cache invalidation |
| 15 | Wire Job into CRUD | Update NoteController to use NoteService |
| 16 | VectorMath helper | cosineSimilarity() pure PHP |
| 17 | SemanticSearchService | Full search flow |
| 18 | SearchController | + Swagger annotations |
| 19 | SummaryService | Cache::remember wrapper |
| 20 | SummaryController | + Swagger annotations |
| 21 | Redis cache invalidation | In NoteService update/delete |
| 22 | Blade layout | app.blade.php (Tailwind CDN + Alpine CDN + nav) |
| 23 | Notes index view | List + semantic search bar + delete modal |
| 24 | Show view | Note content + AI summary button |
| 25 | Create view | Form with validation feedback |
| 26 | Edit view | Pre-filled form |
| 27 | NoteWebController | Blade controller |
| 28 | Web routes | routes/web.php |
| 29 | Unit tests | VectorMathTest, GeminiServiceTest |
| 30 | Feature tests | NotesCrudTest, SemanticSearchTest, SummaryTest |
| 31 | Docker finalize | Verify `docker compose up -d` works |
| 32 | README.md | Full setup + architecture + AI usage + prompts |
| 33 | .env.example | All variables documented |
| 34 | Final cleanup | Remove debug code, verify all checklist items |

---

## Testing Strategy

- AI services always **mocked** — no real Gemini calls in tests
- Use Laravel's `Http::fake()` for GeminiService tests
- Use `RefreshDatabase` trait for feature tests
- SQLite in-memory for test database

---

## Things I Will NOT Do

- ❌ Put business logic in controllers
- ❌ Call Gemini directly from a controller
- ❌ Use `$request->validate()` inside controller methods
- ❌ Expose `embedding` field in any API response
- ❌ Use React / Vue / Next.js for frontend
- ❌ Make real Gemini API calls in tests
- ❌ Commit `.env` or real API keys
- ❌ Over-engineer the frontend
- ❌ Use `LIKE %query%` and call it "semantic search"
- ❌ Skip graceful error handling for AI failures

---

## Progress Tracker

> I will update checkboxes here as I complete each step.

- [x] Phase 1 — Laravel install + packages (laravel/laravel 12, l5-swagger, predis)
- [x] Phase 2 — Docker skeleton (Dockerfile, nginx.conf, docker-compose.yml with 4 services)
- [x] Phase 3 — Migration + Model + Seeder (notes table, Note.php, NoteSeeder with 10 notes)
- [x] Phase 4 — CRUD APIs + Form Requests + Resources
- [x] Phase 5 — Swagger annotations
- [x] Phase 6 — Gemini integration (GeminiService + EmbeddingService + Job)
- [x] Phase 7 — Semantic Search (VectorMath + SemanticSearchService + SearchController)
- [x] Phase 8 — AI Summary (SummaryService + SummaryController)
- [x] Phase 9 — Redis caching + invalidation
- [x] Phase 10 — Blade Frontend (4 views)
- [x] Phase 11 — Unit + Feature Tests
- [x] Phase 12 — Docker finalize
- [x] Phase 13 — README + .env.example
- [x] Phase 14 — Final cleanup + checklist review

---

## Current Status

> **PROJECT COMPLETE ✅

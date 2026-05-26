# Project Rules & Development Guide
## AI-Powered Notes Management System
### Laravel 12 + Gemini/OpenAI + Docker

---

# 1. Core Project Goal

Build a clean, scalable, AI-powered Notes Management System with:
- CRUD APIs
- Semantic Search
- AI Summaries
- Simple Frontend UI
- Clean Laravel Architecture

The focus is:
- backend quality
- architecture
- API standards
- AI integration

NOT fancy frontend design.

---

# 2. Development Philosophy

## Main Rule
Keep the project:
- simple
- clean
- maintainable
- production-inspired

Avoid:
- unnecessary complexity
- overengineering
- premature optimization

---

# 3. Architecture Rules

## Controllers Must Stay Thin

Controllers should:
- receive request
- validate request
- call service
- return response

Controllers must NOT:
- contain business logic
- contain AI logic
- contain vector math
- contain caching logic

BAD:
```php
public function store(Request $request)
{
    // 200 lines of logic
}
```

GOOD:
```php
public function store(StoreNoteRequest $request)
{
    return $this->noteService->create($request->validated());
}
```

---

# 4. Service Layer Rules

All business logic belongs inside Services.

Example:
```txt
app/Services/
```

Services should handle:
- AI integration
- semantic search
- caching
- embeddings
- summaries

---

# 5. Validation Rules

NEVER validate directly in controllers.

Always use:
- Form Requests

Validation must include:
- required fields
- max lengths
- data types

---

# 6. API Response Rules

Every API response must follow consistent structure.

## Success Response

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {}
}
```

## Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {}
}
```

---

# 7. HTTP Status Code Rules

| Action | Code |
|---|---|
| Create | 201 |
| Success | 200 |
| Delete | 204 |
| Validation Error | 422 |
| Unauthorized | 401 |
| Not Found | 404 |
| Rate Limited | 429 |
| Server Error | 500 |

Never return incorrect status codes.

---

# 8. Database Rules

## Notes Table Must Include
- title
- content
- embedding
- timestamps
- soft deletes

## Important Rules
- Never expose embeddings in API response
- Use soft deletes
- Keep schema minimal

---

# 9. AI Integration Rules

## AI Usage Must Be Real

Do NOT fake semantic search.

Semantic search MUST:
- generate embeddings
- compare vectors
- rank by similarity

## Summary Endpoint

Must:
- generate actual AI summary
- use Gemini/OpenAI
- handle API failures gracefully

---

# 10. Semantic Search Rules

## Search Logic

Flow:
1. Generate query embedding
2. Compare with stored embeddings
3. Calculate cosine similarity
4. Sort by relevance
5. Return top results

## Important

DO NOT use only:
```sql
LIKE %search%
```

That is NOT semantic search.

---

# 11. Queue Rules

AI operations should use Jobs.

Example:
```php
GenerateEmbeddingJob::dispatch($note);
```

Even if using:
```env
QUEUE_CONNECTION=sync
```

This keeps architecture scalable.

---

# 12. Cache Rules

Use Redis caching for:
- summaries
- paginated notes

Cache must be invalidated on:
- update
- delete

---

# 13. Security Rules

Must include:
- validation
- mass assignment protection
- rate limiting
- hidden API keys

Never:
- commit `.env`
- expose secrets
- trust raw user input

---

# 14. Rate Limiting Rules

Apply throttle middleware on:
- summary endpoint
- search endpoint

Example:
```php
Route::middleware('throttle:30,1');
```

---

# 15. Frontend Rules

Frontend should remain:
- simple
- functional
- clean

DO NOT:
- overbuild UI
- spend too much time on animations
- build unnecessary components

Frontend goal:
- demonstrate API usage
- show search
- show summaries

---

# 16. Swagger Rules

All APIs must be documented.

Swagger should include:
- endpoint description
- request body
- response examples
- error responses

Swagger URL:
```txt
/api/documentation
```

---

# 17. Testing Rules

Minimum required tests:
- CRUD feature tests
- semantic search tests
- summary tests
- vector math unit tests

Tests should:
- mock AI services
- avoid real API calls

---

# 18. Docker Rules

Docker setup must work with:

```bash
docker compose up -d
```

Must include:
- app container
- nginx
- mysql
- redis

---

# 19. README Rules

README must include:
- setup instructions
- Docker setup
- API docs
- architecture explanation
- AI tools used
- prompts used
- screenshots

README should allow:
- fresh clone
- setup without confusion

---

# 20. Code Quality Rules

## Must Follow
- clean naming
- reusable methods
- small functions
- proper separation of concerns

## Avoid
- duplicated code
- giant controllers
- magic values
- unused files

---

# 21. Naming Conventions

## Classes
PascalCase

Example:
- SemanticSearchService
- GenerateEmbeddingJob

## Methods
camelCase

Example:
- generateSummary()
- findRelevantNotes()

## Database
snake_case

Example:
- created_at
- deleted_at

---

# 22. Git Rules

## Commit Rules

Use meaningful commits.

GOOD:
- Add semantic search service
- Fix summary caching bug

BAD:
- update
- changes
- fix

---

# 23. Environment Rules

Never commit:
- `.env`
- API keys
- secrets

Must include:
- `.env.example`

---

# 24. Error Handling Rules

All exceptions should return clean JSON.

Never expose:
- stack traces
- internal server details
- raw API errors

---

# 25. Performance Rules

For assignment scope:
- simple implementation preferred
- readability over optimization

Avoid:
- premature optimization
- unnecessary abstractions

---

# 26. Scope Control Rules

If running out of time:

PRIORITIZE:
1. CRUD APIs
2. AI summary
3. semantic search
4. Swagger
5. Docker
6. README

LOW PRIORITY:
- advanced frontend polish
- extra features
- advanced caching

---

# 27. Final Quality Standard

Before submission ask:
- Is the architecture clean?
- Are APIs professional?
- Does semantic search actually work?
- Is AI integration meaningful?
- Can reviewer run project easily?
- Does README explain everything clearly?

If YES → ready to submit.

---

# 28. Final Project Principle

The goal is NOT:
> "Most complex project"

The goal IS:
> "Most clean, complete, and professional implementation"

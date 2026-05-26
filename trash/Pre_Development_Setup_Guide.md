# Pre-Development Setup Guide
## AI-Powered Notes Management System

---

# Current Project Status

Planning phase is mostly complete.

The following are already prepared:
- project architecture
- implementation plan
- project rules
- submission checklist
- final submission guide
- feature scope
- backend structure

Now the focus should shift from:
> planning

to:
> execution

---

# Remaining Decisions Before Starting Development

---

# 1. Finalize AI Provider

Choose ONE provider before development starts.

## Option A — Gemini

### Pros
- free tier available
- good embeddings
- good summaries

### Cons
- slightly more complex SDK/docs
- response parsing less clean

---

## Option B — OpenAI

### Pros
- cleaner developer experience
- easier Laravel integration
- better documentation
- lower debugging risk

### Cons
- paid API usage

---

# Recommendation

For assignment speed and reliability:
- OpenAI is easier
- Gemini is cheaper

Priority should be:
> speed + stability over saving small API cost

---

# 2. Finalize Frontend Stack

Recommended stack:

```txt
Blade + Tailwind + Alpine.js
```

Why:
- fast development
- minimal setup
- perfect for assignment scope

Avoid:
- React SPA
- Next.js
- TypeScript complexity
- frontend overengineering

---

# 3. Finalize Embedding Storage Format

Recommended:
```txt
LONGTEXT
```

Store embeddings as stringified JSON.

Example:
```json
"[0.123,0.555,0.222]"
```

Why:
- simpler
- fewer MySQL JSON issues
- easier debugging

---

# 4. Finalize API Response Structure

All endpoints must follow same response format.

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

This should NEVER change during development.

---

# 5. Finalize Development Order

DO NOT randomly build features.

Recommended order:

1. Project setup
2. Database + Model
3. CRUD APIs
4. Validation + Resources
5. Swagger
6. AI summary
7. Embeddings
8. Semantic search
9. Redis caching
10. Frontend UI
11. Docker cleanup
12. Tests
13. README

---

# 6. Create PROJECT_RULES.md

Before coding:
- create rules file
- paste architecture rules
- paste API rules
- paste controller/service rules

When using AI IDE:
tell AI to:
> Follow PROJECT_RULES.md strictly

This improves generated code quality massively.

---

# 7. Create Initial Folder Structure

Create these folders before development:

```txt
app/
 ├── Http/
 ├── Services/
 ├── Jobs/
 ├── Resources/
 ├── Exceptions/
 ├── Helpers/
```

---

# 8. Setup API Testing Tool

Recommended:
- Postman
- Bruno

Create API collection from Day 1.

Test endpoints continuously during development.

---

# 9. Important Development Rules

## Rule 1
Never put business logic inside controllers.

---

## Rule 2
Never call Gemini/OpenAI directly from controller.

Always use Services.

---

## Rule 3
Every endpoint must return consistent JSON.

---

## Rule 4
Every AI feature must fail gracefully.

---

## Rule 5
Every feature must be testable.

---

# 10. AI IDE Usage Rules

Using Antigravity IDE effectively is important.

DO NOT ask AI:
> build whole project

This creates messy architecture.

Instead:
- generate migrations separately
- generate models separately
- generate controllers separately
- review manually
- refactor manually

Best workflow:
> small isolated generation + manual review

---

# 11. Backend First Strategy

DO NOT start frontend early.

Frontend should begin ONLY AFTER:
- CRUD APIs work
- Swagger works
- AI summaries work
- semantic search works

Priority:
> backend quality first

---

# 12. Add Logging Early

Recommended example:

```php
Log::info('Generating embedding', [
    'note_id' => $note->id
]);
```

Why:
- easier debugging
- AI APIs can fail unpredictably

---

# 13. Prepare Before Writing First Feature

Before coding:
- prepare `.env.example`
- prepare Docker skeleton
- install Swagger
- setup Redis
- standardize response format
- create Services structure

Then start feature development.

---

# 14. Biggest Risk To Avoid

Do NOT:
- overengineer frontend
- endlessly plan
- generate huge AI code blocks blindly
- skip testing until end

---

# 15. Final Recommendation

The project is ready to start.

Only remaining tasks:
- finalize AI provider
- finalize embedding format
- finalize response format
- setup initial project structure

After that:
> start implementation immediately

Avoid excessive planning from this point onward.

---

# Final Principle

The goal is:
> clean architecture
> stable APIs
> meaningful AI integration
> professional submission

NOT:
> unnecessary complexity

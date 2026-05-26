<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Services\NoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NoteController extends Controller
{
    public function __construct(private NoteService $noteService)
    {
    }

    #[OA\Get(
        path: '/api/notes',
        summary: 'List all notes with pagination',
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'page',  in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of notes'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $page  = max(1, (int) $request->get('page', 1));
        $limit = min(max(1, (int) $request->get('limit', 10)), 100);

        $notes = $this->noteService->list($page, $limit);

        return response()->json([
            'success' => true,
            'data'    => NoteResource::collection($notes->items()),
            'meta'    => [
                'current_page' => $notes->currentPage(),
                'per_page'     => $notes->perPage(),
                'total'        => $notes->total(),
                'last_page'    => $notes->lastPage(),
                'from'         => $notes->firstItem(),
                'to'           => $notes->lastItem(),
            ],
        ]);
    }

    #[OA\Post(
        path: '/api/notes',
        summary: 'Create a new note',
        tags: ['Notes'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'content'],
                properties: [
                    new OA\Property(property: 'title',   type: 'string', maxLength: 255, example: 'My Note'),
                    new OA\Property(property: 'content', type: 'string', minLength: 10,  example: 'Detailed content...'),
                    new OA\Property(property: 'tags',    type: 'array',  items: new OA\Items(type: 'string')),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Note created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreNoteRequest $request): JsonResponse
    {
        $note = $this->noteService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Note created successfully.',
            'data'    => new NoteResource($note),
        ], 201);
    }

    #[OA\Get(
        path: '/api/notes/{id}',
        summary: 'Get a single note by ID',
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Note details'),
            new OA\Response(response: 404, description: 'Note not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $note = $this->noteService->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => new NoteResource($note),
        ]);
    }

    #[OA\Put(
        path: '/api/notes/{id}',
        summary: 'Update an existing note',
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title',   type: 'string'),
                    new OA\Property(property: 'content', type: 'string'),
                    new OA\Property(property: 'tags',    type: 'array', items: new OA\Items(type: 'string')),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Note updated successfully'),
            new OA\Response(response: 404, description: 'Note not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateNoteRequest $request, int $id): JsonResponse
    {
        $note    = $this->noteService->findOrFail($id);
        $updated = $this->noteService->update($note, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully.',
            'data'    => new NoteResource($updated),
        ]);
    }

    #[OA\Delete(
        path: '/api/notes/{id}',
        summary: 'Soft delete a note',
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Note deleted'),
            new OA\Response(response: 404, description: 'Note not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $note = $this->noteService->findOrFail($id);
        $this->noteService->delete($note);

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NoteService;
use App\Services\SummaryService;
use App\Exceptions\GeminiException;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class SummaryController extends Controller
{
    public function __construct(
        private NoteService    $noteService,
        private SummaryService $summaryService
    ) {
    }

    #[OA\Post(
        path: '/api/notes/{id}/summary',
        summary: 'Generate or retrieve cached AI summary for a note',
        tags: ['Summary'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Note ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'AI summary (generated or from cache)'),
            new OA\Response(response: 404, description: 'Note not found'),
            new OA\Response(response: 503, description: 'AI service temporarily unavailable'),
        ]
    )]
    public function generate(int $id): JsonResponse
    {
        $note = $this->noteService->findOrFail($id);

        try {
            $result = $this->summaryService->getSummary($note);

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        } catch (GeminiException $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI summary is temporarily unavailable. Please try again shortly.',
            ], 503);
        }
    }
}

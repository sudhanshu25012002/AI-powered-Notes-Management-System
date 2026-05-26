<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SemanticSearchService;
use App\Exceptions\GeminiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SearchController extends Controller
{
    public function __construct(private SemanticSearchService $searchService)
    {
    }

    #[OA\Get(
        path: '/api/search',
        summary: 'Semantic search across all notes using Gemini embeddings',
        tags: ['Search'],
        parameters: [
            new OA\Parameter(
                name: 'q',
                in: 'query',
                required: true,
                description: 'Search query (min 2, max 500 characters)',
                schema: new OA\Schema(type: 'string', example: 'machine learning algorithms')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Semantic search results ranked by similarity score'),
            new OA\Response(response: 422, description: 'Validation error — query required'),
            new OA\Response(response: 503, description: 'AI service temporarily unavailable'),
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:500',
        ]);

        try {
            $results = $this->searchService->search($request->get('q'));

            return response()->json([
                'success' => true,
                'message' => count($results) . ' result(s) found.',
                'data'    => $results,
                'meta'    => [
                    'query'         => $request->get('q'),
                    'total_results' => count($results),
                ],
            ]);
        } catch (GeminiException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search is temporarily unavailable. Please try again shortly.',
            ], 503);
        }
    }
}

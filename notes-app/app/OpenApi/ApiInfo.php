<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'AI-Powered Notes Management System API',
    version: '1.0.0',
    description: 'Laravel 12 REST API for managing notes with AI-powered semantic search and summaries using Google Gemini.',
    contact: new OA\Contact(email: 'admin@notes-app.com')
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Local Development Server'
)]
#[OA\Tag(name: 'Notes',   description: 'CRUD operations for notes')]
#[OA\Tag(name: 'Search',  description: 'Semantic search using Gemini embeddings')]
#[OA\Tag(name: 'Summary', description: 'AI summary generation using Gemini Flash')]
#[OA\Schema(
    schema: 'Note',
    properties: [
        new OA\Property(property: 'id',         type: 'integer', example: 1),
        new OA\Property(property: 'title',      type: 'string',  example: 'My Meeting Notes'),
        new OA\Property(property: 'content',    type: 'string',  example: 'Discussed Q3 roadmap...'),
        new OA\Property(property: 'tags',       type: 'array',   items: new OA\Items(type: 'string')),
        new OA\Property(property: 'created_at', type: 'string',  format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string',  format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string',  example: 'Validation failed.'),
        new OA\Property(property: 'errors',  type: 'object'),
    ]
)]
class ApiInfo
{
    // This class exists solely to hold top-level OpenAPI attributes.
    // swagger-php v6 requires PHP 8 native attributes (not docblock annotations).
}

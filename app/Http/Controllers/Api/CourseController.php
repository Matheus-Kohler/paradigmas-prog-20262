<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Http\Resources\CourseResource;
use App\Http\Services\CourseService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CourseController extends Controller
{
    public function __construct(private CourseService $courseService)
    {}

    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/courses',
        summary: 'Lista os cursos',
        security: [['sanctum' => []]],
        tags: ['Cursos'],
        parameters: [new OA\Parameter(name: 'name', in: 'query', required: false, description: 'Filtra pelo nome (busca parcial)', schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de cursos',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Course'))])
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function index(Request $request)
    {
        return CourseResource::collection($this->courseService->getWithFilters($request->all()));
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: '/courses',
        summary: 'Cadastra um curso',
        security: [['sanctum' => []]],
        tags: ['Cursos'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 500, example: 'Engenharia de Software'),
                    new OA\Property(property: 'description', type: 'string', maxLength: 2000, example: 'Curso de graduação', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Curso criado', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Course')])),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function store(CourseRequest $request)
    {
        return new CourseResource($this->courseService->store($request->validated()));
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/courses/{id}',
        summary: 'Exibe um curso',
        security: [['sanctum' => []]],
        tags: ['Cursos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Curso encontrado', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Course')])),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function show(string $id)
    {
        return new CourseResource($this->courseService->show($id));
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/courses/{id}',
        summary: 'Atualiza um curso',
        security: [['sanctum' => []]],
        tags: ['Cursos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 500, example: 'Engenharia de Software'),
                    new OA\Property(property: 'description', type: 'string', maxLength: 2000, example: 'Descrição atualizada', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Curso atualizado', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Course')])),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function update(CourseRequest $request, string $id)
    {
        return new CourseResource($this->courseService->update($request->validated(), $id));
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/courses/{id}',
        summary: 'Remove um curso',
        security: [['sanctum' => []]],
        tags: ['Cursos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Curso removido'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function destroy(string $id)
    {
        $this->courseService->destroy($id);

        return response()->noContent();
    }
}

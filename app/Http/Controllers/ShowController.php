<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Models\Show;
use App\Permissions\ShowPermissions;
use App\Services\ShowService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ShowController extends Controller implements HasMiddleware
{
    protected ShowService $showService;

    /**
     * Constructor method for the ShowController class.
     */
    public function __construct(ShowService $showService)
    {
        $this->showService = $showService;
    }

    /**
     * Get the middleware that should be assigned to the controller.
     * Applies middleware permissions for specific controller actions.
     *
     * @codeCoverageIgnore
     */
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(ShowPermissions::CAN_VIEW_SHOWS), only: ['index', 'show']),
            new Middleware(PermissionMiddleware::using(ShowPermissions::CAN_CREATE_SHOWS), only: ['store']),
            new Middleware(PermissionMiddleware::using(ShowPermissions::CAN_UPDATE_SHOWS), only: ['update']),
            new Middleware(PermissionMiddleware::using(ShowPermissions::CAN_DELETE_SHOWS), only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Pagination\LengthAwarePaginator
    {
        $request->validate([
            'sort' => 'string|in:id,id:asc,id:desc,title,title:asc,title:desc,created_at,created_at:asc,created_at:desc,start_date,start_date:asc,start_date:desc,end_date,end_date:asc,end_date:desc',
            'per_page' => 'integer|between:1,50',
            'enabled' => 'boolean',
            'is_live' => 'boolean',
        ]);

        $filters = [
            'sort' => $request->sort,
            'enabled' => $request->enabled,
            'is_live' => $request->is_live,
        ];

        return $this->showService->getShows($filters, $request->per_page ?? 25);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return ApiSuccessResponse|ApiErrorResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_live' => 'boolean',
            'enabled' => 'boolean',
            'moderators' => 'nullable|array',
            'locked_by' => 'nullable|integer|exists:users,id',
        ]);

        // Additional validation: end_date must be after or equal to start_date if both are provided
        if (isset($validated['start_date']) && isset($validated['end_date'])) {
            if (strtotime($validated['end_date']) < strtotime($validated['start_date'])) {
                return new ApiErrorResponse([
                    'end_date' => ['The end date must be after or equal to the start date.'],
                ], status: Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $show = $this->showService->createShow($validated);

        if ($show === null) {
            // @codeCoverageIgnoreStart
            return new ApiErrorResponse('Failed to create show');
            // @codeCoverageIgnoreEnd
        }

        return new ApiSuccessResponse($show, status: Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @return ApiSuccessResponse|ApiErrorResponse
     */
    public function show(Show $show)
    {
        return new ApiSuccessResponse($show);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return ApiSuccessResponse|ApiErrorResponse
     */
    public function update(Request $request, Show $show)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_live' => 'boolean',
            'enabled' => 'boolean',
            'moderators' => 'nullable|array',
            'locked_by' => 'nullable|integer|exists:users,id',
        ]);

        // Additional validation: end_date must be after start_date if both are provided
        $startDate = $request->filled('start_date') ? $request->start_date : $show->start_date;
        $endDate = $request->filled('end_date') ? $request->end_date : $show->end_date;

        if ($startDate && $endDate && strtotime($endDate) < strtotime($startDate)) {
            return new ApiErrorResponse('The end date must be after or equal to the start date.', status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updatedShow = $this->showService->updateShow($show->id, $validated);

        if ($updatedShow === null) {
            // @codeCoverageIgnoreStart
            return new ApiErrorResponse('Failed to update show');
            // @codeCoverageIgnoreEnd
        }

        return new ApiSuccessResponse($updatedShow);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return ApiSuccessResponse|ApiErrorResponse
     */
    public function destroy(Show $show)
    {
        if ($this->showService->deleteShow($show->id)) {
            return new ApiSuccessResponse('', status: Response::HTTP_NO_CONTENT);
        } else {
            // @codeCoverageIgnoreStart
            return new ApiErrorResponse('Failed to delete show');
            // @codeCoverageIgnoreEnd
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Models\Request as WishRequest;
use App\Permissions\RequestPermissions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class RequestController extends Controller implements HasMiddleware
{


    /**
     * Constructor method for the RequestController class.
     * Applies middleware permissions for specific controller actions.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(RequestPermissions::CAN_VIEW_REQUESTS), only: ['index', 'show']),
            new Middleware(PermissionMiddleware::using(RequestPermissions::CAN_DELETE_REQUESTS), only: ['destroy']),
        ];
    }

    /**
     * Retrieve a paginated list of requests.
     *
     * @param  Request  $httpRequest
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function index(Request $httpRequest): \Illuminate\Pagination\LengthAwarePaginator
    {
        $httpRequest->validate([
            'sort' => 'string|in:id,id:asc,id:desc,name,name:asc,name:desc,created_at,created_at:asc,created_at:desc,',
            'per_page' => 'integer|between:1,50',
        ]);

        if ($httpRequest->sort !== null) {
            $sort = explode(':', $httpRequest->sort);
            $requests = WishRequest::orderBy($sort[0], $sort[1] ?? 'asc')->paginate($httpRequest->per_page ?? 25);
        } else {
            $requests = WishRequest::orderBy('created_at')->paginate($httpRequest->per_page ?? 25);
        }

        return $requests;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $httpRequest)
    {
        $httpRequest->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $request = WishRequest::create([
            'name' => $httpRequest->name,
            'message' => $httpRequest->message,
        ]);

        if ($request === null) {
            // @codeCoverageIgnoreStart
            return new ApiErrorResponse('Failed to create request');
            // @codeCoverageIgnoreEnd
        } else {
            return new ApiSuccessResponse($request, status: Response::HTTP_CREATED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WishRequest $request)
    {
        return new ApiSuccessResponse($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WishRequest $request)
    {
        if ($request->delete()) {
            return new ApiSuccessResponse('', status: Response::HTTP_NO_CONTENT);
        } else {
            // @codeCoverageIgnoreStart
            return new ApiErrorResponse('Failed to delete');
            // @codeCoverageIgnoreEnd
        }
    }
}

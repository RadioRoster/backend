<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Models\Request;
use App\Permissions\RequestPermissions;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Response;

class RequestController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:' . RequestPermissions::CAN_VIEW_REQUESTS)->only(['index', 'show']);
        $this->middleware('permission:' . RequestPermissions::CAN_DELETE_REQUESTS)->only(['destroy']);
    }

    /**
     * Retrieve a paginated list of requests.
     *
     * @param  HttpRequest  $httpRequest
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function index(HttpRequest $httpRequest): \Illuminate\Pagination\LengthAwarePaginator
    {
        $httpRequest->validate([
            'sort' => 'string|in:id,id:asc,id:desc,name,name:asc,name:desc,created_at,created_at:asc,created_at:desc,',
            'per_page' => 'integer|between:1,50',
        ]);

        if ($httpRequest->sort !== null) {
            $sort = explode(':', $httpRequest->sort);
            $requests = Request::orderBy($sort[0], $sort[1] ?? 'asc')->paginate($httpRequest->per_page ?? 25);
        } else {
            $requests = Request::orderBy('created_at')->paginate($httpRequest->per_page ?? 25);
        }

        return $requests;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HttpRequest $httpRequest)
    {
        $httpRequest->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $request = Request::create([
            'name' => $httpRequest->name,
            'message' => $httpRequest->message,
        ]);

        if ($request === null) {
            return new ApiErrorResponse('Failed to create request');
        } else {
            return new ApiSuccessResponse($request, status: Response::HTTP_CREATED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        return new ApiSuccessResponse($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        if($request->delete()) {
            return new ApiSuccessResponse('', status: Response::HTTP_NO_CONTENT);
        } else {
            return new ApiErrorResponse('Failed to delete');
        }
    }
}

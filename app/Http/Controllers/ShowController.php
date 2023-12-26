<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShowResource;
use App\Http\Responses\ApiErrorResponse;
use App\Models\Show;
use App\Permissions\ShowsPermissions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShowController extends Controller
{

    /**
     * Retrieve a paginated list of shows based on the provided query parameters.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \App\Http\Responses\ApiErrorResponse
     */
    public function index()
    {
        static $SORT_OPTIONS = [
            'id',
            'id:asc',
            'id:desc',
            'start_date',
            'start_date:asc',
            'start_date:desc',
            'end_date',
            'end_date:asc',
            'end_date:desc',
        ];

        $casts = [
            'start_date' => 'date',
            'end_date' => 'date',
            'days' => 'integer',
            'live' => 'boolean',
            'moderator' => 'array',
            'moderator.*' => 'integer',
            'primary' => 'boolean',
            'sort' => 'string',
            'per_page' => 'integer',
        ];

        $rules = [
            'start_date' => ['required', 'date', 'before:end_date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'live' => 'boolean',
            'moderator' => ['array'],
            'moderator.*' => ['integer', 'distinct', 'exists:users,id'],
            'primary' => ['boolean', 'exclude_without:moderator'],
            'sort' => ['string', 'in:' . implode(',', $SORT_OPTIONS)],
            'per_page' => 'integer',

        ];

        $validated = $this->validateParams(request()->all(), $casts, $rules);

        $shows = Show::query()->with([
            "moderators" => function ($query) {
                $query->withPivot('primary');
            }
        ]);

        if (!auth()->check()) {
            if ($validated['start_date'] < today() ) {
                return new ApiErrorResponse('Start date must be greater than today.', status: Response::HTTP_BAD_REQUEST);
            }
            if ($validated['end_date'] > today()->addMonth() ) {
                return new ApiErrorResponse('End date must be less than 30 days from today.', status: Response::HTTP_BAD_REQUEST);
            }

            $shows->where('enabled', '=', true);
        }


        $shows->where(function ($query) use ($validated) {
            $query->whereBetween('start_date',
                [
                    $validated['start_date'],
                    $validated['end_date'],
                ]);
            $query->orWhereBetween('end_date',
                [
                    $validated['start_date'],
                    $validated['end_date'],
                ]);
        });

        /**
         * Hide shows that are not enabled and the primary moderator is not the current user.
         */
        if (auth()->check()) {
            /** @var \App\Models\User $user */
            $user = auth()->user();
            if (!$user->hasPermissionTo(ShowsPermissions::CAN_VIEW_DISABLED_SHOWS_OTHERS)) {
                $shows->where(function ($query) use ($user) {
                    $query->where('enabled', '=', true)
                        ->orWhere(function ($query) use ($user) {
                            $query->where('enabled', '=', false)
                                ->whereHas('moderators', function ($query) use ($user) {
                                    $query->where('moderator_id', '=', $user->id)
                                        ->where('primary', '=', true);
                            });
                    });
                });
            }
        }

        /**
         * Check if the query parameter "live" is set.
         * If it is, it should return only shows that are live or not live,
         * depending on the value of the query parameter "live".
         */
        if (isset($validated['live'])) {
            $shows->where('is_live', '=', $validated['live']);
        }

        /**
         * If the query parameter "moderator" is set,
         * it should return only shows that have the given user as a moderator.
         */
        if (isset($validated['moderator'])) {
            $shows->whereHas('moderators', function ($query) use ($validated) {
                $query->whereIn('moderator_id', $validated['moderator']);
                // If the query parameter "primary" is set,
                // it should return only shows that have the given user as a primary moderator or not,
                // depending on the value of the query parameter "primary".
                if (isset($validated['primary'])) {
                    $query->where('primary', '=', $validated['primary']);
                }
            });
        }

        /**
         * If the query parameter "sort" is set,
         * it should return the shows sorted by the given field.
         * If the query parameter "sort" isn't set, it should return the shows sorted by start date.
         */
        if (isset($validated['sort'])) {
            $sort = explode(':', $validated['sort']);
            if (in_array($validated['sort'], $SORT_OPTIONS)) {
                if (count($sort) === 1) {
                    $shows->orderBy($sort[0]);
                } else {
                    $shows->orderBy($sort[0], $sort[1]);
                }
            }
        } else {
            $shows->orderBy('start_date');
        }

        /**
         * If the query parameter "per_page" is set,
         * it should return the given amount of shows per page.
         * If "per_page" isn't set, it should return 25 shows per page, maximum 50.
         */
        if (isset($validated['per_page']) && $validated['per_page'] <= 50) {
            $res = $shows->paginate($validated['per_page']);
        } else {
            $res = $shows->paginate(25);
        }

        return ShowResource::collection($res);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Show $show)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Show $show)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Show $show)
    {
        //
    }
}

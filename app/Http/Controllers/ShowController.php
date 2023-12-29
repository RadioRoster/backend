<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShowResource;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Models\Show;
use App\Models\User;
use App\Permissions\ShowsPermissions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShowController extends Controller
{

    /**
     * Check if there are any other shows that overlap with the given start and end dates.
     * If the start date is the same as the end date of another show, it should return false and vice versa.
     *
     * @param string $start_date The start date of the show.
     * @param string $end_date The end date of the show.
     * @param int|null $show_id The id of the show to exclude from the check.
     * @return bool Returns true if there are no overlapping shows, false otherwise.
     */
    private function _checkForOtherShow(string $start_date, string $end_date, int|null $show_id = null, bool|null $enabled = true): bool
    {
        $overlapCount = Show::query();

        if ($enabled !== null) {
            $overlapCount->where('enabled', '=', $enabled);
        }

        $overlapCount->where(function ($query) use ($start_date, $end_date) {
            $query->where(function ($query) use ($start_date) {
                $query->where(function ($query) use ($start_date) {
                    $query->whereRaw('? BETWEEN `start_date` AND `end_date`', [$start_date]);
                });
                $query->where(function ($query) use ($start_date) {
                    $query->where('end_date', '!=', [$start_date]);
                });
            });

            $query->orWhere(function ($query) use ($end_date) {
                $query->where(function ($query) use ($end_date) {
                    $query->whereRaw('? BETWEEN `start_date` AND `end_date`', [$end_date]);
                });
                $query->where(function ($query) use ($end_date) {
                    $query->where('start_date', '!=', [$end_date]);
                });
            });
            $query->orWhere(function ($query) use ($start_date, $end_date) {
                $query->where('start_date', '<=', $start_date);
                $query->where('end_date', '>=', $end_date);
            });
        });

        if ($show_id !== null) {
            $overlapCount->where('id', '!=', $show_id);
        }

        return $overlapCount->count() === 0;
    }

    /**
     * ShowController constructor.
     *
     * This method initializes the ShowController class.
     * It sets up the necessary middleware for specific actions.
     */
    public function __construct()
    {
        $this->middleware('permission:' . ShowsPermissions::CAN_CREATE_SHOWS . '|' . ShowsPermissions::CAN_CREATE_SHOWS_OTHERS)
            ->only(['store']);
        $this->middleware('permission:' . ShowsPermissions::CAN_UPDATE_SHOWS . '|' . ShowsPermissions::CAN_UPDATE_SHOWS_OTHERS)
            ->only(['update']);
        $this->middleware('permission:' . ShowsPermissions::CAN_DELETE_SHOWS . '|' . ShowsPermissions::CAN_DELETE_SHOWS_OTHERS)
            ->only(['delete']);
    }

    /**
     * Retrieve a paginated list of shows based on the provided query parameters.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \App\Http\Responses\ApiErrorResponse
     */
    public function index(Request $request)
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

        $request->validate([
            'start_date' => ['required', 'date', 'before:end_date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'live' => 'boolean',
            'moderator' => ['array'],
            'moderator.*' => ['integer', 'distinct', 'exists:users,id'],
            'primary' => ['boolean', 'exclude_without:moderator'],
            'sort' => ['string', 'in:' . implode(',', $SORT_OPTIONS)],
            'per_page' => 'integer',

        ]);

        $request['start_date'] = $request->date('start_date')->toDateTimeString();
        $request['end_date'] = $request->date('end_date')->toDateTimeString();

        /** @var \App\Models\User $user */
        $user = $request->user('sanctum');

        $shows = Show::query()->with([
            "moderators" => function ($query) {
                $query->withPivot('primary');
            }
        ]);

        if ($user === null) {
            if ($request->start_date < today() ) {
                return new ApiErrorResponse('Start date must be greater than today.', status: Response::HTTP_BAD_REQUEST);
            }
            if ($request->end_date > today()->addMonth() ) {
                return new ApiErrorResponse('End date must be less than 30 days from today.', status: Response::HTTP_BAD_REQUEST);
            }

            $shows->where('enabled', '=', true);
        }


        $shows->where(function ($query) use ($request) {
            $query->whereBetween('start_date',
                [
                    $request->start_date,
                    $request->end_date,
                ]);
            $query->orWhereBetween('end_date',
                [
                    $request->start_date,
                    $request->end_date,
                ]);
        });

        /**
         * Hide shows that are not enabled and the primary moderator is not the current user.
         */
        if ($user !== null && !$user->hasPermissionTo(ShowsPermissions::CAN_VIEW_DISABLED_SHOWS_OTHERS)) {
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

        /**
         * Check if the query parameter "live" is set.
         * If it is, it should return only shows that are live or not live,
         * depending on the value of the query parameter "live".
         */
        if ($request->live !== null) {
            $shows->where('is_live', '=', $request->live);
        }

        /**
         * If the query parameter "moderator" is set,
         * it should return only shows that have the given user as a moderator.
         */
        if ($request->moderator !== null && count($request->moderator) > 0) {
            $shows->whereHas('moderators', function ($query) use ($request) {
                $query->whereIn('moderator_id', $request->moderator);
                // If the query parameter "primary" is set,
                // it should return only shows that have the given user as a primary moderator or not,
                // depending on the value of the query parameter "primary".
                if (isset($validated['primary'])) {
                    $query->where('primary', '=', $request->primary);
                }
            });
        }

        /**
         * If the query parameter "sort" is set,
         * it should return the shows sorted by the given field.
         * If the query parameter "sort" isn't set, it should return the shows sorted by start date.
         */
        if ($request->sort !== null) {
            $sort = explode(':', $request->sort);
            if (in_array($request->sort, $SORT_OPTIONS)) {
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
        if ($request->sort !== null && $request->per_page <= 50) {
            $res = $shows->paginate($request->per_page);
        } else {
            $res = $shows->paginate(25);
        }

        if ($user === null) {
            $res = collect($res->items())->map(function ($show) {
                return $show->makeHidden('locked_by');
            });
        }

        return ShowResource::collection($res);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ShowResource | \App\Http\Responses\ApiErrorResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'presemt|string|nullable',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
            'live' => 'required|boolean',
            'enabled' => 'required|boolean',
            'moderators' => 'required|array',
            'moderators.*' => 'required|array',
            'moderators.*.id' => 'required|integer|distinct|exists:users,id',
            'moderators.*.primary' => 'required|boolean',
        ]);

        // Get the primary moderator.
        $primaryModerator = collect($request->moderators)->filter(function ($moderator) {
            return $moderator['primary'] === true;
        });

        /**
         * A show must have exactly one primary moderator.
         */
        if ($primaryModerator->count() !== 1) {
            return new ApiErrorResponse('There must be exactly one primary moderator.', status: Response::HTTP_BAD_REQUEST);
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        /**
         * Check if the user is the primary moderator of the show.
         */
        if ($primaryModerator->first()['id'] !== $user->id) {
            if (!$user->checkPermissionTo(ShowsPermissions::CAN_CREATE_SHOWS_OTHERS, 'web')) {
                return new ApiErrorResponse('You are not allowed to create shows for others.', status: Response::HTTP_FORBIDDEN);
            }
        }

        /**
         * Convert the start and end date to datetime strings.
         */
        $request['start_date'] = $request->date('start_date')->toDateTimeString();
        $request['end_date'] = $request->date('end_date')->toDateTimeString();

        /**
         * Check if the show collides with another show.
         */
        if (!$this->_checkForOtherShow($request->start_date, $request->end_date)) {
            return new ApiErrorResponse('There is already a show scheduled for this time.', status: Response::HTTP_BAD_REQUEST);
        }

        // Get the primary moderator.
        $primaryModerator = User::find($primaryModerator->first()['id']);

        /**
         * Check if the primary moderator has the permission to be a primary moderator.
         */
        if (!$primaryModerator->checkPermissionTo(ShowsPermissions::CAN_BE_PRIMARY_MODERATOR)) {
            return new ApiErrorResponse('The primary moderator, "'. $primaryModerator->name .'" does not have the permission to be a primary moderator.', status: Response::HTTP_BAD_REQUEST);
        }

        // Get the non-primary moderators.
        $nonPrimaryModerators = collect($request->moderators)->filter(function ($moderator) {
            return $moderator['primary'] === false;
        });

        /**
         * If there are non-primary moderators, check if the user has the permission to add non-primary moderators.
         */
        if ($nonPrimaryModerators->count() > 0) {
            if (!$user->checkPermissionTo(ShowsPermissions::CAN_ADD_MODERATORS, 'web')) {
                return new ApiErrorResponse('You are not allowed to add non-primary moderators.', status: Response::HTTP_FORBIDDEN);
            }
            /**
             * Check if the non-primary moderators have the permission to be a moderator.
             */
            foreach ($nonPrimaryModerators as $nonPrimaryModerator) {
                $nonPrimaryModerator = User::find($nonPrimaryModerator['id']);
                if (!$nonPrimaryModerator->checkPermissionTo(ShowsPermissions::CAN_BE_MODERATOR)) {
                    return new ApiErrorResponse('"'  . $nonPrimaryModerator->name. '" does not have the permission to be a non-primary moderator.', status: Response::HTTP_BAD_REQUEST);
                }
            }
        }

        /**
         * If the show is live, check if the user has the permission to set live shows.
         */
        if ($request->live && !$user->checkPermissionTo(ShowsPermissions::CAN_SET_LIVE_SHOWS, 'web')) {
            return new ApiErrorResponse('You are not allowed to set shows live.', status: Response::HTTP_FORBIDDEN);
        }

        /**
         * If the show is enabled, check if the user has the permission to enable shows.
         */
        if ($request->enabled && !$user->checkPermissionTo(ShowsPermissions::CAN_ENABLE_SHOWS, 'web')) {
            return new ApiErrorResponse('You are not allowed to enable shows.', status: Response::HTTP_FORBIDDEN);
        }

        $show = new Show();
        $show->title = $request->title;
        $show->body = $request->body;
        $show->start_date = $request->start_date;
        $show->end_date = $request->end_date;
        $show->is_live = $request->live;
        $show->enabled = $request->enabled;

        if (!$show->save()) {
            return new ApiErrorResponse('Something went wrong.');
        }

        try {
            $show->moderators()->sync(
                collect($request->moderators)->mapWithKeys(function ($moderator) {
                    return [$moderator['id'] => ['primary' => $moderator['primary']]];
                })
            );
        } catch (\Exception $e) {
            $show->delete();
            return new ApiErrorResponse('Something went wrong.', $e);
        }

        $show->load([
            "moderators" => function ($query) {
                $query->withPivot('primary');
            }
        ]);

        return new ShowResource($show->makeHidden('primary_moderator'));
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Show $show
     * @return \App\Http\Resources\ShowResource | \App\Http\Responses\ApiErrorResponse
     */
    public function show(Request $request, Show $show)
    {
        $IS_USER_LOGGED_IN = $request->user('sanctum') !== null;
        /**
         * If the show is disabled and the user is not logged in,
         * it should return a 404 error.
         */
        if (!$IS_USER_LOGGED_IN) {
            if ($show->enabled == false) {
                throw new NotFoundHttpException('No query results for model [App\\Models\\Show] ' . $show->id, code: Response::HTTP_NOT_FOUND, headers: ['Accept' => 'application/json', 'Content-Type' => 'application/json']);
            }
        }

        /**
         * If the show is disabled and the user is logged in,
         * it should return a 404 error if the user is not a moderator of the show
         * and doesn't have the permission to view disabled shows of others.
         */
        if ($IS_USER_LOGGED_IN) {
            /** @var \App\Models\User $user */
            $user = $request->user('sanctum');
            $isPrimaryModerator = $show->moderators()->where('moderator_id', '=', $user->id)->where('primary', '=', true)->exists();
            if (!$isPrimaryModerator) {
                if ($show->enabled == false && !$user->checkPermissionTo(ShowsPermissions::CAN_VIEW_DISABLED_SHOWS_OTHERS, 'web')) {
                    throw new NotFoundHttpException('No query results for model [App\\Models\\Show] ' . $show->id, code: Response::HTTP_NOT_FOUND, headers: ['Accept' => 'application/json', 'Content-Type' => 'application/json']);
                }
            }
        }

        if (!$IS_USER_LOGGED_IN) {
            $show->makeHidden('locked_by');
        }

        $show->load([
            "moderators" => function ($query) {
                $query->withPivot('primary');
            }
        ]);

        return new ShowResource($show);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Show $show
     * @return \App\Http\Resources\ShowResource | \App\Http\Responses\ApiErrorResponse
     */
    public function update(Request $request, Show $show)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'sometimes|string',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
            'live' => 'required|boolean',
            'enabled' => 'required|boolean',
            'moderators' => 'required|array',
            'moderators.*' => 'required|array',
            'moderators.*.id' => 'required|integer|distinct|exists:users,id',
            'moderators.*.primary' => 'required|boolean',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Check if the user is the primary moderator of the show.
        $isPrimaryModerator = $show->moderators()->where('moderator_id', '=', $user->id)->where('primary', '=', true)->exists();

        /**
         * If the show is disabled and the user is logged in,
         * it should return a 404 error if the user is not a moderator of the show
         * and doesn't have the permission to view disabled shows of others.
         */
        if (!$isPrimaryModerator && $show->enabled === false && !$user->checkPermissionTo(ShowsPermissions::CAN_VIEW_DISABLED_SHOWS_OTHERS)) {
            throw new NotFoundHttpException(
                'No query results for model [App\\Models\\Show] ' . $show->id,
                code: Response::HTTP_NOT_FOUND,
                headers: ['Accept' => 'application/json', 'Content-Type' => 'application/json']
            );
        }

        /**
         * Check if the show is currently locked by another user.
         */
        if ($show->locked_by !== null && $show->locked_by !== $user->id) {
            return new ApiErrorResponse('The show is currently locked by another user.', status: Response::HTTP_LOCKED);
        }

        /**
         * If the user is not the primary moderator of the show, and doesn't have the permission to update shows of others,
         * it should return a 403 error.
         */
        if (!$isPrimaryModerator && !$user->checkPermissionTo(ShowsPermissions::CAN_UPDATE_SHOWS_OTHERS, 'web')) {
            return new ApiErrorResponse('You are not allowed to update shows of others.', status: Response::HTTP_FORBIDDEN);
        }

        /**
         * Check if the show collides with another show.
         */
        if (!$this->_checkForOtherShow($request->start_date, $request->end_date, $show->id)) {
            return new ApiErrorResponse('There is already a show scheduled for this time.', status: Response::HTTP_BAD_REQUEST);
        }

        /**
         * Get primary moderators.
         */
        $primaryModerator = collect($request->moderators)->filter(function ($moderator) {
            return $moderator['primary'] === true;
        });

        /**
         * A show must have exactly one primary moderator.
         */
        if ($primaryModerator->count() !== 1) {
            return new ApiErrorResponse('There must be exactly one primary moderator.', status: Response::HTTP_BAD_REQUEST);
        }

        // Get the new primary moderator.
        $newPrimaryModerator = User::find($primaryModerator->first()['id']);

        /**
         * Check if the primary moderator is the same as the old primary moderator,
         * and if not, check if the user has the permission to update shows of others.
         */
        if ($newPrimaryModerator->id !== $show->primary_moderator->first()->id) {
            if (!$user->checkPermissionTo(ShowsPermissions::CAN_UPDATE_SHOWS_OTHERS, 'web')) {
                return new ApiErrorResponse('You are not allowed to update shows of others.', status: Response::HTTP_FORBIDDEN);
            }
        }

        /**
         * Check if the new primary moderator has the permission to be a primary moderator.
         */
        if (!$newPrimaryModerator->checkPermissionTo(ShowsPermissions::CAN_BE_PRIMARY_MODERATOR)) {
            return new ApiErrorResponse('The new primary moderator, "'. $newPrimaryModerator->name .'" does not have the permission to be a primary moderator.', status: Response::HTTP_BAD_REQUEST);
        }

        // Get the non-primary moderators.
        $nonPrimaryModerators = collect($request->moderators)->filter(function ($moderator) {
            return $moderator['primary'] === false;
        });

        /**
         * Check if there are any moderators that are not primary moderators.
         * If there are, check if the user has changed the moderators.
         * If the user has changed the moderators, check if the user has the permission to add non-primary moderators.
         */
        if ($nonPrimaryModerators->count() > 0) {
            // Check if the user has changed the moderators itself.
            $oldNonPrimaryModerators = $show->moderators()->where('primary', '=', false)->get();
            $oldNonPrimaryModeratorsIds = $oldNonPrimaryModerators->pluck('id')->toArray();
            $newNonPrimaryModeratorsIds = collect($nonPrimaryModerators)->pluck('id')->toArray();
            if ($oldNonPrimaryModeratorsIds !== $newNonPrimaryModeratorsIds) {
                if (!$user->checkPermissionTo(ShowsPermissions::CAN_ADD_MODERATORS, 'web')) {
                    return new ApiErrorResponse('You are not allowed to add non-primary moderators.', status: Response::HTTP_FORBIDDEN);
                }
                /**
                 * Check if the non-primary moderators have the permission to be a moderator.
                 */
                foreach ($nonPrimaryModerators as $nonPrimaryModerator) {
                    $nonPrimaryModerator = User::find($nonPrimaryModerator['id']);
                    if (!$nonPrimaryModerator->checkPermissionTo(ShowsPermissions::CAN_BE_MODERATOR)) {
                        return new ApiErrorResponse('"'  . $nonPrimaryModerator->name. '" does not have the permission to be a non-primary moderator.', status: Response::HTTP_BAD_REQUEST);
                    }
                }
            }
        }

        /**
         * Check if the is_live field was changed, and if so, check if the user has the permission to change the live status.
         */
        if ($show->is_live !== $request->is_live) {
            if (!$user->checkPermissionTo(ShowsPermissions::CAN_SET_LIVE_SHOWS, 'web')) {
                return new ApiErrorResponse('You are not allowed to change the live status of shows.', status: Response::HTTP_FORBIDDEN);
            }
        }

        /**
         * Check if the enabled field was changed, and if so, check if the user has the permission to enable or disable shows.
         */
        if ($show->enabled !== $request->enabled) {
            if (!$user->checkPermissionTo(ShowsPermissions::CAN_ENABLE_SHOWS, 'web')) {
                return new ApiErrorResponse('You are not allowed to enable or disable shows.', status: Response::HTTP_FORBIDDEN);
            }
        }

        $show->title = $request->title;
        $show->body = $request->body;
        $show->start_date = $request->start_date;
        $show->end_date = $request->end_date;
        $show->is_live = $request->is_live;
        $show->enabled = $request->enabled;

        $show->moderators()->sync(
            collect($request->moderators)->mapWithKeys(function ($moderator) {
                return [$moderator['id'] => ['primary' => $moderator['primary']]];
            })
        );

        if ($show->save()) {
            return new ShowResource($show->makeHidden('primary_moderator'));
        } else {
            return new ApiErrorResponse('Something went wrong.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Show $show
     * @return \App\Http\Responses\ApiErrorResponse | \App\Http\Responses\ApiSuccessResponse
     */
    public function destroy(Show $show)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        /**
         * If the show is disabled and the user is not the primary moderator of the show,
         * it should return a 404 error.
         */
        $isPrimaryModerator = $show->moderators()->where('moderator_id', '=', $user->id)->where('primary', '=', true)->exists();
        if (!$isPrimaryModerator && $show->enabled === false && !$user->checkPermissionTo(ShowsPermissions::CAN_VIEW_DISABLED_SHOWS_OTHERS)) {
            throw new NotFoundHttpException(
                'No query results for model [App\\Models\\Show] ' . $show->id,
                code: Response::HTTP_NOT_FOUND,
                headers: ['Accept' => 'application/json', 'Content-Type' => 'application/json']
            );
        }

        /**
         * Check if the show is currently locked by another user.
         */
        if ($show->locked_by !== null && $show->locked_by !== $user->id) {
            return new ApiErrorResponse('The show is currently locked by another user.', status: Response::HTTP_LOCKED);
        }

        /**
         * If the user is not the primary moderator of the show, and doesn't have the permission to delete shows of others,
         * it should return a 403 error.
         */
        if (!$isPrimaryModerator && !$user->checkPermissionTo(ShowsPermissions::CAN_DELETE_SHOWS_OTHERS, 'web')) {
            return new ApiErrorResponse('You are not allowed to delete shows of others.', status: Response::HTTP_FORBIDDEN);
        }

        /**
         * If the user is the primary moderator of the show, or has the permission to delete shows of others,
         * it should delete the show.
         */
        if ($show->delete()) {
            return new ApiSuccessResponse('', Response::HTTP_NO_CONTENT);
        } else {
            return new ApiErrorResponse('Something went wrong.');
        }
    }
}

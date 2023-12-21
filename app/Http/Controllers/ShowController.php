<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShowResource;
use App\Models\Show;
use App\Permissions\ShowsPermissions;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

class ShowController extends Controller
{

    /**
     * Retrieve a paginated list of shows based on the provided query parameters.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {

        static $SORT_OPTIONS = [
            'id',
            'id:asc',
            'id:desc',
            'start',
            'start:asc',
            'start:desc',
            'end',
            'end:asc',
            'end:desc',
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
            'start_date' => ['date'],
            'end_date' => ['exclude_without:start_date', 'date', 'after:start_date'],
            'days' => ['integer', 'min:1'],
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
            $shows->where('enabled', '=', true);
        }

        $shows->where(function ($query) use ($validated) {
            $NOW = now();
            if (isset($validated['start_date'])) {
                $query->whereBetween('start_date',
                [
                    $validated['start_date'],
                    $validated['end_date'] ?? $validated['days'] ?? $validated['start_date']->addDays(7)
                ]);
            } elseif (isset($validated['days']) && !isset($validated['start_date'])) {
                $inXDays = (clone $NOW)->addDays($validated['days']);
                $query->whereBetween('start_date',
                [
                    $NOW,
                    $inXDays
                ])
                ->orWhereBetween('end_date',
                [
                    $NOW,
                    $inXDays
                ]);
            } else {
                $in7Days = (clone $NOW)->addDays(7);
                $query->whereBetween('start_date',
                [
                    $NOW,
                    $in7Days
                ])
                ->orWhereBetween('end_date',
                [
                    $NOW,
                    $in7Days
                ]);
            }
        });

        /**
         * Hide shows that are not enabled and the primary moderator is not the current user.
         */
        if (auth()->check()) {
            $user = auth()->user();
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

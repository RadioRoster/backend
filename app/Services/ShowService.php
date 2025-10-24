<?php

namespace App\Services;

use App\Models\Show;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShowService
{
    /**
     * Get a paginated list of shows.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getShows(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Show::query();

        // Apply filters
        if (isset($filters['enabled'])) {
            $query->where('enabled', $filters['enabled']);
        }

        if (isset($filters['is_live'])) {
            $query->where('is_live', $filters['is_live']);
        }

        // Apply sorting
        if (isset($filters['sort'])) {
            $sort = explode(':', $filters['sort']);
            $query->orderBy($sort[0], $sort[1] ?? 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get a single show by ID.
     *
     * @param int $id
     * @return Show|null
     */
    public function getShow(int $id): ?Show
    {
        return Show::find($id);
    }

    /**
     * Create a new show.
     *
     * @param array $data
     * @return Show
     */
    public function createShow(array $data): Show
    {
        return Show::create($data);
    }

    /**
     * Update an existing show.
     *
     * @param int $id
     * @param array $data
     * @return Show|null
     */
    public function updateShow(int $id, array $data): ?Show
    {
        $show = Show::find($id);

        if (!$show) {
            return null;
        }

        $show->update($data);

        return $show->fresh();
    }

    /**
     * Delete a show.
     *
     * @param int $id
     * @return bool
     */
    public function deleteShow(int $id): bool
    {
        $show = Show::find($id);

        if (!$show) {
            return false;
        }

        return $show->delete();
    }

    /**
     * Toggle the live status of a show.
     *
     * @param int $id
     * @return Show|null
     */
    public function toggleLiveStatus(int $id): ?Show
    {
        $show = Show::find($id);

        if (!$show) {
            return null;
        }

        $show->is_live = !$show->is_live;
        $show->save();

        return $show;
    }

    /**
     * Lock a show by a user.
     *
     * @param int $showId
     * @param int $userId
     * @return Show|null
     */
    public function lockShow(int $showId, int $userId): ?Show
    {
        $show = Show::find($showId);

        if (!$show) {
            return null;
        }

        $show->locked_by = $userId;
        $show->save();

        return $show;
    }

    /**
     * Unlock a show.
     *
     * @param int $showId
     * @return Show|null
     */
    public function unlockShow(int $showId): ?Show
    {
        $show = Show::find($showId);

        if (!$show) {
            return null;
        }

        $show->locked_by = null;
        $show->save();

        return $show;
    }
}

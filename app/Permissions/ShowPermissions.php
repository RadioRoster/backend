<?php

namespace App\Permissions;

/**
 * Class ShowPermissions
 *
 * This class defines the permissions related to shows.
 */
class ShowPermissions
{
    /** Permission for viewing shows. */
    public const CAN_VIEW_SHOWS = 'shows.view';

    /** Permission for creating shows. */
    public const CAN_CREATE_SHOWS = 'shows.create';

    /** Permission for updating shows. */
    public const CAN_UPDATE_SHOWS = 'shows.update';

    /** Permission for deleting shows. */
    public const CAN_DELETE_SHOWS = 'shows.delete';
}

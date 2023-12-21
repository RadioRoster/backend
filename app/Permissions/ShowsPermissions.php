<?php

namespace App\Permissions;

/**
 * Class ShowsPermissions
 *
 * This class defines the permissions related to shows.
 */
class ShowsPermissions
{
    /** Permission for viewing others disabled shows. */
    public const CAN_VIEW_DISABLED_SHOWS_OTHERS = 'view-disabled-shows-others';

    /** Permission for creating own shows as primary moderator. */
    public const CAN_CREATE_SHOWS = 'create-shows';

    /** Permission for creating shows for others as primary moderator. */
    public const CAN_CREATE_SHOWS_OTHERS = 'create-shows-others';

    /** Permission for updating own shows when primary moderator. */
    public const CAN_UPDATE_SHOWS = 'update-shows';

    /** Permission for updating shows for others. */
    public const CAN_UPDATE_SHOWS_OTHERS = 'update-shows-others';

    /** Permission for deleting own shows when primary moderator. */
    public const CAN_DELETE_SHOWS = 'delete-shows';

    /** Permission for deleting shows for others. */
    public const CAN_DELETE_SHOWS_OTHERS = 'delete-shows-others';

    /** Permission for to be primary moderator of a show. */
    public const CAN_BE_PRIMARY_MODERATOR = 'primary-moderator-shows';

    /** Permission for to be moderator of a show. */
    public const CAN_BE_MODERATOR = 'moderator-shows';

    /** Permission to change a show's live status. */
    public const CAN_SET_LIVE_SHOWS = 'set-live-shows';

    /** Permission to enable or disable a show. */
    public const CAN_ENABLE_SHOWS = 'enable-shows';
}

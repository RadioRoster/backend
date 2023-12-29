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
    public const CAN_VIEW_DISABLED_SHOWS_OTHERS = 'shows.view-disabled.others';

    /** Permission for creating own shows as primary moderator. */
    public const CAN_CREATE_SHOWS = 'shows.create';

    /** Permission for creating shows for others as primary moderator. */
    public const CAN_CREATE_SHOWS_OTHERS = 'shows.create.others';

    /** Permission for updating own shows when primary moderator. */
    public const CAN_UPDATE_SHOWS = 'shows.update';

    /** Permission for updating shows for others. */
    public const CAN_UPDATE_SHOWS_OTHERS = 'shows.update.others';

    /** Permission for deleting own shows when primary moderator. */
    public const CAN_DELETE_SHOWS = 'shows.delete';

    /** Permission for deleting shows for others. */
    public const CAN_DELETE_SHOWS_OTHERS = 'shows.delete.others';

    /** Permission to be primary moderator of a show. */
    public const CAN_BE_PRIMARY_MODERATOR = 'shows.be-primary-moderator';

    /** Permission for adding non-primary moderators to a show. */
    public const CAN_ADD_MODERATORS = 'shows.add-moderators';

    /** Permission to be moderator of a show, but not primary . */
    public const CAN_BE_MODERATOR = 'shows.be-moderator';

    /** Permission to change a show's live status. */
    public const CAN_SET_LIVE_SHOWS = 'shows.set-live';

    /** Permission to enable or disable a show. */
    public const CAN_ENABLE_SHOWS = 'shows.enable';
}

<?php

namespace App\Permissions;

/**
 * Class UsersPermissions
 *
 * This class defines the permissions related to users.
 */
class UsersPermissions
{
    /** Permission for listing and view all users. */
    public const CAN_LIST_USERS = 'users.list';

    /** Permission for showing users itself. */
    public const CAN_SHOW_USERS = 'users.show';

    /** Permission for creating users. */
    public const CAN_CREATE_USERS = 'users.create';

    /** Permission for updating users. */
    public const CAN_UPDATE_USERS = 'users.update';

    /** Permission for updating users itself. */
    public const CAN_UPDATE_USERS_SELF = 'users.update.self';

    /** Permission for deleting users. */
    public const CAN_DELETE_USERS = 'users.delete';
}

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
    public const CAN_LIST_USERS = 'list-users';

    /** Permission for showing users itself. */
    public const CAN_SHOW_USERS = 'show-users';

    /** Permission for creating users. */
    public const CAN_CREATE_USERS = 'create-users';

    /** Permission for updating users. */
    public const CAN_UPDATE_USERS = 'update-users';

    /** Permission for updating users itself. */
    public const CAN_UPDATE_USERS_SELF = 'update-users-self';

    /** Permission for deleting users. */
    public const CAN_DELETE_USERS = 'delete-users';
}

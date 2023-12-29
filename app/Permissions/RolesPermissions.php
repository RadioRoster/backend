<?php

namespace App\Permissions;

/**
 * Class UsersPermissions
 *
 * This class defines the permissions related to users.
 */
class RolesPermissions
{
    /** Permission for showing roles. */
    public const CAN_SHOW_ROLES = 'roles.show';

    /** Permission for creating users. */
    public const CAN_CREATE_ROLES = 'roles.create';

    /** Permission for updating users. */
    public const CAN_UPDATE_ROLES = 'roles.update';

    /** Permission for deleting users. */
    public const CAN_DELETE_ROLES = 'roles.delete';
}

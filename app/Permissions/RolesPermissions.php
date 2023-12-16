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
    public const CAN_SHOW_ROLES = 'show-roles';

    /** Permission for creating users. */
    public const CAN_CREATE_ROLES = 'create-roles';

    /** Permission for updating users. */
    public const CAN_UPDATE_ROLES = 'update-roles';

    /** Permission for deleting users. */
    public const CAN_DELETE_ROLES = 'delete-roles';
}

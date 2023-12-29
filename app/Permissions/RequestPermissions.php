<?php

namespace App\Permissions;

/**
 * Class RequestPermissions
 *
 * This class defines the permissions related to requests.
 */
class RequestPermissions
{
    /** Permission for listing and view all users. */
    public const CAN_VIEW_REQUESTS = 'requests.view';

    /** Permission for deleting users. */
    public const CAN_DELETE_REQUESTS = 'requests.delete';
}

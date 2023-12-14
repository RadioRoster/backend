<?php

use App\Permissions\UsersPermissions;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('permissions')->insert(
            [
                [
                    'name' => UsersPermissions::CAN_LIST_USERS,
                    'guard_name' => 'web',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => UsersPermissions::CAN_SHOW_USERS,
                    'guard_name' => 'web',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => UsersPermissions::CAN_CREATE_USERS,
                    'guard_name' => 'web',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => UsersPermissions::CAN_UPDATE_USERS,
                    'guard_name' => 'web',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => UsersPermissions::CAN_UPDATE_USERS_SELF,
                    'guard_name' => 'web',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => UsersPermissions::CAN_DELETE_USERS,
                    'guard_name' => 'web',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            UsersPermissions::CAN_LIST_USERS,
            UsersPermissions::CAN_SHOW_USERS,
            UsersPermissions::CAN_CREATE_USERS,
            UsersPermissions::CAN_UPDATE_USERS,
            UsersPermissions::CAN_UPDATE_USERS_SELF,
            UsersPermissions::CAN_DELETE_USERS,
        ])->delete();
    }
};

<?php

use App\Permissions\RolesPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('permissions')->insert([
            [
                'name' => RolesPermissions::CAN_SHOW_ROLES,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => RolesPermissions::CAN_CREATE_ROLES,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => RolesPermissions::CAN_UPDATE_ROLES,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => RolesPermissions::CAN_DELETE_ROLES,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            RolesPermissions::CAN_SHOW_ROLES,
            RolesPermissions::CAN_CREATE_ROLES,
            RolesPermissions::CAN_UPDATE_ROLES,
            RolesPermissions::CAN_DELETE_ROLES,
        ])->delete();
    }
};

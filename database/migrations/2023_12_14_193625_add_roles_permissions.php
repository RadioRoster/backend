<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Permission::create([
            'name' => 'roles.show',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'roles.create',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'roles.update',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'roles.delete',
            'guard_name' => 'web',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'roles.show',
            'roles.create',
            'roles.update',
            'roles.delete',
        ])->delete();
    }
};

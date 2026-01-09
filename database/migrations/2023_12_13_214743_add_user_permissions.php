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
            'name' => 'users.list',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'users.show',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'users.create',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'users.update',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'users.update.self',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'users.delete',
            'guard_name' => 'web',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'users.list',
            'users.show',
            'users.create',
            'users.update',
            'users.update.self',
            'users.delete',
        ])->delete();
    }
};

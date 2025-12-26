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
            'name' => 'shows.view',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.create',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.update',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.delete',
            'guard_name' => 'web',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'shows.view',
            'shows.create',
            'shows.update',
            'shows.delete',
        ])->delete();
    }
};

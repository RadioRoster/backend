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
            'name' => 'shows.view-disabled.others',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.create',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.create.others',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.update',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.update.others',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.delete',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.delete.others',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.be-primary-moderator',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.be-moderator',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.add-moderators',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.set-live',
            'guard_name' => 'web',
        ]);
        Permission::create([
            'name' => 'shows.enable',
            'guard_name' => 'web',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'shows.view-disabled.others',
            'shows.create',
            'shows.create.others',
            'shows.update',
            'shows.update.others',
            'shows.delete',
            'shows.delete.others',
            'shows.be-primary-moderator',
            'shows.be-moderator',
            'shows.add-moderators',
            'shows.set-live',
            'shows.enable',
        ])->delete();
    }
};

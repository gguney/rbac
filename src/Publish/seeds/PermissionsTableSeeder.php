<?php

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = collect();
        $actions = collect();
        $routes = collect(Route::getRoutes()->getRoutes());
        $routes->each(function ($route) use ($actions) {
            $action = $route->getAction();
            if (array_key_exists('controller', $action)) {
                if (str_contains($action['controller'], config('rbac.controller_path')) && !str_contains($action['controller'],
                        'App\Http\Controllers\Auth')
                ) {
                    $actions->push($action);
                }
            }
        });

        $actions->each(function ($action) use ($permissions) {
            $permission = Permission::where('action', $action['controller'])->first();
            if (!$permission) {
                $displayName = null;
                if (isset($action['as'])) {
                    $displayName = $action['as'];
                    $displayName = str_replace('.', ' ', $displayName);
                    //$displayName = str_replace('_', ' ',snake_case(studly_case($displayName)));
                    $displayName = ucwords($displayName);
                }
                $permission = Permission::updateOrCreate([
                    'action'       => $action['controller'],
                    'name'         => isset($action['as']) ? $action['as'] : null,
                    'description'  => (isset($action['as']) && strpos($action['as'], '.')) ? explode(".",
                        $action['as'])[0] : null,
                    'display_name' => $displayName,
                    'created_at'   => \Carbon\Carbon::now(),
                    'updated_at'   => \Carbon\Carbon::now(),
                ]);

            }
            if (isset($permission) && !$permissions->contains($permission->id)) {
                $permissions->push($permission->id);
            }
        });

        $role = Role::firstOrCreate([
            'name'         => 'developer',
            'display_name' => 'developer',
            'description'  => 'developer'
        ]);
        $role->attachPermissions($permissions);

        User::where('id', 1)->first()->attachRole($role);


    }
}


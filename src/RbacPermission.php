<?php
namespace GGuney\Rbac;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Route;

trait RbacPermission{

    /**
     * Many-to-Many relations with role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission', 'permission_id', 'role_id');
    }
    
    public static function syncPermissions()
    {
        $permissions = collect();
        $actions = collect();
        $routes = collect(Route::getRoutes()->getRoutes());
        $routes->each(function ($route) use ($actions) {
            $action = $route->getAction();
            if (array_key_exists('controller', $action)) {
                if (str_contains($action['controller'], config('rbac.controller_path')) && !str_contains($action['controller'],
                        config('rbac.controller_path').'\Auth')
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
                    $displayName = title_case(str_replace(['.', '-'], [' ', ' '], $displayName));
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
        $developerRole = Role::whereName('developer')->first();
        if($developerRole){
                $developerRole->attachPermissions($permissions);
        }
    }
}

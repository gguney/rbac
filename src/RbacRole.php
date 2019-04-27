<?php
namespace GGuney\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Mockery\Exception;

trait RbacRole
{
    private $cachedPermissions;
    private $time = 60000;

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id');
    }

    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role', 'role_id', 'user_id');
    }

    /**
     * Many-to-Many relations with the permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission', 'role_id', 'permission_id');
    }

    public function getPermissions()
    {
        $cacheKey = config('rbac.role_permissions_cache_key');
        $cached = Cache::tags([$cacheKey])->get($this->id);
        if ($cached) {
            $this->cachedPermissions = Cache::tags([$cacheKey])->get($this->id);
        } else {
            $permissions = $this->permissions()->get();
            $this->cachedPermissions = $permissions;
            Cache::tags([$cacheKey])->put($this->id, $permissions, $this->time);
        }

        return $this->cachedPermissions;
    }

    public function attachPermissions($permissions = null){
        $currentPermissions = $this->permissions()->get()->map(function($permission){
            return $permission->id;
        })->toArray();
        if(!isset($permissions)){
            return true;
        }
        $tmpPermissions = [];
        foreach($permissions as $permission){
            if(!in_array($permission, $currentPermissions)){
                $tmpPermissions[] = $permission;
            }
        }
        $this->permissions()->attach($tmpPermissions);
        if(Auth::check()){
            Auth::user()->forgetPermissions();
        }
    }

    public function detachCurrentPermissions(){
        $currentPermissions = $this->permissions()->get()->map(function($permission){
            return $permission->id;
        })->toArray();
        $this->permissions()->detach($currentPermissions);
    }
}
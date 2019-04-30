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
        $cacheKey = config('rbac.role_permissions_cache_key', 'rbac_role_permissions');
        $data = Cache::tags([$cacheKey])->get($this->id);
        if (!$data) {
            $data = $this->permissions()->get();
            Cache::tags([$cacheKey])->put($this->id, $data, $this->time);
        }

        return $data;
    }

    public function forgetPermissions()
    {
        $cacheKey = config('rbac.role_permissions_cache_key', 'rbac_role_permissions');
        Cache::tags([$cacheKey])->flush();
    }

    public function syncPermissions($permissionIds)
    {
        $this->permissions()->attach($permissionIds);
        $this->forgetPermissions();
    }
}
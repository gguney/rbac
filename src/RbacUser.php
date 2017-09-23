<?php

namespace GGuney\Rbac;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait RbacUser
{

    protected $permissions;
    protected $roles;
    private $time = 60;

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function forgetRoles(){
        $cacheKey = config('rbac.user_roles_cache_key');
        Cache::tags([$cacheKey])->flush();
    }

    public function forgetPermissions(){
        $cacheKey = config('rbac.user_permissions_cache_key');
        Cache::tags([$cacheKey])->flush();
    }

    public function getRoles()
    {
        $cacheKey = config('rbac.user_roles_cache_key');
        $cached = Cache::tags([$cacheKey])->get($this->id);
        if ($cached) {
            $this->roles = Cache::tags([$cacheKey])->get($this->id);
        } else {
            $userRoles = $this->roles()->get();

            $userRoles = $userRoles->map(function ($role) {
                return $role->pivot->role_id;
            });

            $userRoles = Role::whereIn('id', $userRoles)->get();
            $roles = [];
            foreach ($userRoles as $userRole) {
                $roles[$userRole->id] = ['name' => $userRole->name, 'display_name' => $userRole->display_name];
            }
            $this->roles = $roles;
            Cache::tags([$cacheKey])->put($this->id, $roles, $this->time);
        }
    }

    public function getPermissions()
    {
        $cacheKey = config('rbac.user_permissions_cache_key');
        $cached = Cache::tags([$cacheKey])->get($this->id);
        if ($cached) {
            $this->permissions = Cache::tags([$cacheKey])->get($this->id);
        } else {
            $userRoles = $this->roles()->get();

            $userRoles = $userRoles->map(function ($role) {
                return $role->pivot->role_id;
            });

            $rolePermissions = Role::with('permissions')->whereIn('id', $userRoles)->get();
            $userPermissions = collect();
            foreach ($rolePermissions as $rolePermission) {
                $userPermissions = $userPermissions->merge($rolePermission->permissions);
            }
            $permissions = null;
            foreach ($userPermissions as $userPermission) {
                $permissions[$userPermission->id] = [
                    'action'      => $userPermission->action,
                    'name' => $userPermission->name
                ];
            }
            $this->permissions = $permissions;
            Cache::tags([$cacheKey])->put($this->id, $permissions, $this->time);
        }

    }

    public function attachRole($role)
    {
        $currentRoles = $this->roles()->get()->map(function ($role) {
            return $role->id;
        })->toArray();

        if (!in_array($role->id, $currentRoles)) {
            $this->roles()->attach($role);
        }

    }

    public function detachCurrentRoles()
    {
        $currentRoles = $this->roles()->get()->map(function ($role) {
            return $role->id;
        })->toArray();
        $this->roles()->detach($currentRoles);
    }

    public function attachRoles($roles = null)
    {
        $currentRoles = $this->roles()->get()->map(function ($role) {
            return $role->id;
        })->toArray();
        if(!isset($roles) || empty($roles)){
            return true;
        }
        $tmpRoles = [];
        foreach ($roles as $role){
            if (!in_array($role, $currentRoles)) {
                $tmpRoles[] = $role;
            }
        }
        $this->roles()->attach($tmpRoles);
        $this->forgetRoles();
    }

    public function hasAccessTo($controller)
    {
        if (($this->permissions !== null)) {
            foreach ($this->permissions as $permission) {
                if ($permission['action'] == $controller) {
                    return true;
                }
            }
        }
        return false;
    }

    public function hasRole($roleCodeName)
    {
        if (($this->roles !== null)) {
            foreach ($this->roles as $role) {
                if(is_array($roleCodeName) && in_array($role['name'], $roleCodeName) ){
                    return true;
                }
                if ($role['name'] == $roleCodeName) {
                    return true;
                }
            }
        }
        return false;
    }

    public function permittedTo($controllerName)
    {
        if (($this->permissions !== null) ) {
            foreach ($this->permissions as $permission) {
                if ($permission['name'] == $controllerName) {
                    return true;
                }
            }
        }
        return false;
    }

}
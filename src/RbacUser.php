<?php

namespace GGuney\Rbac;

use App\Models\Permission;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait RbacUser
{
    protected $cachedPermissions;
    protected $cachedRoles;
    protected $cachedCompany;
    private $time = 60000; //seconds

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id');
    }

    public function company()
    {
        return $this->hasOne(\App\Models\Company::class, 'id', 'company_id');
    }

    public function forgetRoles(){
        $cacheKey = config('rbac.user_roles_cache_key');
        Cache::tags([$cacheKey])->flush();
    }

    public function forgetPermissions(){
        $cacheKey = config('rbac.user_permissions_cache_key');
        Cache::tags([$cacheKey])->flush();
    }
    public function getCompany()
    {
        $cacheKey = config('rbac.user_company_cache_key');
        $cached = Cache::tags([$cacheKey])->get($this->id);
        if ($cached) {
            $this->cachedCompany = Cache::tags([$cacheKey])->get($this->id);
        } else {
            $company = $this->company;
            $this->cachedCompany = $company;
            Cache::tags([$cacheKey])->put($this->id, $company, $this->time);
        }

        return $this->cachedCompany;
    }

    public function getRoles()
    {
        $cacheKey = config('rbac.user_roles_cache_key');
        $cached = Cache::tags([$cacheKey])->get($this->id);
        if ($cached) {
            $this->cachedRoles = Cache::tags([$cacheKey])->get($this->id);
        } else {
            $userRoles = $this->roles()->get();
            $this->cachedRoles = $userRoles;
            Cache::tags([$cacheKey])->put($this->id, $userRoles, $this->time);
        }

        return $this->cachedRoles;
    }

    public function getPermissions()
    {
        $cacheKey = config('rbac.user_permissions_cache_key');
        $cached = Cache::tags([$cacheKey])->get($this->id);
        if (!$cached) {
            $this->cachedPermissions = Cache::tags([$cacheKey])->get($this->id);
        } else {
            $userRoles = $this->getRoles();
            $userPermissions = collect();
            foreach ($userRoles as $userRole) {
                $permissions = $userRole->getPermissions();
                $userPermissions = $userPermissions->merge($permissions);
            }
            $this->cachedPermissions = $userPermissions;
            Cache::tags([$cacheKey])->put($this->id, $userPermissions, $this->time);
        }

        return $this->cachedPermissions;
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
        $this->getPermissions();
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
        $this->getRoles();
        if (($this->cachedRoles !== null)) {
            foreach ($this->cachedRoles as $role) {
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
        $this->getPermissions();
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
<?php

namespace GGuney\Rbac;

use App\Models\Permission;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait RbacUser
{
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
        $cacheKey = config('rbac.user_roles_cache_key', 'rbac_user_roles');
        $this->forgetCache($cacheKey);
    }

    public function forgetPermissions(){
        $cacheKey = config('rbac.user_permissions_cache_key', 'rbac_user_permissions');
        $this->forgetCache($cacheKey);
    }

    public function forgetCompany(){
        $cacheKey = config('rbac.user_company_cache_key', 'rbac_user_company');
        $this->forgetCache($cacheKey);
    }

    private function forgetCache($cacheKey){
        Cache::tags([$cacheKey])->flush();
    }

    public function getCompany()
    {
        $cacheKey = config('rbac.user_company_cache_key', 'rbac_user_company');
        $data = Cache::tags([$cacheKey])->get($this->id);
        if (!$data) {
            $data = $this->company;
            Cache::tags([$cacheKey])->put($this->id, $data, $this->time);
        }

        return $data;
    }

    public function getRoles()
    {
        $cacheKey = config('rbac.user_roles_cache_key', 'rbac_user_roles');
        $data = Cache::tags([$cacheKey])->get($this->id);
        if (!$data) {
            $data = $this->roles()->get();
            Cache::tags([$cacheKey])->put($this->id, $data, $this->time);
        }

        return $data;
    }

    public function getPermissions()
    {
        $cacheKey = config('rbac.user_permissions_cache_key', 'rbac_user_permissions');
        $data = Cache::tags([$cacheKey])->get($this->id);
        if (!$data) {
            $userRoles = $this->getRoles();
            $userPermissions = collect();
            foreach ($userRoles as $userRole) {
                $permissions = $userRole->getPermissions();
                $userPermissions = $userPermissions->merge($permissions);
            }
            $data = $userPermissions;
            Cache::tags([$cacheKey])->put($this->id, $data, $this->time);
        }

        return $data;
    }

    public function syncRoles($roleIds)
    {
        $this->roles()->sync($roleIds);
        $this->forgetRoles();
        $this->forgetPermissions();
    }

    public function hasModule($controller)
    {
        $company = $this->getCompany();
        if($company){
            $modules = $company->getModules();
            if (($modules !== null)) {
                foreach ($modules as $module) {
                    if ($module->name == $controller) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function hasPermission($controller)
    {
        $permissions = $this->getPermissions();
        if (($permissions !== null)) {
            foreach ($permissions as $permission) {
                if ($permission['controller'] == $controller) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasRole($roleCodeName)
    {
        $roles = $this->getRoles();
        if (($roles !== null)) {
            foreach ($roles as $role) {
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
        $permissions = $this->getPermissions();
        if (($permissions !== null) ) {
            foreach ($permissions as $permission) {
                if ($permission['full_name'] == $controllerName) {
                    return true;
                }
            }
        }

        return false;
    }

}
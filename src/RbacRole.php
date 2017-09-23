<?php
namespace GGuney\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;

trait RbacRole
{

    private $permissions;

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }

    /**
     * Many-to-Many relations with the permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
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
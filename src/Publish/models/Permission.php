<?php

namespace App\Models;

use GGuney\Rbac\RbacPermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes, RbacPermission;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';

}

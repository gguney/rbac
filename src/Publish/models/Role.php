<?php

namespace App\Models;

use GGuney\Rbac\RbacRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes,RbacRole;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

}

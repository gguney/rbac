<?php
namespace GGuney\Rbac;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cache;

class LoginListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  OrderShipped  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $key = config('rbac.user_permissions_cache_key').$event->user->id;
        Cache::forget($key);
        $key = config('rbac.user_roles_cache_key').$event->user->id;
        Cache::forget($key);
    }
}

?>


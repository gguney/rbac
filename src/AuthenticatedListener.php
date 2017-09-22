<?php
namespace GGuney\Rbac;

use Illuminate\Auth\Events\Authenticated;

class AuthenticatedListener
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
    public function handle(Authenticated $event)
    {
        $event->user->getPermissions();
        $event->user->getRoles();
    }
}

?>


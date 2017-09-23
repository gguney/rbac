<?php
namespace GGuney\Rbac;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class RbacServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Publish/config/rbac.php', 'rbac');
    }

    public function boot()
    {

        Event::listen('Illuminate\Auth\Events\Authenticated', AuthenticatedListener::class);
        Event::listen('Illuminate\Auth\Events\Login', LoginListener::class);
        $this->bladeDirectives();

        $this->publishes([__DIR__ . '/Publish/seeds/PermissionsTableSeeder.php' => database_path('/seeds/PermissionsTableSeeder.php')]);
        $this->publishes([__DIR__ . '/Publish/migrations/create_roles_table.php' => database_path('/migrations/2017_05_11_000000_create_roles_table.php')]);
        $this->publishes([__DIR__ . '/Publish/models/' => app_path('/models')]);
        $this->publishes([__DIR__ . '/Publish/config/rbac.php' => config_path('rbac.php')]);

    }

    public function bladeDirectives(){
        \Blade::directive('permitted', function($expression) {
            return "<?php if (Auth::user()->permittedTo({$expression} )) : ?>";
        });

        \Blade::directive('endpermitted', function($expression) {
            return "<?php endif ?>";
        });

        \Blade::directive('hasrole', function($expression) {
            return "<?php if (Auth::user()->hasRole({$expression})) : ?>";
        });

        \Blade::directive('endhasrole', function($expression) {
            return "<?php endif ?>";
        });
    }

}

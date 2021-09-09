<?php
namespace Colin\Log;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider {

     /**
     * @var array
     */
    protected $commands = [
        Console\InstallCommand::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'log.session'    => Middleware\Session::class,
    ];

        /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'log' => [
        'log.auth',
        'log.pjax',
        'log.log',
        'log.bootstrap',
        'log.permission',
        //            'log.session',
        ],
    ];

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'log');

        $this->ensureHttps();

        if (file_exists($routes = admin_path('routes.php'))) {
            $this->loadRoutesFrom($routes);
        }

        $this->registerPublishing();
    }

    /**
     * Force to set https scheme if https enabled.
     *
     * @return void
     */
    protected function ensureHttps()
    {
        if (config('admin.https') || config('admin.secure')) {
            url()->forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }
    }

        /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config' => config_path()], 'colinlog-config');
            $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang')], 'colinlog-lang');
            $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')], 'colinlog-migrations');
            $this->publishes([__DIR__.'/../resources/assets' => public_path('vendor/colinlog')], 'colinlog-assets');
        }
    }

    // 註冊套件函式
    public function register()
    {
        $this->app->singleton('logsplit', function ($app) {
            return new LogSplit();
        });

        $this->loadAdminAuthConfig();

        $this->registerRouteMiddleware();
    }

    /**
     * Setup auth configuration.
     *
     * @return void
     */
    protected function loadAdminAuthConfig()
    {
        config(Arr::dot(config('admin.auth', []), 'auth.'));
    }

    /**
     * Register the route middleware.
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

        // register middleware group.
        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }

}

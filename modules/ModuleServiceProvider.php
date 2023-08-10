<?php

namespace Modules;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Modules\User\src\Commands\TestCommand;
use Modules\User\src\Http\Middlewares\DemoMiddleware;

class ModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $directories = array_map('basename', File::directories(__DIR__));
        if (!empty($directories)) {
            foreach ($directories as $directory) {
                $this->registerModule($directory);
            }
        }
    }

    private function registerModule($module)
    {
        $modulePath = __DIR__ . "/{$module}";

        //khai báo thành phần
        if (File::exists($modulePath . '/routes/routes.php')) {
            $this->loadRoutesFrom($modulePath . "/routes/routes.php");
        }
        //khai báo Migrations
        if (File::exists($modulePath . '/migrations')) {
            $this->loadMigrationsFrom($modulePath . "/migrations");
        }

        //khai báo languages
        if (File::exists($modulePath . '/resources/languages')) {
            $this->loadTranslationsFrom($modulePath . "/resources/languages", $module);
            $this->loadJsonTranslationsFrom($modulePath . "/resources/languages");
        }

        //khai báo view
        if (File::exists($modulePath . '/resources/views')) {
            $this->loadViewsFrom($modulePath . "/resources/views", $module);
        }

        //khai báo helpers
        if (File::exists($modulePath . '/helpers')) {
            $helperList = File::allFiles($modulePath . '/helpers');
            if (!empty($helperList)) {
                foreach ($helperList as $helper) {
                    $file = $helper->getPathname();
                    require $file;
                }
            }
        }
    }

    public function register()
    {
        $directories = array_map('basename', File::directories(__DIR__));
        // configs
        if (!empty($directories)) {
            foreach ($directories as $directory) {
                $configPath = __DIR__ . '/' . $directory . '/configs';
                if (File::exists($configPath)) {
                    $configFiles = array_map('basename', File::allFiles($configPath));
                    foreach ($configFiles as $config) {
                        $alias = basename($config, '.php');
                        $this->mergeConfigFrom($configPath . '/' . $config, $alias);
                    }
                }

            }
        }
        // khai báo Middleware

        $middlewares = [
            'demo' => DemoMiddleware::class,
        ];
        if (!empty($middlewares)) {
            foreach ($middlewares as $key => $middleware) {
                $this->app['router']->pushMiddlewareToGroup($key, $middleware);
            }
        }
        //khai bao commands
        $this->commands([
            TestCommand::class
        ]);
    }

}

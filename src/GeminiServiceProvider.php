<?php 

namespace Imtiaz\LaravelGemini;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;



class GeminiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This is where you bind classes to the service container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . "./../config/gemini.php", "gemini");
        $this->app->bind("geminiApi", function () {
            return new \Imtiaz\LaravelGemini\Gemini\GeminiApi();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * This method is called after all the services are registered.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . "./../config/gemini.php" => config_path("gemini.php")
        ], 'config');
    
   
    
        $this->publishes([
            __DIR__.'./Controllers/GeminiController.php' => app_path('Http/Controllers/GeminiController.php'),
        ], 'controllers');
    
        $this->loadRoutesFrom(__DIR__ . "/routes/gemini_route.php");
    }
    
}
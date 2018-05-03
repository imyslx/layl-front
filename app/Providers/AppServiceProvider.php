<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('loadLocalCSS', function($filePath) {
            $path = base_path() . "/public" . $filePath;
            return "<link rel=\"stylesheet\" href=\"" . $filePath . "?date=" . "<?php echo \File::lastModified(\"" . $path . "\") ?>" . "\">";
        });
        Blade::directive('loadLocalJS', function($filePath) {
            $path = base_path() . "/public" . $filePath;
            return "<script src=\"" . $filePath . "?date=" . "<?php echo \File::lastModified(\"" . $path . "\") ?>" . "\"></script>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

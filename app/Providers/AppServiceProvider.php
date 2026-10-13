<?php

namespace App\Providers;

use App\Domains\Reportreview\Models\Review;
use App\Domains\Reportreview\Policies\ReviewPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureViews();
        Gate::policy(Review::class, ReviewPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * ให้ <x-layouts::auth> ชี้ไปที่ resources/views/domains/auth/layouts
     */
    protected function configureViews(): void
    {
        // ไฟล์ Livewire ถูกย้ายเข้า Domains จึงกำหนดที่ค้นหาให้ตรงกัน
        Livewire::addNamespace('pages', viewPath: resource_path('views/domains/auth/pages'));
        Blade::anonymousComponentPath(resource_path('views/domains/auth/pages'), 'pages');
        View::addLocation(resource_path('views/domains/auth'));

        Blade::anonymousComponentPath(
            resource_path('views/domains/auth/layouts'),
            'layouts'
        );
    }
}

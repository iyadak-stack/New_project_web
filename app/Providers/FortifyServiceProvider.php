<?php

namespace App\Providers;

/* @chisel-registration */
use App\Domains\Auth\Actions\Fortify\CreateNewUser;
/* @end-chisel-registration */
use App\Domains\Auth\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
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
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureResponses();
    }

    /**
     * Configure Fortify responses for Login and Registration.
     */
    private function configureResponses(): void
    {
        // เมื่อ Login สำเร็จ ให้ Redirect กลับหน้าหลัก (/)
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                return redirect('/');
            }
        });

        // เมื่อ Register สำเร็จ ให้ Redirect กลับหน้าหลัก (/)
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                return redirect('/');
            }
        });
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        /* @chisel-registration */
        Fortify::createUsersUsing(CreateNewUser::class);
        /* @end-chisel-registration */
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        // ใช้ redirect()->to('/') เพื่อป้องกัน CSRF Session Mismatch (419 Page Expired)
        Fortify::loginView(function () {
            return redirect()->to('/')->with('open_login', true);
        });

        /* @chisel-registration */
        Fortify::registerView(function () {
            return redirect()->to('/')->with('open_register', true);
        });
        /* @end-chisel-registration */

        /* @chisel-email-verification */
        Fortify::verifyEmailView(fn () => view('domains.auth.pages.auth.verify-email'));
        /* @end-chisel-email-verification */

        /* @chisel-2fa */
        Fortify::twoFactorChallengeView(fn () => view('domains.auth.pages.auth.two-factor-challenge'));
        /* @end-chisel-2fa */

        /* @chisel-password-confirmation */
        Fortify::confirmPasswordView(fn () => view('domains.auth.pages.auth.confirm-password'));
        /* @end-chisel-password-confirmation */

        Fortify::resetPasswordView(fn () => view('domains.auth.pages.auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('domains.auth.pages.auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        /* @chisel-passkeys */
        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });
        /* @end-chisel-passkeys */
    }
}
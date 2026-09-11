<?php

namespace App\Providers;

use App\Models\Scopes\OfficeScope;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

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
        Carbon::setLocale(config('app.locale'));

        // Daftar pengguna untuk pengalih peran demo di navbar, dikelompokkan per kantor.
        ViewFacade::composer('layout.navbar', function (View $view): void {
            $demoUsers = config('demo.enabled')
                ? User::withoutGlobalScope(OfficeScope::class)
                    ->with(['office', 'roles'])
                    ->where('is_active', true)
                    ->orderBy('office_id')
                    ->orderBy('id')
                    ->get()
                    ->groupBy(fn (User $user) => $user->office->code.' — '.$user->office->name)
                : new Collection;

            $view->with('demoUsers', $demoUsers);
        });
    }
}

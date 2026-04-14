<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Illuminate\Support\Facades\View::composer(['layouts.header', 'components.stock-alert-modal'], function ($view) {
            $view->with('stockAlerts', collect([]))
                 ->with('stockAlertAutoOpen', false);
        });

        \Illuminate\Support\Facades\View::composer('layouts.sidebar', function ($view) {
            $sidebarMenus = [
                (object)[
                    'title' => 'Dashboard',
                    'route' => 'dashboard',
                    'icon' => 'fa-solid fa-gauge-high',
                    'children' => collect([])
                ],
                (object)[
                    'title' => 'Transaksi',
                    'route' => '#',
                    'icon' => 'fa-solid fa-right-left',
                    'children' => collect([
                        (object)['title' => 'Tracking Produksi', 'route' => 'tracking.index']
                    ])
                ],
                (object)[
                    'title' => 'Master Data',
                    'route' => '#',
                    'icon' => 'fa-solid fa-database',
                    'children' => collect([
                        (object)['title' => 'Data Event (PO)', 'route' => 'events.index'],
                        (object)['title' => 'Master Event Project', 'route' => 'master.events.index'],
                        (object)['title' => 'Routing per Part ID', 'route' => 'master.routings.index'],
                        (object)['title' => 'Master Proses', 'route' => 'master.processes.index'],
                        (object)['title' => 'Master Department', 'route' => 'master.departments.index'],
                        (object)['title' => 'Master Poin QA', 'route' => 'master.checkpoints.index'],
                        (object)['title' => 'Master Tujuan Kirim', 'route' => 'master.delivery-targets.index'],
                        (object)['title' => 'Master Kategori Internal', 'route' => 'master.internal-categories.index'],
                        (object)['title' => 'Master Mapping Customer', 'route' => 'master.customer-categories.index'],
                        (object)['title' => 'Master Grup Pengiriman', 'route' => 'master.delivery-groups.index'],
                    ])
                ],
            ];

            $view->with('sidebarMenus', collect($sidebarMenus))
                 ->with('userRoleCode', 'admin');
        });
    }
}

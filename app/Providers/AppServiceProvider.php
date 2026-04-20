<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL; // Tambahan penting
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Menu;

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
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }

        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer(['layouts.header', 'components.stock-alert-modal'], function ($view) {
            $view->with('stockAlerts', collect([]))
                ->with('stockAlertAutoOpen', false);
        });

        View::composer('layouts.sidebar', function ($view) {
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
                        (object)['title' => 'Global Tracking', 'route' => 'tracking.index'],
                        (object)['title' => 'Setup Routing Produksi', 'route' => 'tracking.setup'],
                        (object)['title' => 'Proses Produksi', 'route' => 'tracking.production'],
                        (object)['title' => 'Pemeriksaan Kualitas (QC)', 'route' => 'tracking.qc'],
                        (object)['title' => 'Persetujuan Management', 'route' => 'tracking.mgm'],
                        (object)['title' => 'Stok Barang Jadi', 'route' => 'tracking.stock'],
                        (object)['title' => 'Riwayat Kirim', 'route' => 'tracking.history'],
                    ])
                ],
                (object)[
                    'title' => 'Master Data',
                    'route' => '#',
                    'icon' => 'fa-solid fa-database',
                    'children' => collect([
                        (object)['title' => 'Master Kategori Internal', 'route' => 'master.internal-categories.index'],
                        (object)['title' => 'Master Mapping Customer', 'route' => 'master.customer-categories.index'],
                        (object)['title' => 'Master Department', 'route' => 'master.departments.index'],
                        (object)['title' => 'Master Proses', 'route' => 'master.processes.index'],
                        (object)['title' => 'Routing per Part ID', 'route' => 'master.routings.index'],
                        (object)['title' => 'Master Poin QA', 'route' => 'master.checkpoints.index'],
                        (object)['title' => 'Master Checksheet Part', 'route' => 'master.checksheets.index'],
                        (object)['title' => 'Master Grup Pengiriman', 'route' => 'master.delivery-groups.index'],
                        (object)['title' => 'Master Tujuan Kirim', 'route' => 'master.delivery-targets.index'],
                        (object)['title' => 'Master Event Project', 'route' => 'master.events.index'],
                        (object)['title' => 'Data Event (PO)', 'route' => 'events.index'],
                    ])
                ],
            ];

            $view->with('sidebarMenus', collect($sidebarMenus))
                ->with('userRoleCode', 'admin');
        });
    }
}

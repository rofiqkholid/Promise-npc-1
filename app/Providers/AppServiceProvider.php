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
        \Illuminate\Support\Facades\View::composer(['layouts.header', 'components.stock-alert-modal', 'components.ecn-alert-modal'], function ($view) {
            // Hitung part aktif yang memiliki ECN update
            $ecnQuery = \App\Models\NpcPart::with(['purchaseOrder.event.customerCategory', 'product'])
                ->whereNotIn('status', ['FINISHED', 'CLOSED'])
                ->whereNotNull('part_revision_id')
                ->whereHas('product.docPackage', function ($query) {
                    $query->whereColumn('doc_packages.current_revision_id', '!=', 'npc_parts.part_revision_id');
                });
                
            $ecnNotificationCount = $ecnQuery->count();
            $ecnUpdatedParts = $ecnQuery->latest()->take(10)->get();

            $view->with('stockAlerts', collect([]))
                 ->with('stockAlertAutoOpen', false)
                 ->with('ecnNotificationCount', $ecnNotificationCount)
                 ->with('ecnUpdatedParts', $ecnUpdatedParts);
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
                        (object)['title' => 'Global Tracking', 'route' => 'tracking.index'],
                        (object)['title' => 'Setup Routing Produksi', 'route' => 'tracking.setup'],
                        (object)['title' => 'Proses Produksi', 'route' => 'tracking.production'],
                        (object)['title' => 'Pemeriksaan Kualitas (QC)', 'route' => 'tracking.qc'],
                        (object)['title' => 'Management Check', 'route' => 'tracking.mgm'],
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
                        (object)['title' => 'Master Poin QE', 'route' => 'master.checkpoints.index'],
                        (object)['title' => 'Master Checksheet Part', 'route' => 'master.checksheets.index'],
                        (object)['title' => 'Master Grup Pengiriman', 'route' => 'master.delivery-groups.index'],
                        (object)['title' => 'Master Tujuan Kirim', 'route' => 'master.delivery-targets.index'],
                        (object)['title' => 'Data Event (PO)', 'route' => 'events.index'],
                    ])
                ],
            ];

            $view->with('sidebarMenus', collect($sidebarMenus))
                 ->with('userRoleCode', 'admin');
        });
    }
}

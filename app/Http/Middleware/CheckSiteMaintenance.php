<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CheckSiteMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Schema::hasTable('site_settings')) {
            return $next($request);
        }

        if (! SiteSetting::maintenanceEnabled()) {
            return $next($request);
        }

        if ($request->user()?->is_admin || $this->isAllowedDuringMaintenance($request)) {
            return $next($request);
        }

        return response()
            ->view('errors.maintenance', [
                'message' => SiteSetting::getValue(
                    'maintenance_message',
                    'Le site est temporairement en maintenance. Merci de revenir dans quelques instants.'
                ),
            ], 503);
    }

    private function isAllowedDuringMaintenance(Request $request): bool
    {
        return $request->is(
            'admin',
            'admin/*'
        );
    }
}

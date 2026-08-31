<?php

namespace Packages\Core\Src\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\Core\Src\Http\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index(Request $request)
    {
        // Delegate to Report dashboard when packages/Report is installed.
        // Keeps Core decoupled from Report so the dashboard degrades gracefully
        // when Report isn't loaded (early bootstraps, alternative deployments).
        $reportController = '\\Packages\\Report\\Src\\Http\\Controllers\\Admin\\DashboardController';
        if (class_exists($reportController)) {
            return app($reportController)->index($request);
        }

        return view('core::admin.dashboard');
    }
}

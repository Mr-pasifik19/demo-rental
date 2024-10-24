<?php

namespace App\Http\Middleware;

use App\Models\Asset;
use App\Models\MovementsModel;
use Auth;
use Closure;

class AssetCountForSidebar
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $total_rtd_sidebar = Asset::RTD()->count();
            view()->share('total_rtd_sidebar', $total_rtd_sidebar);
        } catch (\Exception $e) {
            \Log::debug($e);
        }

        try {
            $total_deployed_sidebar = Asset::Deployed()->count();
            view()->share('total_deployed_sidebar', $total_deployed_sidebar);
        } catch (\Exception $e) {
            \Log::debug($e);
        }

        try {
            $total_archived_sidebar = Asset::Archived()->count();
            view()->share('total_archived_sidebar', $total_archived_sidebar);
        } catch (\Exception $e) {
            \Log::debug($e);
        }

        try {
            $total_pending_sidebar = Asset::Pending()->count();
            view()->share('total_pending_sidebar', $total_pending_sidebar);
        } catch (\Exception $e) {
            \Log::debug($e);
        }

        try {
            $total_undeployable_sidebar = Asset::Undeployable()->count();
            view()->share('total_undeployable_sidebar', $total_undeployable_sidebar);
        } catch (\Exception $e) {
            \Log::debug($e);
        }

        try {
            $total_byod_sidebar = Asset::where('byod', '=', '1')->count();
            view()->share('total_byod_sidebar', $total_byod_sidebar);
        } catch (\Exception $e) {
            \Log::debug($e);
        }

        try {
            $total_open_movement = MovementsModel::where('is_return', '=', 0)->count();
            view()->share('total_open_movement', $total_open_movement);
        } catch (\Throwable $e) {
            \Log::debug($e);
        }
        try {
            $total_return_movement = MovementsModel::where('is_return', '=', 1)->count();
            view()->share('total_return_movement', $total_return_movement);
        } catch (\Throwable $e) {
            \Log::debug($e);
        }

        return $next($request);
    }
}

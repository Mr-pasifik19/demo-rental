<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Alarm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;


/**
 * This controller handles all actions related to the Admin Dashboard
 * for the Snipe-IT Asset Management application.
 *
 * @author A. Gianotto <snipe@snipe.net>
 * @version v1.0
 */
class DashboardController extends Controller
{
    /**
     * Check authorization and display admin dashboard, otherwise display
     * the user's checked-out assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return View
     */
    public function index()
    {
        // Show the page
        if (Auth::user()->hasAccess('admin')) {
            $asset_stats = null;

            $counts['asset'] = \App\Models\Asset::count();
            $counts['accessory'] = \App\Models\Accessory::count();
            $counts['movement'] = \App\Models\MovementsModel::count();
            $counts['consumable'] = \App\Models\Consumable::count();
            $counts['component'] = \App\Models\Component::count();
            $counts['user'] = \App\Models\Company::scopeCompanyables(Auth::user())->count();
            $counts['project'] = \App\Models\ProjectMovement::count();
            $counts['grand_total'] = $counts['asset'] + $counts['accessory'] + $counts['movement'] + $counts['consumable'] + $counts['project'];
            $alarms = Alarm::orderBy('due_date', 'desc')->get();

            if ((!file_exists(storage_path() . '/oauth-private.key')) || (!file_exists(storage_path() . '/oauth-public.key'))) {
                Artisan::call('migrate', ['--force' => true]);
                \Artisan::call('passport:install');
            }

            $assets = Asset::with(['location', 'assetstatus'])
                ->whereRaw("SUBSTRING(asset_tag, 1, 2) = '20'")
                ->get();
            return view('dashboard', compact('assets', 'alarms'))->with('asset_stats', $asset_stats)->with('counts', $counts);
        } else {
            // Redirect to the profile page
            return redirect()->intended('account/view-assets');
        }
    }
}

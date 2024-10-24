<?php

namespace App\Http\Controllers\Movement;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetMovement;
use App\Models\BranchCompany;
use App\Models\FormatDate;
use App\Models\Location;
use App\Models\MovementLogs;
use App\Models\MovementsModel;
use App\Models\ProjectMovement;
use App\Models\Statuslabel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use View;

/**
 * This class controls all actions related to assets for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 * @author [A. Gianotto] [<snipe@snipe.net>]
 */
class MovementController extends Controller
{

    protected $qrCodeDimensions = ['height' => 3.5, 'width' => 3.5];
    protected $barCodeDimensions = ['height' => 2, 'width' => 22];
    protected $inMovement = false; // category movement for OPEN MOVEMENT
    protected $returnMovement = true; // category movement for MOVEMENT RETURN

    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the assets listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see AssetController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     * @param Request $request
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $movement = MovementsModel::leftJoin(
            'locations',
            'locations.id',
            '=',
            'movements.location_id'
        )->leftJoin(
            'project_movements',
            'project_movements.id',
            '=',
            'movements.project_id'
        )->leftJoin(
            'branch_companies',
            'branch_companies.id',
            '=',
            'movements.company_id'
        );

        // Add search functionality
        $search = $request->input('searchMovement');
        $selectCategory = $request->input('selectCategory');

        if (!empty($search)) {
            switch ($selectCategory) {
                case 'movementNumber':
                    $movement->where('movements.movement_number', 'LIKE', "%$search%");
                    break;
                case 'projectNumber':
                    $movement->where('project_movements.project_number', 'LIKE', "%$search%");
                    break;
                case 'projectName':
                    $movement->where('project_movements.project_name', 'LIKE', "%$search%");
                    break;
                    // Add more cases for additional search criteria as needed
                default:
                    // Handle the default case (e.g., show an error message, ignore, or handle differently)
                    break;
            }
        }

        if ($status === 'movementReturn') {
            $movement->select(
                'movements.id as id',
                'project_movements.project_number as project_number',
                'project_movements.project_name as project_name',
                'movements.project_id as project_id',
                DB::raw('GROUP_CONCAT(movements.movement_number) as number_movement'),
                DB::raw('GROUP_CONCAT(movements.datetime_specific) as datetime_specific'),
                DB::raw('GROUP_CONCAT(locations.name) as location_movement'),
                DB::raw('GROUP_CONCAT(locations.id) as location_id'),
                DB::raw('GROUP_CONCAT(movements.id) as movement_ids'),
                DB::raw('GROUP_CONCAT(branch_companies.company_name) as company_name'),
                DB::raw('GROUP_CONCAT(branch_companies.id) as company_id'),
                DB::raw('GROUP_CONCAT(movements.to) as receiver'),
                DB::raw('GROUP_CONCAT(movements.person_charge) as person_charge'),
                DB::raw('GROUP_CONCAT(movements.notes) as notes'),
                DB::raw('GROUP_CONCAT(movements.is_return) as is_return'),
            )->where('is_return', $this->returnMovement)->groupBy('project_movements.id')->get();
        } elseif ($status === 'openMovement') {
            $movement->select(
                'movements.id as id',
                'project_movements.project_number as project_number',
                'project_movements.project_name as project_name',
                'movements.project_id as project_id',
                DB::raw('GROUP_CONCAT(movements.movement_number) as number_movement'),
                DB::raw('GROUP_CONCAT(movements.datetime_specific) as datetime_specific'),
                DB::raw('GROUP_CONCAT(locations.name) as location_movement'),
                DB::raw('GROUP_CONCAT(locations.id) as location_id'),
                DB::raw('GROUP_CONCAT(movements.id) as movement_ids'),
                DB::raw('GROUP_CONCAT(branch_companies.company_name) as company_name'),
                DB::raw('GROUP_CONCAT(branch_companies.id) as company_id'),
                DB::raw('GROUP_CONCAT(movements.to) as receiver'),
                DB::raw('GROUP_CONCAT(movements.person_charge) as person_charge'),
                DB::raw('GROUP_CONCAT(movements.notes) as notes'),
                DB::raw('GROUP_CONCAT(movements.is_return) as is_return'),
            )->where('is_return', $this->inMovement)->groupBy('project_movements.id')->get();
        } else {
            $movement->select(
                'movements.id as id',
                'project_movements.project_number as project_number',
                'project_movements.project_name as project_name',
                'movements.project_id as project_id',
                DB::raw('GROUP_CONCAT(movements.movement_number) as number_movement'),
                DB::raw('GROUP_CONCAT(locations.name) as location_movement'),
                DB::raw('GROUP_CONCAT(movements.id) as movement_ids'),
                DB::raw('GROUP_CONCAT(branch_companies.company_name) as company_name'),
                DB::raw('GROUP_CONCAT(branch_companies.id) as company_id'),
                DB::raw('GROUP_CONCAT(movements.is_return) as is_return'),
            )->groupBy('project_movements.id')->get();
        }

        $movementData = $movement->latest('movements.created_at')->paginate(10);


        $movementIds = $movementData->pluck('movement_ids')->implode(',');

        $statuses = DB::table('movements')
            ->whereIn('movements.id', explode(',', $movementIds))
            ->leftJoin('status_labels', 'status_labels.id', '=', 'movements.movement_status_id')
            ->select('movements.id as movement_id', 'status_labels.name as status')
            ->get();

        $cateogoryMovement = DB::table('movements')
            ->whereIn('movements.id', explode(',', $movementIds))
            ->select('movements.id as movement_id', 'movements.is_return as is_return')
            ->get();

        $locations = DB::table('movements')
            ->whereIn('movements.id', explode(',', $movementIds))
            ->leftJoin('locations', 'locations.id', '=', 'movements.location_id')
            ->select('movements.id as movement_id', 'locations.name as location')
            ->get();

        $branchCompany =
            DB::table('movements')
            ->whereIn('movements.id', explode(',', $movementIds))
            ->leftJoin('branch_companies', 'branch_companies.id', '=', 'movements.company_id')
            ->select('movements.id as movement_id', 'branch_companies.company_name as company', 'branch_companies.id as id_company')
            ->get();

        $statusMap = $statuses->pluck('status', 'movement_id')->toArray();
        $locationMap = $locations->pluck('location', 'movement_id')->toArray();
        $companyMap = $branchCompany->pluck('company', 'movement_id')->toArray();
        $companyIdsMap = $branchCompany->pluck('id_company', 'movement_id')->toArray();
        $categoryMovementMap = $cateogoryMovement->pluck('is_return', 'movement_id')->toArray();

        return view('movement/index', compact('movementData', 'statusMap', 'locationMap', 'companyMap', 'companyIdsMap', 'categoryMovementMap', 'search'));
    }



    /**
     * Returns a view that presents a form to create a new asset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param Request $request
     * @return View
     * @internal param int $model_id
     */
    public function create(Request $request)
    {

        $users = User::all();
        $projectMovement = ProjectMovement::latest()->get();
        $company = BranchCompany::latest()->get();

        $dateNow = date("dmY");
        $latestMovement = MovementsModel::whereRaw("SUBSTRING(movement_number, 1, 8) = ?", [$dateNow])
            ->orderBy('movement_number', 'desc')
            ->first();

        if ($latestMovement) {
            // Jika movement number sudah ada, ambil angka terbesar dan tambahkan 1
            $latestNumber = (int) substr($latestMovement->movement_number, -2);
            $movementNumber = $dateNow . '-' . str_pad($latestNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            // Jika belum ada data untuk tanggal hari ini, gunakan angka 01
            $movementNumber = $dateNow . '-01';
        }

        return view('movement.edit', compact('projectMovement', 'company', 'users', 'movementNumber'))
            ->with('statuslabel_list', Helper::statusLabelList())
            ->with('item', new Asset)
            ->with('statuslabel_types', Helper::statusTypeList());
    }

    /**
     * Validate and process new asset form data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return Redirect
     */
    public function store(ImageUploadRequest $request)
    {
    }

    public function getOptionCookie(Request $request)
    {
        $value = $request->cookie('optional_info');
        echo $value;
        return $value;
    }

    /**
     * Returns a view that presents a form to edit an existing asset.
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return View
     */
    public function edit($assetId = null)
    {
        if (!$item = Asset::find($assetId)) {
            // Redirect to the asset management page with error
            return redirect()->route('movement.index')->with('error', trans('admin/movement/message.does_not_exist'));
        }
        //Handles company checks and permissions.
        $this->authorize($item);

        return view('movement/edit', compact('item'))
            ->with('statuslabel_list', Helper::statusLabelList())
            ->with('statuslabel_types', Helper::statusTypeList());
    }


    /**
     * Returns a view that presents information about an asset for detail view.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return View
     */
    public function show($id)
    {
        ///
        $movement = MovementsModel::with(['project', 'location', 'status', 'company'])
            ->where('movements.id', $id)->first();

        if (!$movement) {
            return redirect()->route('movement.index')
                ->with('error', trans('admin/movement/message.does_not_exist'));
        }

        $assets = AssetMovement::leftJoin('movements', 'movements.id', '=', 'asset_movements.movement_id')
            ->leftJoin('assets', 'assets.id', '=', 'asset_movements.asset_id')
            ->leftJoin('locations', 'locations.id', '=', 'assets.location_id')
            ->leftJoin('status_labels', 'status_labels.id', '=', 'assets.status_id')
            ->select(
                'asset_movements.id as id',
                'asset_movements.is_return as return_asset',
                'assets.id as id_asset',
                'assets.name as asset_name',
                'assets.asset_tag as asset_tag',
                'locations.name as location',
                'assets.last_checkout as last_checkout',
                'assets.notes as notes',
                'status_labels.name as status',
            )->where('movements.id', $movement->id)->get();

        $movementLog = MovementLogs::where('movement_id', $id)->get();
        $assetMovement = AssetMovement::all();
        $isShowMovement = true;
        return view('movement.view', compact('movement', 'assets', 'isShowMovement', 'movementLog', 'assetMovement'));
    }

    /**
     * Show Project Movement By ID
     */

    public function showProject($id)
    {
        $project = ProjectMovement::find($id);
        if (!$project) {
            return redirect()->route('movement.index')
                ->with('error', trans('admin/movement/message.project_movement.notFound'));
        }
        $movement = MovementsModel::with(['project', 'location', 'status', 'company'])->where('movements.project_id', $id)->get();
        $isShowMovement = false;
        return view('movement.view', compact('movement', 'project', 'isShowMovement'));
    }

    /**
     * Validate and process asset edit form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return Redirect
     */
    public function update(ImageUploadRequest $request, $assetId = null)
    {
    }

    /**
     * Delete a given asset (mark as deleted).
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return Redirect
     */
    public function destroy($id)
    {
        try {
            $movement = MovementsModel::find($id);
            if (!$movement) {
                return redirect()->route('project-movement.index')
                    ->with('error', trans('admin/movement/message.delete.error'));
            }
            $movementLog = MovementLogs::where('movement_id', $movement->id)->get();
            foreach ($movementLog as $log) {
                $log->delete();
            }
            $assetMovement = AssetMovement::where('movement_id', $movement->id)->get();

            foreach ($assetMovement as $asset) {
                $assetsData = Asset::find($asset->asset_id);
                $assetsData->assigned_to = null;
                $assetsData->assigned_type = null;
                $assetsData->update();
                //

                Actionlog::firstOrCreate([
                    'item_id' => $asset->asset_id,
                    'item_type' => Asset::class,
                    'user_id' => Auth::user()->id,
                    'target_id' => $movement->id,
                    'target_type' => MovementsModel::class,
                    'created_at' => date('Y-m-d H:i:s'),
                    'action_type' => 'deleted asset movement',
                ]);
                $asset->delete();
            }



            $movement->delete();

            return redirect()->route('movement.index')
                ->with('success', trans('admin/movement/message.delete.success'));
        } catch (QueryException $e) {
            // Handle database query exceptions (e.g., foreign key constraints).
            return redirect()->back()->with('error', trans('admin/movement/message.delete.error'));
        } catch (\Exception $e) {
            // Handle other unexpected exceptions.
            return redirect()->back()->with(
                'error',
                trans('admin/movement/message.delete.error')
            );
        }
    }
    ///
    /**
     * Change status movements
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return Redirect
     */
    public function changeStatus(Request $request)
    {
        try {
            $movement = MovementsModel::find($request->id);
            $executionType = $request->input('execution_type');
            $specificDate = $request->input('specific_date_text');
            $statusLabel = Statuslabel::find($request->status_movement);


            if (!$movement) {
                return back()->with('error', trans('admin/movement/message.change_status.error'));
            }


            $movement->movement_status_id = $statusLabel->id;
            $movement->save();

            if ($movement->is_return === 0) {
                $assetMovement = AssetMovement::where('movement_id', $movement->id)->get();
                foreach ($assetMovement as $asset) {
                    $assets = Asset::where('id', $asset->asset_id)->first();
                    $assets->status_id = $statusLabel->id;
                    $assets->update();
                }
            }


            switch ($executionType) {
                case 'current_date_time':
                    $movement->datetime_specific = Carbon::now();
                    MovementLogs::create([
                        'movement_id' => $request->id,
                        'activity' => 'Change Status Movement into ' . $statusLabel->name,
                    ]);
                    break;
                case 'specific_date':
                    $movement->datetime_specific = $specificDate;
                    MovementLogs::create([
                        'movement_id' => $request->id,
                        'activity' => 'Change Status Movement into ' . $statusLabel->name . ' (Manual datetime : ' . $specificDate . '0)',
                        'created_at' => $specificDate
                    ]);
                    break;
                default:
                    break;
            }

            return back()->with('success', trans('admin/movement/message.change_status.success'));
        } catch (QueryException $e) {
            // Handle database query exceptions (e.g., foreign key constraints).
            return back()->with('error', trans('admin/movement/message.change_status.error'));
        } catch (\Exception $e) {
            // Handle other unexpected exceptions.
            return back()->with('error', trans('admin/movement/message.change_status.error'));
        }
    }


    /**
     * Save New MoveMent
     */
    public function saveMovement(Request $request)
    {
        if ($request->status === "print") {

            if ($request->to_receiver === 'manual' && $request->person_id === 'manual') {
                $assetIdList = $request->selected_assets;

                // Split the comma-separated string into an array
                $assetIdArray = explode(',', $assetIdList);

                // Convert each element in the array to integer values
                $assetIdArray = array_map('intval', $assetIdArray);

                // Use the array in the whereIn method
                $listAssets = Asset::whereIn('id', $assetIdArray)->get();

                $company = $request->company_id;
                $companyData = BranchCompany::find(intval($company[0]));
                $project = ProjectMovement::find($request->project_id);
                $locationData = Location::find($request->project_location);

                $to = $request->receiver_manual;
                $movementNumber = $request->movement_numbers;
                $from = $companyData->company_name;
                $projectNumber = $project->project_number;
                $address = $companyData->address;
                $projectName = $project->project_name;
                $location = $locationData->name;
                $notes = $request->notes;
                $person = $request->person_charge_manual;
                $phone = $request->phonePersonInCharge;
                $recipient = User::find($request->contact_recipient);

                $chooseDate = null; // Initialize the variable to avoid "Undefined variable" error

                if ($request->choose_date === 'current_date_time') {
                    $chooseDate = date($request->format_date);
                } elseif ($request->choose_date === 'specific_date') {
                    $specificDate = new DateTime($request->specific_date_text);
                    $chooseDate = $specificDate->format($request->format_date);
                }
                return redirect()->route('movement.invoice')
                    ->with('chooseDate', $chooseDate)
                    ->with('to', $to)
                    ->with(
                        'from',
                        $from
                    )
                    ->with('projectNumber', $projectNumber)
                    ->with('location', $location)
                    ->with(
                        'notes',
                        $notes
                    )
                    ->with('recipient', $recipient)
                    ->with('person', $person)
                    ->with('phone', $phone)
                    ->with('listAssets', $listAssets)
                    ->with('address', $address)
                    ->with('movementNumber', $movementNumber)
                    ->with('projectName', $projectName);
            } elseif ($request->to_receiver === 'manual' && $request->person_id != 'manual') {
                $assetIdList = $request->selected_assets;
                // Split the comma-separated string into an array
                $assetIdArray = explode(',', $assetIdList);

                // Convert each element in the array to integer values
                $assetIdArray = array_map('intval', $assetIdArray);

                // Use the array in the whereIn method
                $listAssets = Asset::whereIn('id', $assetIdArray)->get();

                $company = $request->company_id;
                $companyData = BranchCompany::find(intval($company[0]));
                $project = ProjectMovement::find($request->project_id);
                $locationData = Location::find($request->project_location);

                $to = $request->receiver_manual;
                $movementNumber = $request->movement_numbers;
                $from = $companyData->company_name;
                $projectNumber = $project->project_number;
                $address = $companyData->address;
                $projectName = $project->project_name;
                $location = $locationData->name;
                $notes = $request->notes;
                $person = User::find($request->person_id);
                $recipient = User::find($request->contact_recipient);
                $chooseDate = null; // Initialize the variable to avoid "Undefined variable" error

                if ($request->choose_date === 'current_date_time') {
                    $chooseDate = date($request->format_date);
                } elseif ($request->choose_date === 'specific_date') {
                    $specificDate = new DateTime($request->specific_date_text);
                    $chooseDate = $specificDate->format($request->format_date);
                }

                return redirect()->route('movement.invoice')
                    ->with('chooseDate', $chooseDate)
                    ->with('to', $to)
                    ->with(
                        'from',
                        $from
                    )
                    ->with('projectNumber', $projectNumber)
                    ->with('location', $location)
                    ->with(
                        'notes',
                        $notes
                    )
                    ->with('recipient', $recipient)
                    ->with('person', $person->first_name . ' ' . $person->last_name)
                    ->with('phone', $person->phone)
                    ->with('listAssets', $listAssets)
                    ->with('movementNumber', $movementNumber)
                    ->with('address', $address)
                    ->with('projectName', $projectName);
            } elseif ($request->to_receiver != 'manual' && $request->person_id === 'manual') {
                $assetIdList = $request->selected_assets;
                // Split the comma-separated string into an array
                $assetIdArray = explode(',', $assetIdList);

                // Convert each element in the array to integer values
                $assetIdArray = array_map('intval', $assetIdArray);

                // Use the array in the whereIn method
                $listAssets = Asset::whereIn('id', $assetIdArray)->get();

                $company = $request->company_id;
                $companyData = BranchCompany::find(intval($company[0]));
                $project = ProjectMovement::find($request->project_id);
                $locationData = Location::find($request->project_location);

                $to = $request->to_receiver;
                $movementNumber = $request->movement_numbers;
                $from = $companyData->company_name;
                $projectNumber = $project->project_number;
                $address = $companyData->address;
                $projectName = $project->project_name;
                $location = $locationData->name;
                $notes = $request->notes;
                $person = $request->person_charge_manual;
                $phone = $request->phonePersonInCharge;
                $recipient = User::find($request->contact_recipient);

                $chooseDate = null; // Initialize the variable to avoid "Undefined variable" error

                if ($request->choose_date === 'current_date_time') {
                    $chooseDate = date($request->format_date);
                } elseif ($request->choose_date === 'specific_date') {
                    $specificDate = new DateTime($request->specific_date_text);
                    $chooseDate = $specificDate->format($request->format_date);
                }


                return redirect()->route('movement.invoice')
                    ->with('chooseDate', $chooseDate)
                    ->with('to', $to)
                    ->with('from', $from)
                    ->with('projectNumber', $projectNumber)
                    ->with('location', $location)
                    ->with(
                        'notes',
                        $notes
                    )
                    ->with(
                        'person',
                        $person
                    )
                    ->with('phone', $phone)
                    ->with('recipient', $recipient)
                    ->with('listAssets', $listAssets)
                    ->with('address', $address)
                    ->with('movementNumber', $movementNumber)
                    ->with('projectName', $projectName);
            } else {
                $assetIdList = $request->selected_assets;
                // Split the comma-separated string into an array
                $assetIdArray = explode(',', $assetIdList);

                // Convert each element in the array to integer values
                $assetIdArray = array_map('intval', $assetIdArray);

                // Use the array in the whereIn method
                $listAssets = Asset::whereIn('id', $assetIdArray)->get();

                $company = $request->company_id;
                $companyData = BranchCompany::find(intval($company[0]));
                $project = ProjectMovement::find($request->project_id);
                $locationData = Location::find($request->project_location);

                $to = $request->to_receiver;
                $movementNumber = $request->movement_numbers;
                $from = $companyData->company_name;
                $projectNumber = $project->project_number;
                $address = $companyData->address;
                $projectName = $project->project_name;
                $location = $locationData->name;
                $notes = $request->notes;
                $person = User::find($request->person_id);
                $recipient = User::find($request->contact_recipient);
                $chooseDate = null; // Initialize the variable to avoid "Undefined variable" error

                if ($request->choose_date === 'current_date_time') {
                    $chooseDate = date($request->format_date);
                } elseif ($request->choose_date === 'specific_date') {
                    $specificDate = new DateTime($request->specific_date_text);
                    $chooseDate = $specificDate->format($request->format_date);
                }

                return redirect()->route('movement.invoice')
                    ->with('chooseDate', $chooseDate)
                    ->with(
                        'to',
                        $to
                    )
                    ->with('from', $from)
                    ->with('recipient', $recipient)
                    ->with('projectNumber', $projectNumber)
                    ->with('location', $location)
                    ->with('notes', $notes)
                    ->with('person', $person->first_name . ' ' . $person->last_name)
                    ->with('phone', $person->phone)
                    ->with('listAssets', $listAssets)
                    ->with('address', $address)
                    ->with('movementNumber', $movementNumber)
                    ->with('projectName', $projectName);
            }
        } else {
            try {
                DB::beginTransaction();

                $company = explode('+', $request->company_id);
                $movement_number = '';
                $checkMovementNumber = MovementsModel::where('movement_number', $request->movement_numbers)->get();
                $statusLabel = Statuslabel::find($request->status_movement);
                if (count($checkMovementNumber) > 0) {
                    $dateNow = date("dmY");
                    $latestMovement = MovementsModel::whereRaw("SUBSTRING(movement_number, 1, 8) = ?", [$dateNow])
                        ->orderBy('movement_number', 'desc')
                        ->first();
                    if ($latestMovement) {
                        // Jika movement number sudah ada, ambil angka terbesar dan tambahkan 1
                        $latestNumber = (int) substr($latestMovement->movement_number, -2);
                        $movementsNumber = $dateNow . '-' . str_pad($latestNumber + 1, 2, '0', STR_PAD_LEFT);
                    } else {
                        // Jika belum ada data untuk tanggal hari ini, gunakan angka 01
                        $movementsNumber = $dateNow . '-01';
                    }
                    $movement_number = $movementsNumber;
                } else {
                    $movement_number = $request->movement_numbers;
                }



                if ($request->to_receiver === 'manual' && $request->person_id === 'manual') {
                    $newMovement = MovementsModel::create([
                        'project_id' => $request->project_id,
                        'movement_number' => $movement_number,
                        'location_id' => $request->project_location,
                        'company_id' => $company[0],
                        'movement_status_id' => $statusLabel->id,
                        'to' => $request->receiver_manual,
                        'person_charge' => $request->person_charge_manual,
                        'notes' => $request->notes,
                        'is_return' => $this->inMovement,
                        'phone_person_in_charge' => $request->phonePersonInCharge,
                        'contact_recipient' => $request->contact_recipient
                    ]);
                } elseif ($request->to_receiver === 'manual' && $request->person_id != 'manual') {
                    $newMovement = MovementsModel::create([
                        'project_id' => $request->project_id,
                        'movement_number' => $movement_number,
                        'location_id' => $request->project_location,
                        'company_id' => $company[0],
                        'movement_status_id' => $statusLabel->id,
                        'to' => $request->receiver_manual,
                        'person_charge' => $request->person_id,
                        'notes' => $request->notes,
                        'is_return' => $this->inMovement,
                        'contact_recipient' => $request->contact_recipient
                    ]);
                } elseif ($request->to_receiver != 'manual' && $request->person_id === 'manual') {
                    $newMovement = MovementsModel::create([
                        'project_id' => $request->project_id,
                        'movement_number' => $movement_number,
                        'location_id' => $request->project_location,
                        'company_id' => $company[0],
                        'movement_status_id' => $statusLabel->id,
                        'to' => $request->to_receiver,
                        'person_charge' => $request->person_charge_manual,
                        'notes' => $request->notes,
                        'is_return' => $this->inMovement,
                        'phone_person_in_charge' => $request->phonePersonInCharge,
                        'contact_recipient' => $request->contact_recipient
                    ]);
                } else {
                    $newMovement = MovementsModel::create([
                        'project_id' => $request->project_id,
                        'movement_number' => $movement_number,
                        'location_id' => $request->project_location,
                        'company_id' => $company[0],
                        'movement_status_id' => $statusLabel->id,
                        'to' => $request->to_receiver,
                        'person_charge' => $request->person_id,
                        'notes' => $request->notes,
                        'is_return' => $this->inMovement,
                        'contact_recipient' => $request->contact_recipient
                    ]);
                }

                //

                foreach ($request->selected_assets as $asset) {
                    $assets = Asset::where('id', $asset)->first();
                    $assets->assigned_to = $newMovement->id;
                    $assets->last_checkout = Carbon::now();
                    $assets->assigned_type = MovementsModel::class;
                    $assets->checkout_counter++;
                    $assets->status_id = $statusLabel->id;
                    $assets->update();
                    //
                    AssetMovement::create([
                        'asset_id' => $asset,
                        'movement_id' => $newMovement->id
                    ]);
                    //
                    Actionlog::firstOrCreate([
                        'item_id' => $asset,
                        'item_type' => Asset::class,
                        'user_id' => Auth::user()->id,
                        'target_id' => $newMovement->id,
                        'target_type' => MovementsModel::class,
                        'created_at' =>  Carbon::now(),
                        'action_type' => 'checkout movement',
                    ]);
                }
                MovementLogs::create([
                    'movement_id' => $newMovement->id,
                    'activity' => 'Create New Movement -> ' . $statusLabel->name
                ]);

                DB::commit();

                ///
                // return Redirect::to(url('movement?status=openMovement'))
                //     ->with('success', trans('admin/movement/message.create.success'));
                return response()->json(Helper::formatStandardApiResponse('success', $newMovement, 'Movement saved'));
            } catch (ValidationException $e) {
                DB::rollBack();
                return response()->json(Helper::formatStandardApiResponse('error', $e->errors()));
            } catch (QueryException $e) {
                DB::rollBack();
                // Handle database query exceptions (e.g., foreign key constraints).
                return response()->json(Helper::formatStandardApiResponse('error', 'Failed save movement'));
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(Helper::formatStandardApiResponse('error', 'Failed save movement'));
                // Handle other unexpected exceptions.
            }
        }
    }

    public function updateMovement(Request $request)
    {
        try {
            DB::beginTransaction();

            $company = explode('+', $request->company_id);
            $movement_number = '';
            $checkMovementNumber = MovementsModel::where('movement_number', $request->movement_numbers)->get();
            $statusLabel = Statuslabel::find($request->status_movement);
            if (count($checkMovementNumber) > 0) {
                $dateNow = date("dmY");
                $latestMovement = MovementsModel::whereRaw("SUBSTRING(movement_number, 1, 8) = ?", [$dateNow])
                    ->orderBy('movement_number', 'desc')
                    ->first();
                if ($latestMovement) {
                    // Jika movement number sudah ada, ambil angka terbesar dan tambahkan 1
                    $latestNumber = (int) substr($latestMovement->movement_number, -2);
                    $movementsNumber = $dateNow . '-' . str_pad($latestNumber + 1, 2, '0', STR_PAD_LEFT);
                } else {
                    // Jika belum ada data untuk tanggal hari ini, gunakan angka 01
                    $movementsNumber = $dateNow . '-01';
                }
                $movement_number = $movementsNumber;
            } else {
                $movement_number = $request->movement_numbers;
            }

            $idMovement = $request->idMovement;

            $findMovement = MovementsModel::findOrFail($idMovement);
            if (!$findMovement) {
                return response()->json(Helper::formatStandardApiResponse('error', 'Movement not found'));
            }

            if ($request->to_receiver === 'manual' && $request->person_id === 'manual') {

                $findMovement->project_id = $request->project_id;
                $findMovement->movement_number = $movement_number;
                $findMovement->location_id = $request->project_location;
                $findMovement->company_id = $company[0];
                $findMovement->movement_status_id = $statusLabel->id;
                $findMovement->to = $request->receiver_manual;
                $findMovement->person_charge = $request->person_charge_manual;
                $findMovement->notes = $request->notes;
                $findMovement->phone_person_in_charge = $request->phonePersonInCharge;
                $findMovement->contact_recipient = $request->contact_recipient;
                $findMovement->update();
            } elseif ($request->to_receiver === 'manual' && $request->person_id != 'manual') {

                $findMovement->project_id = $request->project_id;
                $findMovement->movement_number = $movement_number;
                $findMovement->location_id = $request->project_location;
                $findMovement->company_id = $company[0];
                $findMovement->movement_status_id = $statusLabel->id;
                $findMovement->to = $request->receiver_manual;
                $findMovement->person_charge = $request->person_id;
                $findMovement->notes = $request->notes;
                $findMovement->contact_recipient = $request->contact_recipient;
                $findMovement->phone_person_in_charge = null;
                $findMovement->update();
            } elseif ($request->to_receiver != 'manual' && $request->person_id === 'manual') {
                $findMovement->project_id = $request->project_id;
                $findMovement->movement_number = $movement_number;
                $findMovement->location_id = $request->project_location;
                $findMovement->company_id = $company[0];
                $findMovement->movement_status_id = $statusLabel->id;
                $findMovement->to = $request->to_receiver;
                $findMovement->person_charge = $request->person_charge_manual;
                $findMovement->notes = $request->notes;
                $findMovement->phone_person_in_charge = $request->phonePersonInCharge;
                $findMovement->contact_recipient = $request->contact_recipient;
                $findMovement->update();
            } else {
                $findMovement->project_id = $request->project_id;
                $findMovement->movement_number = $movement_number;
                $findMovement->location_id = $request->project_location;
                $findMovement->company_id = $company[0];
                $findMovement->movement_status_id = $statusLabel->id;
                $findMovement->to = $request->to_receiver;
                $findMovement->person_charge = $request->person_id;
                $findMovement->notes = $request->notes;
                $findMovement->phone_person_in_charge = null;
                $findMovement->contact_recipient = $request->contact_recipient;
                $findMovement->update();
            }

            // Find asset IDs assigned to the movement
            $assignedAssetIds = AssetMovement::where('movement_id', $findMovement->id)->pluck('asset_id');

            // Update assets assigned to the movement
            Asset::whereIn('id', $assignedAssetIds)->update([
                'assigned_to' => null,
                'assigned_type' => null,
            ]);
            MovementLogs::create([
                'movement_id' => $findMovement->id,
                'activity' => 'Update : Reassigned asset'
            ]);
            // Iterate over assigned asset IDs
            foreach ($assignedAssetIds as $assetId) {
                // Create action log for each asset ID
                Actionlog::firstOrCreate([
                    'item_id' => $assetId,
                    'item_type' => Asset::class,
                    'user_id' => Auth::user()->id,
                    'target_id' => $findMovement->id,
                    'target_type' => MovementsModel::class,
                    'created_at' => Carbon::now(),
                    'action_type' => 'update reassigned asset',
                ]);
            }
            // Delete existing asset movements associated with the movement ID
            AssetMovement::where('movement_id', $findMovement->id)->delete();
            foreach ($request->selected_assets as $asset) {
                $assets = Asset::where('id', $asset)->first();
                $assets->assigned_to = $findMovement->id;
                $assets->last_checkout = Carbon::now();
                $assets->assigned_type = MovementsModel::class;
                $assets->checkout_counter++;
                $assets->status_id = $statusLabel->id;
                $assets->update();
                //
                AssetMovement::create([
                    'asset_id' => $asset,
                    'movement_id' => $findMovement->id
                ]);
                //
                Actionlog::firstOrCreate([
                    'item_id' => $asset,
                    'item_type' => Asset::class,
                    'user_id' => Auth::user()->id,
                    'target_id' => $findMovement->id,
                    'target_type' => MovementsModel::class,
                    'created_at' =>  Carbon::now(),
                    'action_type' => 'update checkout movement',
                ]);
            }
            MovementLogs::create([
                'movement_id' => $findMovement->id,
                'activity' => 'Update Movement -> ' . $statusLabel->name
            ]);

            DB::commit();

            ///
            // return Redirect::to(url('movement?status=openMovement'))
            //     ->with('success', trans('admin/movement/message.create.success'));
            return response()->json(Helper::formatStandardApiResponse('success', $findMovement, 'Movement saved'));
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(Helper::formatStandardApiResponse('error', $e->errors()));
        } catch (QueryException $e) {
            DB::rollBack();
            // Handle database query exceptions (e.g., foreign key constraints).
            return response()->json(Helper::formatStandardApiResponse('error', 'Failed save movement'));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(Helper::formatStandardApiResponse('error', 'Failed save movement'));
            // Handle other unexpected exceptions.
        }
    }

    /**
     * Print invoice Movement
     *
     * @return void
     */
    public function printInvoice()
    {
        $to = session('to');
        $from = session('from');
        $address = session('address');
        $projectName = session('projectName');
        $projectNumber = session('projectNumber');
        $location = session('location');
        $notes = session('notes');
        $person = session('person');
        $listAssets = session('listAssets');
        $movementNumber = session('movementNumber');
        $phone = session('phone');
        $recipient = session('recipient');
        $chooseDate = session('chooseDate');
        return view('movement.invoices', compact('to', 'from', 'projectNumber', 'location', 'notes', 'person', 'listAssets', 'address', 'projectName', 'movementNumber', 'phone', 'recipient', 'chooseDate'));
    }
    /**
     * Print invoice Movement
     *
     * @return void
     */
    public function printInvoiceIndex(Request $request)
    {
        $movementId = $request->movement_id;

        $movement = MovementsModel::find($movementId);
        $project = ProjectMovement::find($movement->project_id);
        $companyData = BranchCompany::find($movement->company_id);
        $locationData = Location::find($movement->location_id);

        $to = $movement->to;
        $from = $companyData->company_name;
        $address = $companyData->address;
        $projectNumber = $project->project_number;
        $projectName = $project->project_name;
        $location = $locationData->name;
        $notes = $movement->notes;
        $movementNumber = $movement->movement_number;
        $assetMovement = AssetMovement::where('movement_id', $movement->id)->get();
        $assetIds = $assetMovement->pluck('asset_id')->toArray(); // Extracting asset IDs from the collection
        $listAssets = Asset::whereIn('id', $assetIds)->get(); // Fetching assets based on the extracted IDs
        $chooseDate = null; // Initialize the variable to avoid "Undefined variable" error

        if ($request->choose_date === 'current_date_time') {
            $chooseDate = date($request->format_date);
        } elseif ($request->choose_date === 'specific_date') {
            $specificDate = new DateTime($request->specific_date_text);
            $chooseDate = $specificDate->format($request->format_date);
        }

        if ($movement->phone_person_in_charge === null) {
            $personData = User::find($movement->person_charge);
            $person = $personData->first_name . ' ' . $personData->last_name;
            $phone = $personData->phone;
        } else {
            $person = $movement->person_charge;
            $phone = $movement->phone_person_in_charge;
        }

        $recipient = User::find($movement->contact_recipient);

        return view('movement.invoices', compact('to', 'from', 'projectNumber', 'location', 'notes', 'person', 'listAssets', 'address', 'projectName', 'movementNumber', 'phone', 'recipient', 'chooseDate'));
    }

    public function returnMovementPartial($id)
    {
        $movement = MovementsModel::find($id);
        if (!$movement) {
            return back()
                ->with('error', 'Movement not found');
        }


        if($movement->is_return === 1){
            return redirect()->route('movement.index')->with('error', 'Movement has been returned');
        }

        $assets = AssetMovement::leftJoin('movements', 'movements.id', '=', 'asset_movements.movement_id')
            ->leftJoin('assets', 'assets.id', '=', 'asset_movements.asset_id')
            ->leftJoin('locations', 'locations.id', '=', 'assets.location_id')
            ->leftJoin('status_labels', 'status_labels.id', '=', 'assets.status_id')
            ->select(
                'asset_movements.id as id',
                'assets.id as id_asset',
                'asset_movements.is_return as isReturn',
                'assets.name as asset_name',
                'assets.asset_tag as asset_tag',
                'status_labels.name as status',
                'status_labels.id as status_id',
            )->where('movements.id', $movement->id)->get();
        $assetMovement = AssetMovement::all();
        $statuses = Statuslabel::all();
        return view('movement.return-movement', compact('movement', 'assets', 'statuses', 'assetMovement'));
    }

    public function changeMovementDatetime(Request $request)
    {
        try {
            $movementId = $request->input('movement_id');
            $executionType = $request->input('execution_type');
            $specificDate = $request->input('specific_date');  // Corrected line
            $assetMovementIsScanned = $request->input('scannedAsset');
            $selectedReturn = $request->input('select_return');
            $changesStatusAsset = $request->input('status_asset');
            $movement = MovementsModel::find($movementId);
            $locationInOffice = Location::where('name', 'In Office')->first();


            if (!$movement) {
                return response()->json(Helper::formatStandardApiResponse('error', null, 'Movement not found'));
            }


            //! [IF WITHOUT SCANNED]
            if (!$request->filled('scannedAsset')) {

                $returnAssetWithoutScanned = json_decode($selectedReturn, true);
                $statusData = $returnAssetWithoutScanned['statusData'];

                $statusAssetChanges = json_decode($changesStatusAsset, true);
                $statusAsset = $statusAssetChanges['statusAsset'];

                // Loop through each item in statusData
                foreach ($statusData as $item) {
                    $assetId = $item['assetId'];
                    $status = $item['status'];

                    $assetName = Asset::find($assetId);
                    $statusLabel = Statuslabel::where('id', $assetName->status_id)->first();

                    $checkIsReturnNullOrFalse = AssetMovement::where('asset_id', $assetId)
                        ->where('movement_id', $movement->id)
                        ->first();

                    if ($checkIsReturnNullOrFalse && ($checkIsReturnNullOrFalse->is_return === null || $checkIsReturnNullOrFalse->is_return === 0)) {
                        if ($status === "yes") {



                            $assetMovement = AssetMovement::where('asset_id', $assetId)
                                ->where('movement_id', $movement->id)
                                ->update(['is_return' => $this->returnMovement]);

                            if ($assetMovement) {
                                $assets = Asset::where('id', $assetId)->first();
                                $assets->assigned_to = null;
                                $assets->assigned_type = null;
                                $assets->location_id = $locationInOffice->id;
                                $assets->update();

                                Actionlog::firstOrCreate([
                                    'item_id' => $assetId,
                                    'item_type' => Asset::class,
                                    'user_id' => Auth::user()->id,
                                    'target_id' => $movement->id,
                                    'target_type' => MovementsModel::class,
                                    'created_at' => Carbon::now(),
                                    'action_type' => 'return asset from movement',
                                ]);
                            }

                            foreach ($statusAsset as $changedStatus) {
                                $assetId = $changedStatus['assetId'];
                                $status = $changedStatus['status'];

                                $statusLabel = Statuslabel::find($status);

                                if ($statusLabel) {
                                    $assets = Asset::where('id', $assetId)->first();
                                    $assets->status_id = $statusLabel->id;
                                    $assets->update();
                                }
                            }

                            MovementLogs::create([
                                'movement_id' => $movementId,
                                'activity' => 'Asset movement returned (asset name : ' . $assetName->name . ' ) with status ' . $statusLabel->name
                            ]);
                        } elseif ($status === "no") {
                            $assetMovement = AssetMovement::where('asset_id', $assetId)
                                ->where('movement_id', $movement->id)
                                ->update(['is_return' => $this->inMovement]);
                        }
                    }
                }


                if ($executionType === 'current_date_time') {

                    $movement->datetime_specific =  Carbon::now();

                    if (AssetMovement::where('movement_id', $movementId)->where('is_return', 0)->count() === 0) {
                        $movement->is_return = $this->returnMovement;
                        MovementLogs::create([
                            'movement_id' => $movementId,
                            'activity' => 'All asset movement is returned',
                        ]);
                    } else {
                        MovementLogs::create([
                            'movement_id' => $movementId,
                            'activity' => 'Asset movement returned',
                        ]);
                    }

                    $movement->update();
                } elseif ($executionType === 'specific_date') {
                    $movement->datetime_specific = $specificDate;

                    if (AssetMovement::where('movement_id', $movementId)->where('is_return', 0)->count() === 0) {
                        $movement->is_return = $this->returnMovement;

                        MovementLogs::create([
                            'movement_id' => $movementId,
                            'activity' => 'All asset movement is returned (Manual datetime: ' . $specificDate . '0)',
                            'created_at' => $specificDate
                        ]);
                    } else {
                        MovementLogs::create([
                            'movement_id' => $movementId,
                            'activity' => 'Asset movement returned (Manual datetime: ' . $specificDate . '0)',
                            'created_at' => $specificDate
                        ]);
                    }

                    $movement->update();
                }
            } else {
                //! [WITH SCANNED]

                $statusAssetChanges = json_decode($changesStatusAsset, true);
                $statusAsset = $statusAssetChanges['statusAsset'];
                $assetIdsWithScanned = explode(',', $assetMovementIsScanned);

                foreach ($assetIdsWithScanned as $ids_asset) {
                    $checkIsReturnNullOrFalseWithScanned = AssetMovement::where('asset_id', $ids_asset)
                        ->where('movement_id', $movement->id)
                        ->first();

                    if (
                        $checkIsReturnNullOrFalseWithScanned
                        && ($checkIsReturnNullOrFalseWithScanned->is_return === null
                            || $checkIsReturnNullOrFalseWithScanned->is_return === 0)
                    ) {
                        $assetMovement = AssetMovement::where('asset_id', $ids_asset)
                            ->where('movement_id', $movement->id)
                            ->update(['is_return' => $this->returnMovement]);
                        ///
                        $assets = Asset::where('id', $ids_asset)->first();
                        $assets->assigned_to = null;
                        $assets->assigned_type = null;
                        $assets->location_id = $locationInOffice->id;
                        $assets->update();

                        Actionlog::firstOrCreate([
                            'item_id' => $ids_asset,
                            'item_type' => Asset::class,
                            'user_id' => Auth::user()->id,
                            'target_id' => $movement->id,
                            'target_type' => MovementsModel::class,
                            'created_at' => Carbon::now(),
                            'action_type' => 'return asset from movement',
                        ]);
                    }
                }
                // Update is_return to $this->inMovement for assets not in $assetIdsWithScanned
                AssetMovement::where('movement_id', $movement->id)
                    ->whereNotIn('asset_id', $assetIdsWithScanned)
                    ->where(function ($query) {
                        $query->where('is_return', 0)
                            ->orWhereNull('is_return');
                    })
                    ->update(['is_return' => $this->inMovement]);


                foreach ($statusAsset as $changedStatus) {
                    $assetId = $changedStatus['assetId'];
                    $status = $changedStatus['status'];
                    $statusLabel = Statuslabel::where('id', $status)->first();
                    if ($statusLabel) {
                        $assets = Asset::where('id', $assetId)->first();
                        $assets->status_id = $statusLabel->id;
                        $assets->update();
                    }
                }

                if ($executionType === 'current_date_time') {

                    $movement->datetime_specific =  Carbon::now();
                    //
                    foreach (explode(',', $assetMovementIsScanned) as $idsAsset) {
                        $assetName = Asset::find($idsAsset);
                        $status = Statuslabel::where(
                            'id',
                            $assetName->status_id
                        )->first();

                        MovementLogs::create([
                            'movement_id' => $movementId,
                            'activity' => 'Asset movement returned (asset name : ' . $assetName->name . ' ) with status ' . $status->name
                        ]);
                    }
                    if (
                        AssetMovement::where(
                            'movement_id',
                            $movementId
                        )->where('is_return', 0)->count() === 0
                    ) {
                        $movement->is_return = $this->returnMovement;
                        MovementLogs::create([
                            'movement_id' => $movementId,
                            'activity' => 'All asset movement is returned',
                        ]);
                    } else {
                        MovementLogs::create([
                            'movement_id' => $movementId,
                            'activity' => 'Asset movement returned',
                        ]);
                    }
                    $movement->update();
                } elseif ($executionType === 'specific_date') {
                    $movement->datetime_specific = $specificDate;
                    foreach (explode(',', $assetMovementIsScanned) as $idsAsset) {
                        $assetName = Asset::find($idsAsset);
                        $status = Statuslabel::where(
                            'id',
                            $assetName->status_id
                        )->first();

                        MovementLogs::create([
                            'movement_id' => $movementId,
                            'activity' => 'Asset movement returned (asset name : ' . $assetName->name . ' ) with status ' . $status->name,
                            'created_at' => $specificDate

                        ]);
                    }

                    if (
                        AssetMovement::where(
                            'movement_id',
                            $movementId
                        )->where('is_return', 0)->count() === 0
                    ) {
                        $movement->is_return = $this->returnMovement;

                        MovementLogs::create([
                            'movement_id' => $movementId,
                            'activity' => 'All asset movement is returned (Manual datetime: ' . $specificDate . '0)',
                            'created_at' => $specificDate
                        ]);
                    } else {
                        MovementLogs::create([
                            'movement_id' => $movementId,
                            'activity' => 'Asset movement returned (Manual datetime: ' . $specificDate . '0)',
                            'created_at' => $specificDate
                        ]);
                    }
                    $movement->update();
                }
            }

            if (AssetMovement::where('movement_id', $movementId)->where('is_return', 0)->count() === 0) {
                return response()->json(Helper::formatStandardApiResponse('success', 'movementReturn', 'Success return assets'));
            } else {
                return response()->json(Helper::formatStandardApiResponse('success', null, 'Success return assets'));
            }
        } catch (\Exception $e) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Success return assets'), 200);
        }
    }


    public function saveFormatDate(Request $request)
    {
        try {
            FormatDate::create([
                'format' => $request->format
            ]);
            return response()->json(Helper::formatStandardApiResponse('success', $request->format, 'Success create new format date'));
        } catch (\Throwable $e) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'error create format date'), 200);
        }
    }
}

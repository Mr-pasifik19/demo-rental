<?php

namespace App\Http\Controllers\Movement;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\BranchCompany;
use App\Models\MovementsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompaniesMovementController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companyBranch = BranchCompany::latest()->get();
        $movement = MovementsModel::all();
        return view('movement.company-movement.company-movement', compact('companyBranch', 'movement'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('movement.company-movement.edit')->with('statuslabel_list', Helper::statusLabelList())
            ->with('item', new BranchCompany())
            ->with('statuslabel_types', Helper::statusTypeList());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            BranchCompany::create([
                'company_name' => $request->company_name,
                'address' => $request->address
            ]);

            return redirect()->route('company-movement.index')->with('success', trans('admin/movement/message.company_movement.success'));
        } catch (\Exception $e) {
            return back()->with('error', trans('admin/movement/message.company_movement.error'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = BranchCompany::find($id);
        if (!$item) {
            return redirect()->route('company-movement.index')
                ->with('error', trans('admin/movement/message.company_movement.notFound'));
        }
        $this->authorize($item);
        return view('movement.company-movement.edit', compact('item'))->with('statuslabel_list', Helper::statusLabelList())
            ->with('statuslabel_types', Helper::statusTypeList());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $company = BranchCompany::find($id);
            if (!$company) {
                return redirect()->route('company-movement.index')
                    ->with('error', trans('admin/movement/message.company_movement.notFound'));
            }
            $company->company_name = $request->company_name;
            $company->address = $request->address;
            $company->update();
            //
            return redirect()->route('company-movement.index')->with('success', trans('admin/movement/message.company_movement.successUpdate'));
        } catch (\Exception $e) {
            return back()->with('error', trans('admin/movement/message.company_movement.errorUpdate'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $company = BranchCompany::find($id);
            if (!$company) {
                return redirect()->route('company-movement.index')
                    ->with('error', trans('admin/movement/message.company_movement.notFound'));
            }
            $company->delete();

            return redirect()->route('company-movement.index')->with('success', trans('admin/movement/message.company_movement.successDelete'));
        } catch (\Exception $e) {
            return back()->with('error', trans('admin/movement/message.company_movement.errorDelete'));
        }
    }

    public function showMovementByCompanyBranch($id, Request $request)
    {
        $companyBranch = BranchCompany::find($id);

        if (!$companyBranch) {
            return redirect()->route('company-movement.index')
                ->with('error', trans('admin/movement/message.company_movement.notFound'));
        }

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

        $search =  $request->input('searchMovement');
        $selectCategory = $request->input('selectCategory');
        //

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
        )->where('movements.company_id', $id)->groupBy('project_movements.id')->get();

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

        return view('movement.company-movement.view', compact('companyBranch', 'search', 'movementData', 'statusMap', 'locationMap', 'companyMap', 'companyIdsMap', 'categoryMovementMap'));
    }
}

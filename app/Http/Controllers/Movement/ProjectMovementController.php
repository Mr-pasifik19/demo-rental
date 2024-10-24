<?php

namespace App\Http\Controllers\Movement;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AddressMovement;
use App\Models\MovementsModel;
use App\Models\ProjectMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

use function PHPUnit\Framework\isEmpty;

class ProjectMovementController extends Controller
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
        $projectMovement = ProjectMovement::leftJoin('address_movements', 'address_movements.project_movement_id', '=', 'project_movements.id')
            ->select(
                'project_movements.id as id_project',
                'project_movements.project_number as project_number',
                'project_movements.project_name as project_name',
                DB::raw('GROUP_CONCAT(address_movements.address) as addresses')
            )->groupBy('project_movements.id')
            ->get();

        $movement = MovementsModel::all();

        return view('movement.project-movement.project-movement', compact('projectMovement', 'movement'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::all();
        return view('movement.project-movement.edit', compact('users'))->with('statuslabel_list', Helper::statusLabelList())
            ->with('item', new ProjectMovement)
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
            $project = ProjectMovement::create([
                'project_number' => $request->project_number,
                'project_name' => $request->project_name,
                // 'person_id' => $request->person_id,
            ]);
            foreach ($request->address as $address) {
                AddressMovement::create([
                    'address' => $address,
                    'project_movement_id' => $project->id
                ]);
            }
            return redirect()->route('project-movement.index')
                ->with('success', trans('admin/movement/message.project_movement.success'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', trans('admin/movement/message.project_movement.error'));
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
        $users = User::all();
        $item = ProjectMovement::find($id);
        if (!$item) {
            return redirect()->route('project-movement.index')
                ->with('error', trans('admin/movement/message.project_movement.notFound'));
        }
        $address = AddressMovement::where('project_movement_id', $id)->get();
        $this->authorize($item);
        return view('movement.project-movement.edit', compact('users', 'item', 'address'))->with('statuslabel_list', Helper::statusLabelList())
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
            $project = ProjectMovement::find($id);
            if (!$project) {
                return redirect()->route('project-movement.index')
                    ->with('error', trans('admin/movement/message.project_movement.notFound'));
            }
            $project->project_number = $request->project_number;
            $project->project_name = $request->project_name;
            // $project->person_id = $request->person_id;
            $project->update(); // Save the main project details



            if (!empty($request->address)) {
                foreach ($request->address as $address) {
                    AddressMovement::create([
                        'address' => $address,
                        'project_movement_id' => $project->id
                    ]);
                }
            }



            return redirect()->route('project-movement.index')
                ->with('success', trans('admin/movement/message.project_movement.successUpdate'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', trans('admin/movement/message.project_movement.errorUpdate'));
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
            $project = ProjectMovement::find($id);

            if (!$project) {
                return redirect()->route('project-movement.index')
                    ->with('error', trans('admin/movement/message.project_movement.notFound'));
            }

            $addressMovements = AddressMovement::where('project_movement_id', $project->id)->get();

            $movement = MovementsModel::where('project_id', $project->id)->first();

            foreach ($addressMovements as $addressMovement) {
                $addressMovement->delete();
            }

            if ($movement) {
                $movement->delete();
            }


            $project->delete();

            return redirect()->route('project-movement.index')
                ->with('success', trans('admin/movement/message.project_movement.successDelete'));
        } catch (QueryException $e) {
            // Handle database query exceptions (e.g., foreign key constraints).
            return redirect()->back()->with('error', trans('admin/movement/message.project_movement.errorDelete'));
        } catch (\Exception $e) {
            // Handle other unexpected exceptions.
            return redirect()->back()->with('error', trans('admin/movement/message.project_movement.errorDelete'));
        }
    }


    /**
     * Get ALl  Address By Project ID
     *
     * @return void
     */
    public function getAddressByProjectId(Request $request)
    {
        $empData['data'] = AddressMovement::where('project_movement_id', $request->project)->get();

        return response()->json($empData);
    }

    public function updatedAddress(Request $request, $id)
    {

        try {
            $data = AddressMovement::find($id);
            $data->address = $request->addressUpdate;
            $data->update();

            return back()
                ->with('success', trans('admin/movement/message.project_movement.successUpdate'));
        } catch (\Exception $e) {
            // Handle other unexpected exceptions.
            return back()->with('error', trans('admin/movement/message.project_movement.errorUpdate'));
        }
    }
    public function destroyAddress($id)
    {

        try {
            $address = AddressMovement::find($id);
            $address->delete();

            return redirect()->back()
                ->with('success', trans('admin/movement/message.project_movement.successDelete'));
        } catch (\Exception $e) {
            // Handle other unexpected exceptions.
            return redirect()->back()->with('error', trans('admin/movement/message.project_movement.errorDelete'));
        }
    }
}

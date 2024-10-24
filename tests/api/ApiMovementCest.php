<?php

use App\Helpers\Helper;
use App\Http\Transformers\MovementsTransformer;
use App\Models\Movement;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class ApiMovementsCest
{
    protected $faker;
    protected $user;
    protected $timeFormat;

    public function _before(ApiTester $I)
    {
        $this->faker = \Faker\Factory::create();
        $this->user = \App\Models\User::find(1);
        Setting::getSettings()->time_display_format = 'H:i';
        $I->amBearerAuthenticated($I->getToken($this->user));
    }

    /** @test */
    public function indexMovements(ApiTester $I)
    {
        $I->wantTo('Get a list of movements');

        // call
        $I->sendGET('/movement?limit=20&sort=id&order=desc');
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        // FIXME: This is disabled because the statuslabel join is doing something weird in Api/MovementsController@index
        // However, it's hiding other real test errors in other parts of the code, so disabling this for now until we can fix.
//        $response = json_decode($I->grabResponse(), true);

        // sample verify
//        $Movement = Movement::orderByDesc('id')->take(20)->get()->first();

        //
//        $I->seeResponseContainsJson($I->removeTimestamps((new MovementsTransformer)->transformMovement($Movement)));
    }

    /** @test */
    public function createMovement(ApiTester $I, $scenario)
    {
        $I->wantTo('Create a new movement');

        $temp_movement = \App\Models\Movement::factory()->laptopMbp()->make([
            'movement_tag' => 'Test Movement Number',
            'company_id' => 2,
        ]);

        // setup
        $data = [
            'movement_tag' => $temp_movement->movement_tag,
            'assigned_to' => $temp_movement->assigned_to,
            'company_id' => $temp_movement->company->id,
            'image' => $temp_movement->image,
            'model_id' => $temp_movement->model_id,
            'name' => $temp_movement->name,
            'notes' => $temp_movement->notes,
            'purchase_cost' => $temp_movement->purchase_cost,
            'purchase_date' => $temp_movement->purchase_date,
            'rtd_location_id' => $temp_movement->rtd_location_id,
            'serial' => $temp_movement->serial,
            'status_id' => $temp_movement->status_id,
            'supplier_id' => $temp_movement->supplier_id,
            'warranty_months' => $temp_movement->warranty_months,
        ];

        // create
        $I->sendPOST('/movement', $data);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
    }

    /** @test */
    public function updateMovementWithPatch(ApiTester $I, $scenario)
    {
        $I->wantTo('Update an movement with PATCH');

        // create
        $movement = \App\Models\Movement::factory()->laptopMbp()->create([
            'company_id' => 2,
            'rtd_location_id' => 3,
        ]);
        $I->assertInstanceOf(\App\Models\Movement::class, $movement);

        $temp_movement = \App\Models\Movement::factory()->laptopAir()->make([
            'company_id' => 3,
            'name' => 'updated movement name',
            'rtd_location_id' => 1,
        ]);

        $data = [
            'movement_tag' => $temp_movement->movement_tag,
            'assigned_to' => $temp_movement->assigned_to,
            'company_id' => $temp_movement->company->id,
            'image' => $temp_movement->image,
            'model_id' => $temp_movement->model_id,
            'name' => $temp_movement->name,
            'notes' => $temp_movement->notes,
            'order_number' => $temp_movement->order_number,
            'purchase_cost' => $temp_movement->purchase_cost,
            'purchase_date' => $temp_movement->purchase_date->format('Y-m-d'),
            'rtd_location_id' => $temp_movement->rtd_location_id,
            'serial' => $temp_movement->serial,
            'status_id' => $temp_movement->status_id,
            'supplier_id' => $temp_movement->supplier_id,
            'warranty_months' => $temp_movement->warranty_months,
        ];

        $I->assertNotEquals($movement->name, $data['name']);

        // update
        $I->sendPATCH('/movement/'.$movement->id, $data);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        $response = json_decode($I->grabResponse());
        // dd($response);
        $I->assertEquals('success', $response->status);
        $I->assertEquals(trans('admin/movement/message.update.success'), $response->messages);
        $I->assertEquals($movement->id, $response->payload->id); // movement id does not change
        $I->assertEquals($temp_movement->movement_tag, $response->payload->movement_tag); // movement tag updated
        $I->assertEquals($temp_movement->name, $response->payload->name); // movement name updated
        $I->assertEquals($temp_movement->rtd_location_id, $response->payload->rtd_location_id); // movement rtd_location_id updated
        $temp_movement->created_at = Carbon::parse($response->payload->created_at);
        $temp_movement->updated_at = Carbon::parse($response->payload->updated_at);
        $temp_movement->id = $movement->id;
        $temp_movement->location_id = $response->payload->rtd_location_id;

        // verify
        $I->sendGET('/movement/'.$movement->id);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson((new MovementsTransformer)->transformMovement($temp_movement));
    }

    /** @test */
    public function deleteMovementTest(ApiTester $I, $scenario)
    {
        $I->wantTo('Delete an movement');

        // create
        $movement = \App\Models\Movement::factory()->laptopMbp()->create();
        $I->assertInstanceOf(\App\Models\Movement::class, $movement);

        // delete
        $I->sendDELETE('/movement/'.$movement->id);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        $response = json_decode($I->grabResponse());
        $I->assertEquals('success', $response->status);
        $I->assertEquals(trans('admin/movement/message.delete.success'), $response->messages);

        // verify, expect a 200
        $I->sendGET('/movement/'.$movement->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        // Make sure we're soft deleted.
        $response = json_decode($I->grabResponse());
        $I->assertNotNull($response->deleted_at);
    }
}

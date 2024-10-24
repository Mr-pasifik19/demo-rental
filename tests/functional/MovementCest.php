<?php

class MovementCest
{
    public function _before(FunctionalTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('username', 'admin');
        $I->fillField('password', 'password');
        $I->click('Login');
    }

    // tests
    public function tryToTest(FunctionalTester $I)
    {
        $I->wantTo('ensure that the create movements form loads without errors');
        $I->lookForwardTo('seeing it load without errors');
        $I->amOnPage(route('movement.create'));
        $I->dontSee('Create Movement', '.page-header');
        $I->see('Create Movement', 'h1.pull-left');
    }

    public function failsEmptyValidation(FunctionalTester $I)
    {
        $I->wantTo('Test Validation Fails with blank elements');
        $I->amOnPage(route('movement.create'));
        // Settings factory can enable auto prefixes, which generate a random Movement id.  Lets clear it out for the sake of this test.
        $I->fillField('#movement_tag', '');
        $I->click('Save');
        $I->see('The movement number field is required.', '.alert-msg');
        $I->see('The model id field is required.', '.alert-msg');
        $I->see('The status id field is required.', '.alert-msg');
    }

    public function passesCreateAndCheckout(FunctionalTester $I)
    {
        $movement = \App\Models\Movement::factory()->laptopMbp()->make([
            'movement_tag'=>'test tag',
            'name'=> 'test movement',
            'company_id'=>1,
            'warranty_months'=>15,
         ]);
        $userId = $I->getUserId();
        $values = [
            'movement_tags[1]'         => $movement->movement_tag,
            'assigned_user'     => $userId,
            'company_id'        => $movement->company_id,
            'model_id'          => $movement->model_id,
            'name'              => $movement->name,
            'notes'             => $movement->notes,
            'order_number'      => $movement->order_number,
            'purchase_cost'     => $movement->purchase_cost,
            'purchase_date'     => '2016-01-01',
            'requestable'       => $movement->requestable,
            'rtd_location_id'   => $movement->rtd_location_id,
            'serials[1]'        => $movement->serial,
            'status_id'         => $movement->status_id,
            'supplier_id'       => $movement->supplier_id,
            'warranty_months'   => $movement->warranty_months,
        ];

        $seenValues = [
            'movement_tag'         => $movement->movement_tag,
            'assigned_to'       => $userId,
            'assigned_type'     => \App\Models\User::class,
            'company_id'        => $movement->company_id,
            'model_id'          => $movement->model_id,
            'name'              => $movement->name,
            'notes'             => $movement->notes,
            'order_number'      => $movement->order_number,
            'purchase_cost'     => $movement->purchase_cost,
            'purchase_date'     => '2016-01-01',
            'requestable'       => $movement->requestable,
            'rtd_location_id'   => $movement->rtd_location_id,
            'serial'            => $movement->serial,
            'status_id'         => $movement->status_id,
            'supplier_id'       => $movement->supplier_id,
            'warranty_months'   => $movement->warranty_months,
        ];

        $I->wantTo('Test Validation Succeeds');
        $I->amOnPage(route('movement.create'));
        $I->submitForm('form#create-form', $values);
        $I->seeRecord('movements', $seenValues);
        $I->seeResponseCodeIs(200);
    }

    public function allowsDelete(FunctionalTester $I)
    {
        $I->wantTo('Ensure I can delete an movement');
        $I->sendDelete(route('movement.destroy', $I->getMovementId()), ['_token' => csrf_token()]);
        $I->seeResponseCodeIs(200);
    }
}

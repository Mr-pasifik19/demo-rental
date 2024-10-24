<?php

namespace Database\Seeders;

use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Database\Seeder;

class StatuslabelSeeder extends Seeder
{
    public function run()
    {
        ///
        // Statuslabel::truncate();
        // $admin = User::where('permissions->superuser', '1')->first() ?? User::factory()->firstAdmin()->create();
        // Statuslabel::factory()->rtd()->create([
        //     'name' => 'Out for Calibration',
        //     'user_id' => $admin->id,
        // ]);
        // Statuslabel::factory()->pending()->create([
        //     'name' => 'Pending',
        //     'user_id' => $admin->id,
        // ]);
        // Statuslabel::factory()->archived()->create([
        //     'name' => 'Archived',
        //     'user_id' => $admin->id,
        // ]);
        // Statuslabel::factory()->outForDiagnostics()->create(['user_id' => $admin->id]);
        // Statuslabel::factory()->outForRepair()->create(['user_id' => $admin->id]);
        // Statuslabel::factory()->broken()->create(['user_id' => $admin->id]);
        // Statuslabel::factory()->lost()->create(['user_id' => $admin->id]);

        $data = [
            [
                'archived' => 0,
                'deployable' => 0,
                'name' => 'Out For Calibration',
                'pending' => 1,
                'user_id' => 1,
            ],
            [
                'archived' => 1,
                'deployable' => 0,
                'name' => 'Archived',
                'pending' => 0,
                'user_id' => 1,
            ],
            [
                'archived' => 0,
                'deployable' => 1,
                'name' => 'Available',
                'pending' => 0,
                'user_id' => 1,
            ],
            [
                'archived' => 0,
                'deployable' => 0,
                'name' => 'Booked',
                'pending' => 1,
                'user_id' => 1,
            ],
            [
                'archived' => 0,
                'deployable' => 0,
                'name' => 'Damaged',
                'pending' => 0,
                'user_id' => 1,
            ],
            [
                'archived' => 0,
                'deployable' => 1,
                'name' => 'In Project',
                'pending' => 0,
                'user_id' => 1,
            ],
            [
                'archived' => 0,
                'deployable' => 0,
                'name' => 'Out for Service',
                'pending' => 1,
                'user_id' => 1,
            ],
        ];

        foreach ($data as $value) {
            Statuslabel::create($value);
        }
    }
}

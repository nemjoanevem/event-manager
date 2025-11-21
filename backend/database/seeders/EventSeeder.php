<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $admins = User::where('is_admin', true)->get();

        Event::factory()
            ->count(25)
            ->state(fn () => [
                'created_by' => $admins->random()->id,
            ])
            ->create();
    }
}

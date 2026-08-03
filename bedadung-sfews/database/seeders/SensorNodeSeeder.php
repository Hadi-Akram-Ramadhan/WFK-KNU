<?php

namespace Database\Seeders;

use App\Models\SensorNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SensorNodeSeeder extends Seeder
{
    public function run(): void
    {
        SensorNode::updateOrCreate(
            ['node_id' => 'BEDADUNG_01'],
            [
                'name'             => 'Checkpoint Alpha — Sumbersari',
                'latitude'         => -8.168567,
                'longitude'        => 113.700339,
                'status'           => 'offline',
                'sensor_height_cm' => 30.0,
                'api_token'        => env('WEMOS_API_TOKEN', 'bedadung-sfews-secret-token-01'),
            ]
        );

        SensorNode::updateOrCreate(
            ['node_id' => 'BEDADUNG_02'],
            [
                'name'             => 'Checkpoint Beta — Kaliwates',
                'latitude'         => -8.175000,
                'longitude'        => 113.712000,
                'status'           => 'offline',
                'sensor_height_cm' => 30.0,
                'api_token'        => env('WEMOS_API_TOKEN_2', 'bedadung-sfews-secret-token-02'),
            ]
        );

        $this->command->info('✅ Sensor nodes seeded successfully.');
        $this->command->line('');
        $this->command->line('  API Token Node 01: ' . env('WEMOS_API_TOKEN', 'bedadung-sfews-secret-token-01'));
        $this->command->line('  API Token Node 02: ' . env('WEMOS_API_TOKEN_2', 'bedadung-sfews-secret-token-02'));
        $this->command->line('');
        $this->command->warn('  ⚠ Ganti API token ini di .env sebelum deploy ke production!');
    }
}

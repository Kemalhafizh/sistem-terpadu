<?php

namespace Database\Seeders;

use App\Services\EdwOlapEngineService;
use Illuminate\Database\Seeder;

class EdwOlapSeeder extends Seeder
{
    public function run(): void
    {
        /** @var EdwOlapEngineService $olapService */
        $olapService = app(EdwOlapEngineService::class);
        $olapService->refreshAllMarts(now()->toDateString());
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RhtCongeAgentSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/rhtcongeagent.sql');

        if (!file_exists($path)) {
            $this->command->info("SQL file not found: {$path}");
            return;
        }

        $sql = file_get_contents($path);

        // Extract INSERT statements for rhtcongeagent
        preg_match_all('/INSERT INTO `rhtcongeagent`.*?;/is', $sql, $matches);

        if (empty($matches[0])) {
            $this->command->info('No INSERT statements found for rhtcongeagent in SQL file.');
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($matches[0] as $stmt) {
                $stmt = rtrim($stmt, "\n; ");
                DB::statement($stmt);
            }
            DB::commit();
            $this->command->info('RhtCongeAgent data imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Import failed: ' . $e->getMessage());
        }
    }
}

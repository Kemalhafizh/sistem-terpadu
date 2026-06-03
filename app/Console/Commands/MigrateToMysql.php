<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateToMysql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-to-mysql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from SQLite to MySQL database for scaling up';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting SQLite to MySQL migration helper...');

        // Ensure default connection is sqlite and mysql is configured
        if (config('database.default') !== 'sqlite') {
            $this->error('The default database connection must be sqlite to run this command.');

            return 1;
        }

        try {
            DB::connection('mysql')->getPdo();
        } catch (\Exception $e) {
            $this->error('Cannot connect to MySQL database. Please check your config/database.php and .env settings.');
            $this->error($e->getMessage());

            return 1;
        }

        $this->info('MySQL connection verified.');

        if (! $this->confirm('This will wipe all existing data in the target MySQL database and run migrations. Do you wish to continue?')) {
            return 0;
        }

        $this->info('Running migrations on MySQL...');
        $this->call('migrate:fresh', [
            '--database' => 'mysql',
            '--force' => true,
        ]);

        $this->info('Disabling foreign key checks on MySQL...');
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

        // Get all tables from SQLite
        $tables = DB::connection('sqlite')->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        $skipTables = ['migrations']; // We don't migrate the migrations table since we just ran migrate:fresh

        foreach ($tables as $tableInfo) {
            $table = $tableInfo->name;
            if (in_array($table, $skipTables)) {
                continue;
            }

            $count = DB::connection('sqlite')->table($table)->count();
            if ($count === 0) {
                $this->line("Skipping table {$table} (0 rows)");

                continue;
            }

            $this->info("Migrating table: {$table} ({$count} rows)");

            // Chunk and insert
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            DB::connection('sqlite')->table($table)->orderByRaw('1')->chunk(500, function ($rows) use ($table, $bar) {
                $data = array_map(function ($row) {
                    return (array) $row;
                }, $rows->toArray());

                DB::connection('mysql')->table($table)->insert($data);
                $bar->advance(count($rows));
            });

            $bar->finish();
            $this->newLine();
        }

        $this->info('Re-enabling foreign key checks on MySQL...');
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Migration to MySQL completed successfully!');
        $this->line('You can now update your .env file to set DB_CONNECTION=mysql');

        return 0;
    }
}

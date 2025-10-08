<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PostgresRefresh extends Command
{
    protected $signature = 'pg:refresh 
                          {--seed : Ejecutar seeders}
                          {--seeder= : Seeder específico}';

    protected $description = 'Refresh completo para PostgreSQL (incluye schemas)';

    public function handle()
    {
        // 1. Eliminar todos los schemas
        $schemas = ['general_settings', 'public']; // Agrega otros schemas si existen

        foreach ($schemas as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS $schema CASCADE");
        }

        // 2. Recrear schema public
        DB::statement('CREATE SCHEMA public');

        // 3. Ejecutar migraciones
        $this->call('migrate');

        // 4. Ejecutar seeder si se solicita
        if ($this->option('seed')) {
            $seeder = $this->option('seeder') ?: 'Database\\Seeders\\DatabaseSeeder';
            $this->call('db:seed', ['--class' => $seeder]);
        }

        $this->info('Base de datos PostgreSQL refrescada correctamente.');
    }
}

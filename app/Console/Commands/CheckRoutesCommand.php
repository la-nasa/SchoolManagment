<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class CheckRoutesCommand extends Command
{
    protected $signature = 'school:check-routes';
    protected $description = 'Vérifier toutes les routes définies dans l\'application';

    public function handle()
    {
        $routes = Route::getRoutes();
        $tableData = [];

        $this->info('Liste des routes définies:');
        $this->info('===========================');

        foreach ($routes as $route) {
            $tableData[] = [
                'Method' => implode('|', $route->methods()),
                'URI' => $route->uri(),
                'Name' => $route->getName() ?? '-',
                'Action' => $route->getActionName(),
            ];
        }

        $this->table(
            ['Method', 'URI', 'Name', 'Action'],
            $tableData
        );

        $this->info('Total routes: ' . count($tableData));
    }
}

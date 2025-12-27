<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define el schedule de tareas automáticas
     */
    protected function schedule(Schedule $schedule): void
    {
        // Por ahora VACÍO - lo llenaremos después
    }

    /**
     * Registrar comandos de artisan
     */
    protected function commands(): void
    {
        // Cargar comandos personalizados
        $this->load(__DIR__ . '/Commands');
    }
}
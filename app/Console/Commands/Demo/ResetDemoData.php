<?php

namespace App\Console\Commands\Demo;

use App\Domain\User\Enums\RoleEnum;
use Illuminate\Console\Command;

class ResetDemoData extends Command
{
    protected $signature = 'demo:reset {--fresh : Drop all tables and re-run migrations before seeding}';

    protected $description = 'Reset the FlowForge demo database to a clean seeded state';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->call('migrate:fresh', ['--seed' => true]);
        } else {
            $this->call('db:seed');
        }

        $this->newLine();
        $this->info('Demo data is ready.');
        $this->table(
            ['Role', 'Email', 'Password'],
            collect(RoleEnum::cases())->map(fn (RoleEnum $role) => [
                $role->label(),
                strtolower($role->value).'@flowforge.dev',
                'password',
            ])->all()
        );

        return self::SUCCESS;
    }
}
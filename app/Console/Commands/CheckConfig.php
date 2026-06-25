<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:check-config')]
#[Description('Command description')]
class CheckConfig extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}

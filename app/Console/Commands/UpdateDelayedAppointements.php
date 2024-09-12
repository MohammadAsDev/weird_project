<?php

namespace App\Console\Commands;

use App\Enums\AppointementStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateDelayedAppointements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointements:delayed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Non-Acknowledged Commands to Delayed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        error_log("test");
        DB::table('appointements')
        ->where("status" , AppointementStatus::NEED_ACK)
        ->update(["status" => AppointementStatus::DELAYED]);
    }

}

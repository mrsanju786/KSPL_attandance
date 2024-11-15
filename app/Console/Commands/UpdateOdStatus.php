<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OutDoor;
use Carbon\Carbon;
use Auth;
use Log;

class UpdateOdStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:update_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update od status rejected on Rsm amd Cmt side ';

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
     * @return mixed
     */
    public function handle()
    {
        $od = OutDoor::where('from_date',date('Y-m-d'))->where('status',0)->update(['status'=>2]);
        Log::debug('Od Rejected successfully!');
    }
}

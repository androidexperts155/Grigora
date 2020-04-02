<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon;
use App\User;
use App\Notification;

class ClearNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear notifications after 10 days.';

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
        $myDate = Carbon\Carbon::now();
        //echo $myDate;die;
        $notificationRemovedate = date("Y-m-d H:i:s", strtotime($myDate.'-2 days'));
        //echo $notificationRemovedate;die;
        $notifications = Notification::where('created_at', '<=', $notificationRemovedate)->delete();
    }
}

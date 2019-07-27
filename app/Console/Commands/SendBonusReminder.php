<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\reminderBonusMail;
use App\User;
use Carbon\Carbon;

class SendBonusReminder extends Command
{

    public $day;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:bonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder email two days before any bonus date';

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
        list($summary_array,$salary_dates,$bonus_dates)  = app('App\Http\Controllers\AdminController')->reminderHelper();
        foreach($bonus_dates as $b){
            if($b->format('Y-m-d') == Carbon::now()->addDays(2)->format('Y-m-d')){
                $this->day=$b;        
            }
        }
        $Month = Carbon::createFromFormat('Y-m-d', $this->day->format('Y-m-d'))->format('F');
        foreach($summary_array as $arr){
            if($arr["Month: "]==$Month){
                $amount = $arr['Bonus_total: '];
            }
        }
         if($amount != "$0"){
            $user =User::where('is_admin',1)->first();
            \Mail::to($user)->send(new reminderBonusMail($amount));
         }

    }


}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Mail\reminderMail;
use App\Http\Controllers\AdminController;
use Carbon\Carbon;

class SendReminderEmail extends Command
{
    public $day;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:salary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder email two days before any salary date';

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
        foreach($salary_dates as $s){
            if($s->format('Y-m-d') == Carbon::now()->addDays(2)->format('Y-m-d')){
                $this->day=$s;        
            }
        }
        $Month = Carbon::createFromFormat('Y-m-d', $this->day->format('Y-m-d'))->format('F');
        foreach($summary_array as $arr){
            if($arr["Month: "]==$Month){
                $amount = $arr['Salaries_total: '];
            }
        }
        $user =User::where('is_admin',1)->first();
        \Mail::to($user)->send(new reminderMail($amount));
    }
}
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{

    public $summary_array;

    public $salary_dates;
    public $bonus_dates;
    public $salary_day;
    public $bonus_day;
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SendReminderEmail::class,
        Commands\SendBonusReminder::class,
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {


        list($summary_array,$salary_dates,$bonus_dates) =  app('App\Http\Controllers\AdminController')->reminderHelper();
            $this->summary_array = $summary_array;
            $this->salary_dates = $salary_dates;
            $this->bonus_dates = $bonus_dates;

            

        $schedule->command('send:salary')->daily()->when(function () use($salary_dates) {
            foreach($salary_dates as $s){
                if($s->format('Y-m-d') == Carbon::now()->addDays(2)->format('Y-m-d')){ 
                    return (1==1);
                }
            }
        });

        $schedule->command('send:bonus')->daily()->when(function () use($bonus_dates) {
            foreach($bonus_dates as $b){
                if($b->format('Y-m-d') == Carbon::now()->addDays(2)->format('Y-m-d')){
                    return (1==1);
                }
            }
        });

        // * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

}

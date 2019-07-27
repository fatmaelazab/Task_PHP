<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Validator;
use App\User;
use App\Employee;
use App\Http\Requests;
use JWTAuth;
use JWTAuthException;
use Socialite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use DateTime;
use DateInterval;
use DatePeriod;
use App\Bonus;
use App\Mail\reminderMail;

use Carbon\Carbon;

class AdminController extends Controller
{
    public $day;
        /**
     * @var User
     */
    private $user;
    /**
     * UserController constructor.
     * @param User $user
     */
    public function __construct(User $user){
        $this->user = $user;
    }


    // Admin retrieves all the employees
    public function getEmployees(){
        $employees = Employee::all();
        if($employees->isEmpty()){
            return response()->json(['status'=>true,'message'=>"There are no employees yet"],200); 
        }
        return response()->json(['status'=>true,'data'=>$employees],200);
    }


    // Admin retrieves a particular employee by id
    public function getEmployee($id){
        if(Employee::find($id)==null){
            return response()->json(['status'=>true,'message'=>"There is no employee with this id"],200); 
        }
        else{
            $employee = Employee::find($id)->first();
            return response()->json(['status'=>true,'data'=>$employee],200);
        }
    }


    // Admin adds a new employee
    public function addEmployee(Request $request){
        $validator = Validator::make($request->all(),[
            'name'=>'required|string|max:255',
            'email'=>'required|string|max:255|email|unique:employees',
            'department'=>'required|string|max:255',
            'base_salary'=>'required|numeric'
        ]);
        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],400);
        }
        $employee =Employee::where('email',$request->get('email'))->first();
        if($employee){
            return response()->json(['status'=>false,'message'=>'Employee with the requested email already exists'],400);
        }
        if(!$request->get('monthly_bonus')){
            $monthly_bonus = 0.1 * ($request->get('base_salary'));
        }
        else{
            $monthly_bonus = ($request->get('monthly_bonus')/100)* ($request->get('base_salary'));
        }
        $employee = Employee::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'department' => $request->get('department'),
            'base_salary' => $request->get('base_salary'),
            'monthly_bonus' => $monthly_bonus
        ]);
        $employee->save();
        return response()->json(['status'=>true,'message'=>'Employee added successfully','data'=>$employee],200);
    }


    // Admin updates an already existing employee record
    public function updateEmployee(Request $request,$id){
        $employeeUpdate  = Employee::find($id);
        if($employeeUpdate==null){
            return response()->json(['status'=>true,'message'=>'There is no employee with this ID'],200);
        }
        $input = $request->all();
        if($request->get('monthly_bonus')){
            $employeeUpdate->update($input);
            $monthly_bonus = ($request->get('monthly_bonus')/100)* ($employeeUpdate->base_salary);
            $employeeUpdate->update([
                'monthly_bonus' =>  $monthly_bonus
            ]);
        }
        else{
            $employeeUpdate->update($input);
        }
       
        $employeeUpdate->save();
        
        return response()->json(['status'=>true,'message'=>'Employee updated successfully','data'=>$employeeUpdate],200);
    }

    // Admin deletes an employee by id
    public function deleteEmployee($id){
        $employeeDelete  = Employee::find($id);
        if($employeeDelete==null){
            return response()->json(['status'=>true,'message'=>'There is no employee with this ID'],200);
        }
        $employeeDelete->delete();
        return response()->json(['status'=>true,'message'=>'Employee deleted successfully'],200);
    }

    // Admin lists and filters the payment dates for the remainder of this year with the corresponding amount to be paid each month
    public function summary(){
        list($summary_array,$t1,$t2) =$this->helper();
        return response()->json(['status'=>true,'data'=>$summary_array],200);
       
    }
    public function helper(){
        $start_bonus = DB::table('bonuses')->latest()->first();
        if($start_bonus!=null){
            $check = $start_bonus->first_year;
        }
        else{
            $check=0;
        }
        $start    = (new DateTime(Carbon::now()->format("Y-m-d")));
        $end      = (new DateTime(Carbon::now()->endOfYear()->format("Y-m-d")));
        $interval = DateInterval::createFromDateString('1 month');
        $period   = new DatePeriod($start, $interval, $end);
        $result = array();
        $salary_dates = array();
        $bonus_dates = array();
        foreach ($period as $month) {

            $year = $month->format('Y');
            $mth = $month->format('m');
            
            $bonus_date = $year.'-'.$mth.'-15';
            $bonus_d = new DateTime($bonus_date);
            $bonus_day_name = $bonus_d->format('l');

            if($bonus_day_name == "Friday" || $bonus_day_name=="Saturday"){
                
                $bonus_d = Carbon::parse($bonus_d)->next(Carbon::THURSDAY);
                $bonus_payement_day = $bonus_d->format('d');
            }
            else{
                $bonus_payement_day = $bonus_d->format('d');
            }
            $salary_day = Carbon::parse($month)->endOfMonth()->format('d');
            $salary_date = $year.'-'.$mth.'-'.$salary_day;
            $d    = new DateTime($salary_date);
            $day_name = $d->format('l');
            if($day_name == "Friday" || $day_name=="Saturday"){
                $searchDay = 'Thursday';
                $searchDate = new Carbon();
                $timestamp = Carbon::createFromFormat('Y-m-d', $salary_date)->timestamp;
                $lastThursday = Carbon::createFromTimeStamp(strtotime("last $searchDay", $timestamp));
                $Month = $lastThursday->format('F');
                $salaries_payement_day = $lastThursday->format('d');
                $d = $lastThursday;
            }
            else{
                $Month = Carbon::createFromFormat('Y-m-d', $salary_date)->format('F');
                $salaries_payement_day = $d->format('d');

            }


            $salaries = Employee::all()->sum('base_salary');
            $bonus = Employee::all()->sum('monthly_bonus');
            $salaries_total ='$'.$salaries;
            
            if($check==1){
                    $bonus_payement_day="No Bonus this month";
                    $bonus = 0;
                    $first = Bonus::create([
                        'first_year' => 0,
                    ]);
                    $first->save();
                    $check =0;
            }
            $bonus_total ='$'.$bonus;
            $payements_total = '$'.($salaries+$bonus);

            $array = array(
                "Month: " => $Month,
                "Salaries_payment_day: " => $salaries_payement_day,
                "Bonus_payment_day: " => $bonus_payement_day,
                "Salaries_total: "=> $salaries_total,
                "Bonus_total: "=> $bonus_total,
                "Payments_total: "=> $payements_total
            );

            array_push($result,$array);
            array_push($salary_dates,$d);
            array_push($bonus_dates,$bonus_d);
        }
        return [$result,$salary_dates,$bonus_dates];

    }

    public function reminderHelper(){
        list($summary_array,$salary_dates,$bonus_dates) =$this->helper();

        return[$summary_array,$salary_dates,$bonus_dates];

    }


    // Admin decides if the bonus will start to be added from next month
    // or as default the bonus is already added and calculated in the payements
    public function startBonus(Request $request){
        if($request->get("first_year")==1){
            $first = Bonus::create([
                'first_year' => 1,
            ]);
            $first->save();
            return response()->json(['status'=>true,'message'=>'Bonus will be added starting from next month'],200);
        }

    }
}

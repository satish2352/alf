<?php

namespace App\Providers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use App\Models\
{
EmployeesModel,User
}; // Replace YourModel with the actual model you are using

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // view()->composer('*', function ($view) {
        //     //  $dashboard = User::join('tbl_employees', 'users.u_email', '=', 'tbl_employees.email')
        //     //     ->where('users.role_id', session()->get('role_id'))
        //     //     ->select('users.role_id', 'tbl_employees.department_id', 'tbl_employees.employee_name', 'tbl_employees.email')
        //     //     ->get();
        //     $dashboard=EmployeesModel::where('email',session()->get('u_email'))->get();
        //     $view->with('dashboard', $dashboard);
        // });


        DB::listen(function ($query) {
            Log::info("Query Time: {$query->time} ms; SQL: {$query->sql}; Bindings: " . json_encode($query->bindings));
        });
    }
}

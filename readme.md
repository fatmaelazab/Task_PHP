
# Getting started

Clone the repository

    git clone https://github.com/fatmaelazab/PHP_Task.git

Switch to the repo folder

    cd PHP_Task

Install all the dependencies using composer

    composer install

Copy the example env file and make the required configuration changes in the .env file

    cp .env.example .env
    
As the API is using an email server, you should make the configuration for the following fields in the .env file

    MAIL_DRIVER=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=reminder.noreply123@gmail.com 
    MAIL_PASSWORD=ladlfwckkjdafuyj
    MAIL_ENCRYPTION=tls

You could change the MAIL_USERNAME and MAIL_PASSWORD fields with the email you want to use and its application key, but for this API, I have already created one so you could use it right away.
Also go to /config/mail.php and update the following field

        'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'reminder.noreply123@gmail.com'),
        'name' => env('MAIL_FROM_NAME', 'Reminder'),
    ],

Generate a new application key

    php artisan key:generate

Generate a new JWT authentication secret key

    php artisan jwt:secret

Run the database migrations (**Set the database connection in .env before migrating**)

    php artisan migrate

Start the local development server

    php artisan serve

You can now access the server at http://localhost:8000


## To Consume the API

You can use [Postman](https://www.getpostman.com/) to test the API with the following routes:

Routes for the authentication process(Register, login, authenticated user info, logout) 
    
    http://127.0.0.1:8000/api/auth/register

    http://127.0.0.1:8000/api/auth/login

    http://127.0.0.1:8000/api/auth/me

    http://127.0.0.1:8000/api/auth/logout


Admin gets all employees

    http://127.0.0.1:8000/api/employees

Admin gets the details of a specific employee

    http://127.0.0.1:8000/api/employee/{id}

Admin adds a new employee

    http://127.0.0.1:8000/api/addEmployee

Admin updates the info of a specific employee

    http://127.0.0.1:8000/api/updateEmployee/{id}

Admin deletes a specific employee

    http://127.0.0.1:8000/api/deleteEmployee/{id}

Admin gets the payment dates for the remainder of this year with the corresponding amount to be paid each month

    http://127.0.0.1:8000/api/summary

Admin decides if the bonus will start to be added from next month or as default the bonus is already added and calculated in the payements

    http://127.0.0.1:8000/api/startBonus



## Also the API contains a schedule cron job, so to start the Laravel Scheduler

Let’s setup the Cron Jobs to run automatically without initiating manually by running the command. To start the Laravel Scheduler itself, we only need to add one Cron job which executes every minute. Go to your terminal, open another tab than the tab of the alrealy running server, cd into your project and run this command.

    crontab -e

This will open the server Crontab file, paste the code below into the file, save and then exit.

    * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1

Do not forget to replace /path/to/artisan with the full path to the Artisan command of your Laravel Application.

So the schedule is now able to run automatically without running this command manually.
    
    php artisan schedule:run


    
   






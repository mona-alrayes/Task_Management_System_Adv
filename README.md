# Task Management System Advanced

## Description
This project is a ** Task Management System Advanced ** built with **Laravel 10** that provides a **RESTful API**  for managing task system  with different roles and dealing with task depandances so they can do their job effiently . The system Has **JWT Authentication** for ( Register , login , logout , refresh)  . The project follows **repository design patterns** and incorporates **clean code** and **refactoring principles**. also adding **security level** to protect the system such as protecting against famous attacks such as XSS,SQl Injection, CSRF ,Bruce-Force 
with adding applity to scan files for viruces and safe uploading attachements to the system
**Error log System** also added to the system to deal with errors and analizing them for better performance for the system 
**rate limiting** for routes to protect against DDos attacks 
**Task Log System** for watching changes that's happening on task system 

### Key Features:
- **CRUD Operations**: Create, read, update, delete and restore and softdelets methods for tasks with advanced filters on searching on tasks and method for adding attachements on tasks.
- **CRUD Operations**: Create, read, update, delete , restore for Users .
- **CRUD Operations**: Create, read, update, delete , restore for comments as morph relationship .
- **Operations for Authentication** : register, login , logout and refresh .
- **Feature Testing** : testing for Users , Authentication , Tasks , Comments , Daily Tasks Reports and Error Log .
- **Api Testing in Postman** : api testing for all routes in postman in folder of testing .
- **Testing** : all feature testing is applied in sqlite in memory please check file **phpnit.xml** then uncomment these two lines if they are commented
- <env name="DB_CONNECTION" value="sqlite"/>
  <env name="DB_DATABASE" value=":memory:"/>.

- **Repository Design Pattern**: Implements repositories and services for clean separation of concerns.
- **Form Requests**: Validation is handled by custom form request classes.
- **API Response Service**: Unified responses for API endpoints.
- **Pagination**: Results are paginated for better performance and usability.
- **Seeders**: Populate the database with initial data for testing and development.

### Technologies Used:
- **Laravel 10**
- **PHP**
- **MySQL**
- **XAMPP** (for local development environment)
- **Composer** (PHP dependency manager)
- **Postman Collection**: Contains all API requests for easy testing and interaction with the API.

---

## Installation

### Prerequisites

Ensure you have the following installed on your machine:
- **XAMPP**: For running MySQL and Apache servers locally.
- **Composer**: For PHP dependency management.
- **PHP**: Required for running Laravel.
- **MySQL**: Database for the project
- **Postman**: Required for testing the requestes.

### Steps to Run the Project

1. Clone the Repository  
   ```bash
  https://github.com/mona-alrayes/Task_Management_System_Adv
  
2. Navigate to the Project Directory
   ```bash
   cd Task_Management_System_Adv
3. Install Dependencies
   ```bash
   composer install
4. Create Environment File
   ```bash
   cp .env.example .env
   Update the .env file with your database configuration (MySQL credentials, database name, etc.).
5. Generate Application Key
    ```bash
    php artisan key:generate
6. Run Migrations
    ```bash
    php artisan migrate
7. Run this command to generate JWT Secret
   ```bash
   php artisan jwt:secret
   
9. Seed the Database
    ```bash
    php artisan db:seed
10. Run the Application
    ```bash
    php artisan serve
11. Interact with the API and test the various endpoints via Postman collection 
    Get the collection from here: https://documenter.getpostman.com/view/34416184/2sAXxWYTj4
    
13. add VIRUSTOTAL_API_KEY : VirusTotal key here
    ```bash
VIRUSTOTAL_API_KEY=dc5a8210ae5ec5d7cd1b8e8cfdae16a790e6297112453053dfc78697cb8da43f


<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

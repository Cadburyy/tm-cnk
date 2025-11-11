
## 📄 Setup Instructions

1. Turn on XAMPP (Apache and MySQL))

2. Install Laravel 12

   `composer create-project laravel/laravel example-app`

3. Install Spatie Permission Package

   `composer require spatie/laravel-permission`

4. Publish the package’s configuration and migration files

   `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`

5. Run migration table

   `php artisan migrate`

6. Install Laravel UI and generate auth scaffolding

   `composer require laravel/ui
    php artisan ui bootstrap --auth`

7. Compile the frontend

   `npm install
    npm run build`

8. Run the seeder with:

    `php artisan db:seed --class=PermissionTableSeeder`
    `php artisan db:seed --class=CreateAdminUserSeeder`

9. Run the frontend dev server:

    `npm run dev`

10. Start the Laravel development server:

    `php artisan serve`

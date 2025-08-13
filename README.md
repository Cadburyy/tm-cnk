
## 📄 Setup Instructions

1. Turn on XAMPP (Apache and MySQL))
2. Run `composer install`.
3. Run `npm install`.
4. Run `php artisan migrate`.
5. Run the seeder with:

    `php artisan db:seed --class=PermissionTableSeeder`
    `php artisan db:seed --class=CreateAdminUserSeeder`

6. Run the frontend dev server:

    `npm run dev`

7. Start the Laravel development server:

    `php artisan serve`
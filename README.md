########################################
# Transaction Management (TM) — Setup
########################################

# 1. Go to your project directory
cd path/to/tm

# 2. Install backend dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate Laravel app key
php artisan key:generate

########################################
# 5. Create your database in phpMyAdmin:
#    Database name example: tm_db
#
# Then update .env with:
#    DB_DATABASE=tm_db
#    DB_USERNAME=root
#    DB_PASSWORD=
########################################

# 6. Run migrations and seed roles/users
php artisan migrate:fresh --seed

# 7. Install frontend dependencies
npm install

# 8. Build frontend assets
npm run build

# 9. Start Laravel server
php artisan serve

# 10. Open a NEW terminal and start Vite
npm run dev

########################################
# Application will run at:
# http://localhost:8000
########################################

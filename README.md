# --- 1. SETUP: GO TO PROJECT DIRECTORY ---
cd path/to/tm

# --- 2. INSTALL BACKEND DEPENDENCIES ---
composer install

# --- 3. CONFIGURE ENVIRONMENT FILE ---
cp .env.example .env

# --- 4. GENERATE LARAVEL APPLICATION KEY ---
php artisan key:generate

# --- 5. DATABASE CONFIGURATION (REQUIRES MANUAL DB CREATION FIRST) ---
# NOTE: Before running the next step, you must manually create the database.
# Example command to update .env after creating 'tm_db' in phpMyAdmin:
# sed -i 's/DB_DATABASE=laravel/DB_DATABASE=tm_db/' .env
# sed -i 's/DB_USERNAME=root/DB_USERNAME=root/' .env
# sed -i 's/DB_PASSWORD=/DB_PASSWORD=/' .env

# --- 6. RUN MIGRATIONS AND SEED INITIAL DATA ---
php artisan migrate:fresh --seed

# --- 7. INSTALL FRONTEND DEPENDENCIES ---
npm install

# --- 8. BUILD FRONTEND ASSETS ---
npm run build

# --- 9. START LARAVEL SERVER (NEEDS SEPARATE TERMINAL) ---
# php artisan serve

# --- 10. START VITE SERVER (NEEDS ANOTHER SEPARATE TERMINAL) ---
# npm run dev

# --- ACCESS: http://localhost:8000 ---
echo "Setup steps completed. You must now run 'php artisan serve' and 'npm run dev' in separate terminals."

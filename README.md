⚙️ Project Setup Guide
This guide will walk you through setting up the Transaction Management (TM) application.

1. Environment and Dependencies
Ensure you have PHP (with Composer) and Node.js (with npm) installed.

Go to your project directory:

Bash

cd path/to/tm
Install PHP dependencies:

Bash

composer install
Prepare the environment file:

Bash

cp .env.example .env
Generate the application key:

Bash

php artisan key:generate
2. Database Configuration
Create your database (e.g., tm_db) in your database manager (like phpMyAdmin).

Update your .env file with the correct connection details:

Code snippet

# .env File Snippet
DB_DATABASE=tm_db
DB_USERNAME=root
DB_PASSWORD=
Run migrations and seed the database (this resets all tables):

Bash

php artisan migrate:fresh --seed
3. Frontend Setup and Build
Install Node dependencies:

Bash

npm install
Build frontend assets for production:

Bash

npm run build
4. Running the Application
You must run the backend and the frontend processes simultaneously in two separate terminals.

Terminal 1: Start Laravel server:

Bash

php artisan serve
Terminal 2: Start Vite development server:

Bash

npm run dev
The application will be running at: http://localhost:8000

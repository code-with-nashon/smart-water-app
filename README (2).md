# Smart Water Metering with Predictive Analysis and Leak Detection

A web application developed using Laravel, designed to monitor water consumption, detect leaks, provide predictive analysis, and offer comprehensive management tools for both end-users and administrators.

##  Features

### User Dashboard
* Real-time display of water consumption for assigned meters.
* **Automated Leak Detection Alerts (Red Alert):** Notifies users of potential leaks based on unusual consumption patterns (e.g., high consumption today vs. very low yesterday).
* **High Consumption Alerts (Orange Alert):** Warns users when daily consumption exceeds a personalized threshold.
* Interactive charts displaying historical and predictive consumption trends.
* Date range filtering for consumption data.
* Option to export consumption data to CSV.

### Admin Panel
* System-wide overview of total users, total meters, and global leak alerts.
* **User Management:**
    * View and manage all registered users.
    * Assign and unassign water meters to users.
    * **Reset user passwords** to a default value (e.g., 'password').
    * Delete user accounts and associated data.
* **Meter Management:**
    * View, add, edit, and delete water meter records.
    * Update meter location, installation date, and notes.
* **Reporting & Analytics:**
    * **Overall Consumption Report:** Detailed report with system-wide daily consumption charts and consumption breakdown by meter for a selected period.
    * **Anomaly Detection Report:** Identifies and lists meters with potential leaks or unusually high consumption, providing details for investigation.

## 🛠️ Technology Stack

* **Backend:** PHP 8.2+ with Laravel Framework (v10/v11 - specify your version if known)
* **Database:** MySQL
* **Frontend:** Blade Templating Engine, Tailwind CSS, JavaScript (Vanilla JS)
* **Charting:** Chart.js
* **Authentication:** Laravel Breeze
* **API Authentication (Internal):** Laravel Sanctum
* **Package Management:** Composer (PHP), npm (Node.js)
* **Development Environment:** XAMPP

## ⚙️ Installation and Setup (Local)

Follow these steps to get the project up and running on your local machine.

### Prerequisites

* **XAMPP:** (or equivalent Apache/Nginx, MySQL, PHP environment) - Ensure Apache and MySQL are running.
* **PHP:** v8.2 or higher (matching your XAMPP installation).
* **Composer:** [https://getcomposer.org/](https://getcomposer.org/)
* **Node.js & npm:** [https://nodejs.org/](https://nodejs.org/)

### Steps

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/your-username/smart-water-app-laravel.git](https://github.com/your-username/smart-water-app-laravel.git)
    cd smart-water-app-laravel
    ```
2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```
3.  **Create and configure your `.env` file:**
    * Copy the `.env.example` file to `.env`:
        ```bash
        cp .env.example .env
        ```
    * Open `.env` and configure your database connection:
        ```
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=smart_water_app_db # Your database name
        DB_USERNAME=root             # Your MySQL username
        DB_PASSWORD=                 # Your MySQL password (often empty for XAMPP root)
        ```
    * Generate an application key:
        ```bash
        php artisan key:generate
        ```
4.  **Run migrations and seed the database:**
    * This will create all necessary tables and populate them with initial data (admin user, test user, meters, and historical water readings).
    ```bash
    php artisan migrate:fresh --seed
    ```
    * **Note:** If you encounter foreign key errors during migration, ensure your `create_meters_table.php` migration file has an earlier timestamp than `create_water_readings_table.php` in `database/migrations/`. Also, ensure `string('meter_id', 255)` is consistent in both migrations.

5.  **Install Node.js dependencies and compile assets:**
    ```bash
    npm install
    npm run dev # For development, or npm run build for production
    ```
6.  **Start the Laravel development server:**
    ```bash
    php artisan serve
    ```
7.  **Access the application:**
    * Open your web browser and navigate to: `http://127.0.0.1:8000/`

### Default Credentials

* **Admin User:**
    * Email: `admin.smartwater@gmail.com`
    * Password: `password`
* **Regular User:**
    * Email: `user.smartwater@gmail.com`
    * Password: `password`
* **Fanuel (Test User):**
    * Email: `fanuel@example.com` (or whatever email you used)
    * Password: `password`

### 💡 Generating Test Data / Triggering Alerts

The `php artisan water:generate-daily-readings` command generates random daily consumption data. To specifically trigger alerts for demonstration:

* **Red Alert (Potential Leak):** Manually insert data into `water_readings` table via phpMyAdmin:
    * For a specific meter (e.g., `SMW001`) and user (e.g., `user_id = 3` for Fanuel):
    * Set `consumption_liters` for **yesterday** to `< 100` (e.g., `80`).
    * Set `consumption_liters` for **today** to `> 500` (e.g., `600`).
* **Orange Alert (High Consumption):** Ensure a user's `daily_consumption_alert_threshold` is low (e.g., `45` for Fanuel) and their `current_daily_consumption` is higher than this threshold.

### 🤝 Contributing

Contributions are welcome! If you find a bug or have a feature suggestion, please open an issue or submit a pull request.

### 📄 License

This project is open-sourced under the [MIT License](https://opensource.org/licenses/MIT).

---
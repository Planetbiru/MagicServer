# MagicServer

**MagicServer** is a lightweight and portable server package that includes **Apache**, **PHP**, **MariaDB**, and **Redis**, pre-configured to run [MagicAppBuilder](https://github.com/planetbiru/magicappbuilder) smoothly on Windows systems.

---

# Screenshot

![MagicServer Screenshot](https://raw.githubusercontent.com/Planetbiru/MagicServer/refs/heads/main/screenshoot.png)

## 🚀 Features

---

## ✨ Features

* ✅ **Portable** – no installation required
* ✅ **Zero configuration**
* ✅ **Auto-installs MagicAppBuilder**
* ✅ **Rebuilds server configuration on each startup**
* ✅ **PlanetbiruServer GUI** – Easy-to-use Windows interface to manage services
* ✅ **Includes**:
  * **Apache** 2.4.64
  * **PHP** 8.4.10
  * **MariaDB** 11.8.2
  * **Redis** 5.0.14.1 (Windows build)

---

## 📁 Folder Structure

```txt
MagicServer/
├── apache/                      # Apache HTTP Server binaries and configuration files
├── config/                      # Generated config files (e.g., for Apache, PHP, Redis)
├── data/                        # MariaDB data directory (stores databases)
├── logs/                        # Central log directory (for Apache, MariaDB, Redis, etc.)
├── mysql/                       # MariaDB binaries and supporting files
├── php/                         # PHP runtime and configuration (php.ini, extensions)
├── redis/                       # Redis Server binaries and configuration
├── sessions/                    # PHP session file storage directory
├── tmp/                         # Temporary files (e.g., uploads, caches)
├── www/                         # Web root directory (place your web apps here)
│   ├── __assets/                # Contains common asset files for the root index page
│   ├── MagicAppBuilder/         # Auto-installed MagicAppBuilder (a low-code web platform)
│   ├── phpmyadmin/              # Pre-installed phpMyAdmin for managing MariaDB databases
│   └── index.php                # Index page for the www directory
├── fn.php                       # Common/shared PHP utility functions
├── PlanetbiruServer.exe         # GUI Control Panel to manage services (Apache, MariaDB, Redis)
├── localization.ini             # Language localization file for Planetbiru Server GUI
├── install-magicappbuilder.php  # Installer script for MagicAppBuilder platform
├── install-mariadb.php          # Script to initialize MariaDB system tables (data directory)
├── set-mariadb-password.php     # Script to set or change the MariaDB root password
├── start.php                    # Script to generate config and start Apache, MariaDB, and Redis
└── stop.php                     # Script to gracefully stop Apache, MariaDB, and Redis
```

---

## 🚀 Getting Started

### 1. Download and Extract

Extract MagicServer to any location, e.g.:

```
D:\MagicServer
```

### 2. Open Command Prompt as Administrator

> Required to allow Apache, MariaDB, and Redis to run properly.

```bat
D:
cd MagicServer
```

### 3. Install MariaDB

Run the following to install MariaDB:

```bat
php\php.exe install-mariadb.php
```

### 4. Install MagicAppBuilder

Run the following to download and install the latest version:

```bat
php\php.exe install-magicappbuilder.php
```

### 5. Start the Server

You can start the server using the **PlanetbiruServer** GUI or the command line.

#### A. Using PlanetbiruServer GUI (Recommended)
Double-click **`PlanetbiruServer.exe`** in the root directory. This tool provides:
* **One-Click Control**: Start, stop, or restart Apache, MariaDB, and Redis services.
* **Real-time Monitoring**: View service status and logs directly within the application.
* **Redis Viewer**: Inspect Redis database keys and values in a safe, read-only environment.
* **System Tray Integration**: Minimize the server manager to the system tray for easy access.

#### B. Using Command Line
Alternatively, run the following to start all services and rebuild configurations:

```bat
php\php.exe start.php
```

### 6. Set the MariaDB Root User Password

Once the server is up and running, it's important to set a secure password for the MariaDB `root` user.

To do this, run the following script:

```bat
php\php.exe set-mariadb-password.php
```

By default, this script will set the `root` user's password to `password`. If you'd prefer to use a different password, you can modify the script beforehand by editing the following lines in the `set-mariadb-password.php` file:

```php
$rootPassword = ''; // Current password. Leave empty if no password is set yet.
$newPassword = 'password'; // Desired new password.
```

If you've already executed the script before changing the password, simply fill in the current password in the `$rootPassword` variable and re-run the script. This ensures the password update is applied correctly.

> Tip: Always choose a strong, unique password for database root access to enhance security and prevent unauthorized access.

### 7. Access Your Application and Tools

Open your browser and go to:

* MagicAppBuilder:

  ```
  http://localhost/MagicAppBuilder/
  ```

* phpMyAdmin (MariaDB web interface):

  ```
  http://localhost/phpmyadmin/
  ```

> If you see the error **"Login without a password is forbidden by configuration"**, set a MariaDB root password by running:
>
> ```bat
> php\php.exe set-mariadb-password.php
> ```

### 8. Stop the Server

To stop all services:

```bat
php\php.exe stop.php
```

---

## 🔧 Configuration

Template-based configurations are rebuilt automatically.

| Component | Template File                | Generated File      |
| --------- | ---------------------------- | ------------------- |
| Apache    | `config/httpd-template.conf` | `config/httpd.conf` |
| PHP       | `config/php-template.ini`    | `php/php.ini`       |
| MariaDB   | `config/my-template.ini`     | `config/my.ini`     |
| Redis     | `config/redis-template.conf` | `redis/redis.conf`  |

> 📝 Do **not** edit generated files. Modify the templates instead.

---

## 🛡️ Default Credentials

| Service         | Username        | Password        |
| --------------- | --------------- | --------------- |
| MariaDB         | `root`          | `password`      |
| MagicAppBuilder | `administrator` | `administrator` |
| Your App        | `superuser`     | `superuser`     |

> 🔐 Secure your environment by setting strong passwords.

---

### 🔐 Changing the MariaDB Root Password via phpMyAdmin

To change the root password securely using phpMyAdmin:

1. Open [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/) in your browser.
2. Log in with the default credentials:

   * Username: `root`
   * Password: `password`
3. Click the **"User accounts"** tab on the top menu.
4. Find the user **`root`@`localhost`** and click **"Edit privileges"**.
5. Scroll to the **"Change password"** section.
6. Enter your **new secure password** twice, and click **Go**.
7. Repeat the same steps for any other **`root`** user entries with a different Host name.
8. After changing the password, you can log out and log in again using the new credentials.

> ⚠️ If you update the root password, make sure any application (including MagicAppBuilder) that connects to MariaDB is updated accordingly.

---

## 🔑 Forgot MariaDB Password?

If you’ve forgotten the MariaDB `root` password, you can reset it by following these steps. This method temporarily starts MariaDB in a special mode that does not require a password to log in.

1. **Stop the Server (if it’s running)**

   Open Command Prompt and run the `stop.php` script to make sure all services are stopped.

   ```bat
   php\php.exe stop.php
   ```

2. **Open Command Prompt as Administrator**

   Make sure you are in the `MagicServer` directory.

3. **Run MariaDB in Safe Mode**

   Run the following command to start MariaDB without access control checks. Keep this Command Prompt window open.

   ```bat
   mysql\bin\mysqld.exe --skip-grant-tables --skip-networking
   ```

4. **Open a New Command Prompt (as Administrator)**

   Open a **second** Command Prompt window and navigate back to the `MagicServer` directory.

5. **Log In to MariaDB Without a Password**

   Type the following command to log in as `root`:

   ```bat
   mysql\bin\mysql.exe -u root
   ```

6. **Reset the Password**

   Once inside the MariaDB console, run the following SQL commands to set a new password. Replace `your_new_password` with the password you want.

   ```sql
    FLUSH PRIVILEGES;
    CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED BY 'your_new_password';
    CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY 'your_new_password';
    CREATE USER IF NOT EXISTS 'root'@'::1' IDENTIFIED BY 'your_new_password';
    CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY 'your_new_password';
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'::1' WITH GRANT OPTION;
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
    QUIT;
   ```

7. **Stop MariaDB Safe Mode**

   Close the first Command Prompt window (where you ran `mysqld.exe --skip-grant-tables`).

8. **Restart the Server Normally**

   Now you can start all services again as usual.

   ```bat
   php\php.exe start.php
   ```

Your `root` password has now been successfully reset.

---

## 🆘 Repairing a Corrupt MariaDB Installation

If MariaDB fails to start, it might be due to corrupted data files, often caused by an improper shutdown (e.g., a system crash or closing the command window without running `stop.php`). You can attempt to recover your data by starting MariaDB in InnoDB's force recovery mode.

> **Warning:** These steps are for emergency recovery. While this procedure can save your data, it's possible some recent transactions might be lost. Always back up your data regularly.

1.  **Stop All Services**

    Ensure all server components are fully stopped.
    ```bat
    php\php.exe stop.php
    ```

2.  **Enable Force Recovery Mode**

    *   Open the MariaDB configuration template: `config/my-template.ini`.
    *   Add the following line under the `[mysqld]` section:
        ```ini
        innodb_force_recovery = 1
        ```
    *   Save the file. If MariaDB still fails to start in the next step, you can try increasing this value from 1 up to 6. Use the lowest value that allows the server to start.

3.  **Start the Server in Recovery Mode**

    This command will regenerate the configuration and attempt to start MariaDB. In recovery mode, InnoDB tables will be read-only.
    ```bat
    php\php.exe start.php
    ```
    If MariaDB starts successfully, proceed immediately to the next step.

4.  **Backup Your Databases (Critical Step)**

    With the server running, you must export all your databases to SQL files.
    *   Go to **phpMyAdmin** at `http://localhost/phpmyadmin/`.
    *   For each of your important databases, select it, go to the **"Export"** tab, and click **"Export"** to save the `.sql` file.

5.  **Clean Up and Re-initialize**

    Once you have backed up your data, you need to reset MariaDB to a clean state.
    *   Stop the server:
        ```bat
        php\php.exe stop.php
        ```
    *   Remove the `innodb_force_recovery` line from `config/my-template.ini`.
    *   Delete the old, corrupt data directory: `data/mysql`.
    *   Re-initialize MariaDB to create a fresh data directory:
        ```bat
        php\php.exe install-mariadb.php
        ```

6.  **Restart and Restore**

    *   Start the server normally:
        ```bat
        php\php.exe start.php
        ```
    *   Go back to **phpMyAdmin**, create new empty databases with the same names as before, and use the **"Import"** tab to restore your data from the `.sql` files you saved.

Your MariaDB server should now be running with your data restored.

---

## 🔐 Setting a Redis Password

For enhanced security, it is highly recommended to set a password for your Redis server. By default, Redis is configured without a password.

1.  **Stop the Server**

    If the server is running, stop it first to prevent any issues.
    ```bat
    php\php.exe stop.php
    ```

2.  **Edit the Redis Template**

    Open the Redis configuration template file located at `config/redis.windows-template.conf`.

3.  **Set the Password**

    Find the line `# requirepass foobared`. Uncomment it (remove the `#`) and replace `foobared` with your own strong, secure password.
    ```conf
    requirepass your_strong_password_here
    ```

4.  **Restart the Server**

    Run the start script. This will regenerate `redis/redis.windows.conf` from the template and start the server with the new password requirement.
    ```bat
    php\php.exe start.php
    ```

5.  **Update Your Application**

    Don't forget to update the Redis configuration in your application (e.g., in MagicAppBuilder's configuration files) to include the new password, so it can connect to the Redis server.

---

## ⚙️ Compatibility

* ✅ Windows 10 and 11
* ✅ PHP 7.x and 8.x supported
* ✅ MagicAppBuilder v1.12.0+
* ✅ Redis for Windows (Memurai / Microsoft port)

---

## 🛠 Included Tools

| Tool        | Description                                    | Path                                         |
| ----------- | ---------------------------------------------- | -------------------------------------------- |
| PHP CLI     | Run PHP scripts from the command line          | `php\php.exe`                                |
| MariaDB CLI | MySQL-compatible database client               | `mysql\bin\mysql.exe`                        |
| Redis CLI   | Command-line interface for Redis               | `redis\redis-cli.exe`                        |
| phpMyAdmin  | Web-based interface for MariaDB administration | Accessible at `/phpmyadmin/` in your browser |

---

## 📜 License

Licensed under the [MIT License](LICENSE), except for bundled components that use their respective open-source licenses.

---

## 🙏 Acknowledgements

MagicServer includes:

* [Apache HTTP Server](https://httpd.apache.org/)
* [PHP](https://www.php.net/)
* [MariaDB](https://mariadb.org/)
* [Redis (Windows Build)](https://github.com/microsoftarchive/redis/releases)
* [MagicAppBuilder](https://github.com/planetbiru/magicappbuilder)

---

💡 Happy building with **MagicAppBuilder + Redis** on MagicServer! 🚀

# Video

https://www.youtube.com/watch?v=sF63LxBJPZI 

# MagicServer Version 0.0.0

Date: July 28th, 2025

## ✨ Features

* **Redis Support**
  MagicServer now supports Redis for session storage and caching features.

* **phpMyAdmin Integration**
  phpMyAdmin is included by default and accessible via the `www/phpMyAdmin` directory for easier database management through the browser.

* **MariaDB Installer Script**
  A new `install-mariadb.php` script is available to automatically initialize MariaDB's system tables and data directory.

* **Set Root Password Script**
  Easily set or change the MariaDB `root` user password using the new `set-mariadb-password.php` script.

* **Safer Installation**
  The installer no longer deletes the `MagicAppBuilder` folder during installation to prevent accidental data loss.

* **Install Script Renamed**
  The original `install.php` has been renamed to `install-magicappbuilder.php` for improved clarity and consistency.

* **Redis PHP Extension Added**
  The Redis PHP extension is bundled by default, enabling Redis-based features such as session storage and caching to work out of the box.

* **Custom Redis Data Directory Support**
  Redis is now configured to store its data (e.g., `dump.rdb`) in a dedicated directory under the install path: `${INSTALL_DIR}/data/redis`.
  This improves data separation and makes it easier to back up or relocate Redis data alongside other application components.

  The Redis configuration templates (`redis.windows-service-template.conf` and `redis.windows-template.conf`) have been updated to include:

  ```conf
  dir "${INSTALL_DIR}/data/redis"
  ```

  During setup, the **installer and starter scripts** will automatically generate the actual Redis configuration files (`redis.windows-service.conf` and `redis.windows.conf`) by replacing `${INSTALL_DIR}` with the appropriate installation path.
  These scripts also ensure the target folder exists and is writable by the Redis service, ensuring seamless startup and persistence.


# MagicServer Version 0.0.1

Date: October 1st, 2025

## ✨ Enhancements

### Index Page for `www` Directory

Previously, accessing the server's root URL (`http://localhost/`) would automatically redirect to the `/MagicAppBuilder/` application.

This version introduces a new index page at the root. Now, visiting `http://localhost/` displays a user-friendly list of all available tools and applications within the `www` directory, such as:
- **MagicAppBuilder**
- **phpMyAdmin**
- Any other custom web applications you've added.

This improves discoverability and makes it easier to navigate between different projects hosted on MagicServer.

### Direct Startup Navigation to MagicAppBuilder

The `start.php` script has been updated to improve the user experience. Previously, running the start script would open the browser to the server root (`http://localhost/`).

Now, it automatically opens the browser directly to `http://localhost/MagicAppBuilder/`, allowing you to get started with the main application immediately after server startup.

# MagicServer Version 0.0.2

Date: October 13rd, 2025

## 📖 Documentation Updates

This release focuses on enhancing the `README.md` with critical troubleshooting and security guides to improve user self-sufficiency and server management.

### Added MariaDB Password Reset Guide

A comprehensive, step-by-step guide has been added to the `README.md` for resetting a forgotten MariaDB `root` password. This procedure involves:
1.  Stopping the server.
2.  Restarting MariaDB in safe mode (`--skip-grant-tables`).
3.  Connecting without a password to reset the `root` user's credentials.
4.  Restarting the server normally.

This ensures users can regain access to their database without losing data.

### Added MariaDB Corruption Repair Guide

Instructions are now available for recovering a corrupt MariaDB installation, which can happen after an improper shutdown. The guide details how to use `innodb_force_recovery` mode to:
1.  Start the server in a read-only state.
2.  Back up all databases using a tool like phpMyAdmin.
3.  Re-initialize the MariaDB data directory.
4.  Restore the databases from the backups.

This provides a clear recovery path for otherwise catastrophic data corruption.

### Added Redis Password Setup Guide

To improve security, a new section in the `README.md` explains how to set a password for the Redis server. The process involves:
1.  Editing the `config/redis-template.conf` file.
2.  Uncommenting and setting the `requirepass` directive.
3.  Restarting the server to apply the new configuration.

This helps users secure their Redis instance from unauthorized access.

## MagicServer Version 0.0.3

Date: October 23rd, 2025

## ✨ Improvements

### MagicServer Updater

A new script, `update-magicserver.php`, has been introduced to streamline the update process. This robust command-line tool **automates the updating of MagicServer** to the latest version.

Key features of the updater include:
- **Automatic Updates**: Fetches and installs the latest release directly from the official **GitHub repository**.
- **Service Management**: Automatically **stops all running services** (Apache, MariaDB, Redis) before the update and ensures none are running during the process.
- **User Confirmation**: Displays the new version to be installed and **prompts for user confirmation** before proceeding.
- **Safe Backup & Recovery**: Creates a **full backup of the existing installation** (excluding user data and configurations) before applying any changes. In case of an error, the updater will automatically **roll back to the previous version**, ensuring zero corruption.
- **PHP Runtime Update**: Intelligently handles updates to the **PHP runtime itself** by using a separate helper script, avoiding file locking issues on Windows.
- **Data Protection**: **Safeguards user data and configurations** by skipping sensitive directories and files (e.g., `www/`, `data/`, `mysql/`, `logs/`, and main configuration files like `httpd.conf` and `my.ini`) during the update process. Configuration template files (like `httpd-template.conf`) will still be updated to ensure you receive the latest default settings.

## MagicServer Version 1.0.0

Date: October 30th, 2025

## ✨ Features

### PlanetbiruServer GUI Control Panel

This version marks a major milestone with the introduction of **`PlanetbiruServer.exe`**, a comprehensive Graphical User Interface (GUI) designed to simplify server management on Windows.
- **One-Click Service Control**: Easily start, stop, or restart Apache, MariaDB, and Redis from a centralized dashboard.
- **Port Management**: Configure service ports (e.g., 80, 3306, 6379) directly within the app without editing configuration files manually.
- **Integrated Database Tools**: Built-in functionality to change MariaDB root passwords, featuring a "Force Reset" mode for recovery.
- **Task Scheduler**: A new interface to add, edit, or delete cron-like scheduled jobs and commands.
- **System Tray Integration**: Ability to minimize the server manager to the system tray with a quick-access context menu.
- **Auto-Start Options**: Settings to run the application on Windows startup and automatically start services.

### Multi-language Localization

To cater to a global audience, the GUI now supports full localization via the **`localization.ini`** file.
- **Wide Language Support**: Includes translation for 11 languages: English, Indonesian, Malay, Javanese, Sundanese, Chinese (Simplified), Japanese, Korean, Hindi, Arabic, and Urdu.
- **RTL Support**: Proper layout handling for Right-to-Left languages like Arabic and Urdu.
- **User Customizable**: Users can easily add new languages or modify existing ones by updating the INI file.

### 🔧 Other Improvements

- Added `localization.ini` to the root directory to manage GUI display strings.

## MagicServer Version 1.0.1

Date: May 2nd, 2026

## 🛠 Bug Fixes & Improvements

### Service Management

* **Improved Monitoring**: When the user starts or stops a service (Apache, MariaDB, Redis), the control panel immediately updates the UI while still checking the port usage in the background. If the action fails, the control panel reverts the UI update accordingly. This eliminates unnecessary waiting time and enhances user convenience.
* **MariaDB Installation**: Improved the MariaDB installation process to correctly handle cases where the data directory does not yet exist.

### PlanetbiruServer GUI

* **Redis Viewer (Read-only)**: Added a built-in Redis data inspector to view keys, types, values, and TTL without needing external tools.
* **Log Viewer Fixes**: Resolved an issue where the log view would stop auto-scrolling when output was generated too rapidly.

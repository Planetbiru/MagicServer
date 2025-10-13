# MagicServer Version 0.0.0

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


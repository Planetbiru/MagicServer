# MagicServer Version 0.1.0

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


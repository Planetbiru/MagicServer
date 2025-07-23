# MagicServer Version 0.1.0

## 🎉 What's New

* **Redis Support**
  MagicServer now supports Redis for session storage and caching features.

## 🔧 Changes

* **Safer Installation**
  The installer no longer deletes the `MagicAppBuilder` folder during installation to prevent accidental data loss.


# MagicServer Version 0.2.0

## ✨ What's New

* **phpMyAdmin Integration**
  phpMyAdmin is now included by default and accessible via the `www/phpMyAdmin` directory for easier database management through the browser.

* **MariaDB Installer Script**
  A new `install-mariadb.php` script is added to initialize MariaDB's system tables and data directory automatically.

* **Set Root Password Script**
  Easily set or change the MariaDB `root` user password using the new `set-mariadb-password.php` script.

## 🔧 Changes

* **Install Script Renamed**
  The original `install.php` has been renamed to `install-magicappbuilder.php` for clarity and consistency.




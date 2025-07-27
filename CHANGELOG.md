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



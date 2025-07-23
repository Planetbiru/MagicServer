# MagicServer

**MagicServer** is a lightweight and portable server package that includes **Apache**, **PHP**, **MariaDB**, and **Redis**, pre-configured to run [MagicAppBuilder](https://github.com/planetbiru/magicappbuilder) smoothly on Windows systems.

---

## ✨ Features

* ✅ **Portable** – no installation required
* ✅ **Zero configuration**
* ✅ **Auto-installs MagicAppBuilder**
* ✅ **Rebuilds server configuration on each startup**
* ✅ **Includes**:

  * Apache HTTP Server
  * PHP
  * MariaDB (MySQL-compatible)
  * Redis Server (Windows build)

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
│   └── MagicAppBuilder/         # Auto-installed MagicAppBuilder (a low-code web platform)
├── fn.php                       # Common/shared PHP utility functions
├── index.php                    # Default web entry point (can be replaced with your own app)
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

### 3. Install MagicAppBuilder

Run the following to download and install the latest version:

```bat
php\php.exe install.php
```

### 4. Start the Server

Starts Apache, MariaDB, Redis, and rebuilds configs:

```bat
php\php.exe start.php
```

### 5. Access Your Application

Open your browser and go to:

```
http://localhost/MagicAppBuilder/
```

### 6. Stop the Server

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
| MariaDB         | `root`          | *(empty)*       |
| MagicAppBuilder | `administrator` | `administrator` |
| Your App        | `superuser`     | `superuser`     |

> 🔐 Secure your environment by setting strong passwords.

---

## ⚙️ Compatibility

* ✅ Windows 10 and 11
* ✅ PHP 7.x and 8.x supported
* ✅ MagicAppBuilder v1.12.0+
* ✅ Redis for Windows (Memurai / Microsoft port)

---

## 🛠 Included Tools

* **PHP CLI** — Run PHP scripts
* **MariaDB Client** — via `mysql/bin/mysql.exe`
* **Redis CLI** — via `redis/redis-cli.exe`

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


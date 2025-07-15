# MagicServer

**MagicServer** is a lightweight and portable server package that includes **Apache**, **PHP**, and **MariaDB** (MySQL-compatible), pre-configured to run [MagicAppBuilder](https://github.com/planetbiru/magicappbuilder) smoothly on Windows systems.


## ✨ Features

* ✅ **Portable** – no installation required
* ✅ **Zero configuration**
* ✅ **Auto-installs MagicAppBuilder**
* ✅ **Rebuilds server configuration on each startup**
* ✅ **Includes**:

  * Apache HTTP Server
  * PHP
  * MariaDB (MySQL-compatible)


## 📁 Folder Structure

```txt
MagicServer/
├── apache/              # Apache HTTP Server binaries and configurations
├── config/              # Template files and generated configurations
├── data/                # MariaDB data directory
├── logs/                # Central log directory (Apache, MariaDB, etc.)
├── mysql/               # MariaDB binaries
├── php/                 # PHP runtime environment
├── sessions/            # PHP session file storage
├── tmp/                 # Temporary file directory (e.g., uploads)
├── www/                 # Web root directory (host your app here)
│   └── MagicAppBuilder/ # Auto-installed MagicAppBuilder (low-code platform)
├── fn.php               # Common PHP utility functions
├── index.php            # Default index file (entry point)
├── install.php          # Installer script for MagicAppBuilder
├── start.php            # Script to generate config and start Apache + MariaDB
└── stop.php             # Script to stop Apache + MariaDB
```


## 🚀 Getting Started

### 1. Download and Extract

Extract MagicServer to any location, e.g.:

```
D:\MagicServer
```

### 2. Open Command Prompt as Administrator

> Required to allow Apache and MariaDB to run properly.

1. Click Start → search **"cmd"**
2. Right-click **Command Prompt** → click **"Run as administrator"**
3. Navigate to the server folder:

```bat
D:
cd MagicServer
```

### 3. Install MagicAppBuilder

Run the following command to download and install the latest version of MagicAppBuilder into `www/MagicAppBuilder/`:

```bat
php\php.exe install.php
```

> ⚠️ Required before the first startup.

### 4. Start the Server

To start Apache and MariaDB and regenerate configurations:

```bat
php\php.exe start.php
```

> ℹ️ Config files are rebuilt automatically based on current folder path.

### 5. Access Your Application

Open your browser and go to:

```
http://localhost/MagicAppBuilder/
```

You should now see the MagicAppBuilder interface.

### 6. Stop the Server

To stop all services:

```bat
php\php.exe stop.php
```


## 🔧 Configuration

The system uses template-based configuration. These are rebuilt each time you run `start.php`.

| Component | Template File                | Generated File      |
| --------- | ---------------------------- | ------------------- |
| Apache    | `config/httpd-template.conf` | `config/httpd.conf` |
| PHP       | `config/php-template.ini`    | `php/php.ini`       |
| MariaDB   | `config/my-template.ini`     | `config/my.ini`     |

> 📝 Do **not** edit generated files directly. Modify the template files instead.


## 🛡️ Default Credentials

| Service         | Username        | Password        |
| --------------- | --------------- | --------------- |
| MariaDB         | `root`          | *(empty)*       |
| MagicAppBuilder | `administrator` | `administrator` |
| Your App        | `superuser`     | `superuser`     |

> 🔐 It is strongly recommended to set a password for the `root` user.


## ⚙️ Compatibility

* ✅ Windows 10 and 11
* ✅ PHP 7.x and 8.x supported
* ✅ Works with MagicAppBuilder v1.12.0+


## 🛠 Included Tools

* **PHP CLI** — Run PHP scripts like `install.php`, `start.php`, `stop.php`
* **MariaDB Client** — Accessible via `mysql/bin/mysql.exe`


## 📜 License

This project is licensed under the [MIT License](LICENSE), except for bundled third-party components which retain their respective open-source licenses.

## 🙏 Acknowledgements

MagicServer bundles the following open-source software:

* [Apache HTTP Server](https://httpd.apache.org/)
* [PHP](https://www.php.net/)
* [MariaDB](https://mariadb.org/)
* [MagicAppBuilder](https://github.com/planetbiru/magicappbuilder)


Happy developing with **MagicAppBuilder**! 🚀

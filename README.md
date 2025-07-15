# MagicServer

**MagicServer** is a lightweight and portable server package that includes **Apache**, **PHP**, and **MariaDB** (MySQL-compatible), pre-configured to run [MagicAppBuilder](https://github.com/planetbiru/magicappbuilder) smoothly on Windows systems.

## ✨ Features

- ✔ Portable – no installation required
- ✔ Zero configuration
- ✔ Automatically downloads and installs MagicAppBuilder
- ✔ Rebuilds server configuration every time the server starts
- ✔ Includes:
  - Apache HTTP Server
  - PHP
  - MariaDB (MySQL)
  - Adminer for database management

## 📁 Folder Structure

```txt
MagicServer/
├── apache/              # Apache HTTP Server binaries and configs
├── mysql/               # MariaDB binaries and data
├── php/                 # PHP runtime
├── www/                 # Web root directory (place your app here)
│   └── MagicAppBuilder/ # Auto-installed MagicAppBuilder
├── config/              # Template and generated config files
├── logs/                # Apache and MariaDB logs
├── install.php          # Script to download and install MagicAppBuilder
├── start.php            # Script to generate config and start the server
└── stop.php             # Script to stop the server
````

## 🚀 Getting Started

### 1. Download and Extract

Extract the MagicServer package to any location, for example:

```
D:\Server\MagicServer
```

### 2. Install MagicAppBuilder

Before running the server, you **must install** MagicAppBuilder. This step downloads the latest version and places it in `www/MagicAppBuilder/`.

Run the following command:

```bash
php\php.exe install.php
```

### 3. Start the Server

Start the Apache and MariaDB servers, and regenerate configuration files by running:

```bash
php\php.exe start.php
```

> ℹ️ `start.php` will always regenerate server configuration files based on the current path using templates.

### 4. Access Your Application

Open your browser and go to:

```
http://localhost/MagicAppBuilder/
```

You should now see the MagicAppBuilder interface.

### 5. Stop the Server

To stop Apache and MariaDB services, run:

```bash
php\php.exe stop.php
```

## 🔧 Configuration

The server uses template-based configuration. These templates are used to generate final configuration files each time the server starts:

* Apache: `config/httpd-template.conf`
* PHP: `php/php-template.ini`
* MariaDB: `mysql/my-template.ini`

> ⚠️ Do **not** manually edit the generated config files. Instead, edit the templates.

## 🛡️ Default Credentials

| Service | Username | Password      |
| ------- | -------- | ------------- |
| MariaDB | root     | (no password) |
| Adminer | root     | (no password) |

> 💡 It is strongly recommended to set a password for the root user after setup.

## ⚙️ Compatibility

* Works on Windows 10/11
* Supports PHP 7.x and 8.x
* Compatible with MagicAppBuilder **v1.12.0+**

## 🛠 Tools Included

* **Adminer** — Access via `http://localhost/adminer.php`
* **PHP CLI** — Run PHP scripts like `install.php`, `start.php`, `stop.php`
* **MariaDB Client** — Accessible via `mysql/bin/mysql.exe`

## 📜 License

This project is licensed under the [MIT License](LICENSE), except for bundled third-party components which retain their respective open-source licenses.

## 🙏 Acknowledgements

MagicServer bundles the following open-source software:

* [Apache HTTP Server](https://httpd.apache.org/)
* [PHP](https://www.php.net/)
* [MariaDB](https://mariadb.org/)
* [Adminer](https://www.adminer.org/)
* [MagicAppBuilder](https://github.com/planetbiru/magicappbuilder)

---

Happy developing with **MagicAppBuilder**! 🚀



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
├── index.php            # Default PHP script from server root
├── install.php          # Script to download and install MagicAppBuilder
├── start.php            # Script to generate config and start the server
└── stop.php             # Script to stop the server
```

## 🚀 Getting Started

### 1. Download and Extract

Extract the MagicServer package to any location, for example:

```
D:\MagicServer
```

### 2. Open Command Prompt as Administrator

Before running any script, **open Command Prompt (CMD) with Administrator privileges**:

1. Click Start Menu → search **"cmd"**
2. Right-click **Command Prompt** → click **"Run as administrator"**
3. In Command Prompt, navigate to the server directory:

```bash
D:
cd MagicServer
```

### 3. Install MagicAppBuilder

To download and install the latest version of MagicAppBuilder into `www/MagicAppBuilder/`, run:

```bash
php\php.exe install.php
```

> ⚠️ This step is required before starting the server.

### 4. Start the Server

Start the Apache and MariaDB servers, and regenerate configuration files by running:

```bash
php\php.exe start.php
```

> ℹ️ `start.php` will always regenerate server configuration files based on the current path using templates.

### 5. Access Your Application

Open your browser and go to:

```
http://localhost/MagicAppBuilder/
```

You should now see the MagicAppBuilder interface.

### 6. Stop the Server

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

| Service          | Username        | Password      |
| ---------------- | --------------- | ------------- |
| MariaDB          | root            | (no password) |
| MagicAppBuilder  | administrator   | administrator |
| Your Application | superuser       | superuser     |

> 💡 It is strongly recommended to set a password for the root user after setup.

## ⚙️ Compatibility

* Works on Windows 10/11
* Supports PHP 7.x and 8.x
* Compatible with MagicAppBuilder **v1.12.0+**

## 🛠 Tools Included

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

---

Happy developing with **MagicAppBuilder**! 🚀



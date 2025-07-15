# MagicServer

**MagicServer** is a lightweight and portable server package that includes **Apache**, **PHP**, and **MariaDB** (MySQL-compatible), pre-configured to run [MagicAppBuilder](https://github.com/planetbiru/magicappbuilder) smoothly on Windows systems.

## ✨ Features

- ✔ Portable – no installation required
- ✔ Zero configuration
- ✔ Optimized for [MagicAppBuilder](https://github.com/planetbiru/magicappbuilder)
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
│   └── MagicAppBuilder/ # Optional: pre-installed MagicAppBuilder
├── config/              # Template and generated config files
├── logs/                # Apache and MariaDB logs
├── install.php          # Script to install MagicAppBuilder
├── start.php            # Script to start the server
└── stop.php             # Script to stop the server
```

## 🚀 Getting Started

### 1. Download and Extract

Extract the MagicServer package to any location, for example:

```

D:\Server\MagicServer

````

### 2. Start the Server

Run the server using:

```bash
php start.php
````

Or double-click `start.php` if associated with PHP CLI.

### 3. Access Your Application

Open your browser and navigate to:

```
http://localhost/
```

If MagicAppBuilder is installed, it should appear at:

```
http://localhost/MagicAppBuilder/
```

### 4. Stop the Server

To stop Apache and MariaDB services:

```bash
php stop.php
```

### 5. Install MagicAppBuilder (Optional)

If MagicAppBuilder is not yet installed, you can run:

```bash
php install.php
```

This will set up the necessary files under `www/MagicAppBuilder/`.

## 🔧 Configuration

You can customize configuration files if needed:

* Apache: `config/httpd.conf`
* PHP: `php/php.ini`
* MariaDB: `mysql/my.ini`

The configuration files are automatically adjusted based on the install path.

## 🛡️ Default Credentials

| Service | Username | Password      |
| ------- | -------- | ------------- |
| MariaDB | root     | (no password) |
| Adminer | root     | (no password) |

> 💡 It is strongly recommended to set a password for the root user after setup.

## ⚙️ Compatibility

* Works on Windows 10/11
* Supports PHP 7.x and 8.x
* Compatible with MagicAppBuilder v1.10.0+

## 🛠 Tools Included

* **Adminer** — Access via `http://localhost/adminer.php`
* **PHP CLI** — Run PHP scripts like `start.php`, `install.php`
* **MariaDB Client** — Accessible via `mysql/bin/mysql.exe`

## 📜 License

This project is licensed under the [MIT License](LICENSE), except bundled third-party components which are licensed under their respective open-source licenses.

## 🙏 Acknowledgements

MagicServer packages the following open-source software:

* [Apache HTTP Server](https://httpd.apache.org/)
* [PHP](https://www.php.net/)
* [MariaDB](https://mariadb.org/)
* [Adminer](https://www.adminer.org/)
* [MagicAppBuilder](https://github.com/planetbiru/magicappbuilder)

---

Happy developing with **MagicAppBuilder**! 🚀


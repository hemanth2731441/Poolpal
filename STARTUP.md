# PoolPal — Local Setup Guide

Get the PoolPal marketing site running locally.

---

## Prerequisites

| Software | Version | Download |
|----------|---------|----------|
| XAMPP | 8.2+ (Apache + PHP) | https://www.apachefriends.org/ |
| Git | Any | https://git-scm.com/ |

> **Note:** No database, Composer, or external APIs are required. This is a static marketing site.

---

## 1. Install XAMPP

1. Download and install XAMPP from https://www.apachefriends.org/
2. Default install path: `C:\xampp`
3. Ensure **Apache** is selected during installation (MySQL is not needed)

---

## 2. Clone the Project

```bash
cd C:\xampp\htdocs
git clone https://github.com/hemanth2731441/Poolpal.git Poolpal
```

The project should be at:
```
C:\xampp\htdocs\Poolpal\
```

---

## 3. Start Apache

1. Open **XAMPP Control Panel**
2. Start **Apache** (click "Start")
3. It should show a green status indicator

---

## 4. Open the Application

Open your browser and navigate to:

| Page | URL |
|------|-----|
| **Homepage** | http://localhost/Poolpal/ |
| **About Us** | http://localhost/Poolpal/aboutus.php |

That's it — no database setup, no environment variables, no dependencies to install.

---

## Project Structure

```
Poolpal/
├── css/
│   └── poolpal-marketing.css   # Design system & animations
├── images/
│   └── logo/                   # Logo assets
├── nav.php                     # Shared navigation header
├── footer.php                  # Shared footer
├── fpage.php                   # Homepage (marketing landing page)
├── aboutus.php                 # About Us / Founders page
├── .htaccess                   # Routes / to fpage.php
└── README.md                   # Project overview
```

---

## Troubleshooting

| Issue | Solution |
|-------|---------|
| **404 on http://localhost/Poolpal/** | Ensure the folder is named `Poolpal` under `C:\xampp\htdocs\` |
| **Styles missing / broken layout** | Clear browser cache; verify `css/poolpal-marketing.css` exists |
| **Blank page** | Check Apache is running in XAMPP Control Panel |

---

*For detailed project info, see [BRD.md](BRD.md) and [BRD_CLEANUP.md](BRD_CLEANUP.md).*

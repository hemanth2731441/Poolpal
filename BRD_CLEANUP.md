# PoolPal Website Cleanup — Project Tracker & BRD

## Objective

Transform PoolPal from a full-service ridesharing web application into a **static marketing/showcase website**. All services have migrated to the mobile app; this website exists solely to present the brand, showcase the app, and provide company information.

---

## Scope

### IN SCOPE (Keep / Build)
| # | Item | Details |
|---|------|---------|
| 1 | **Homepage** (`fpage.php`) | Hero section, app showcase, features overview, download CTA, visual animations |
| 2 | **About Us** (`aboutus.php`) | Founders, mission, values — refreshed design |
| 3 | **Navigation** (`nav.php`) | Simplified — Home, About Us only. No login/profile/post-ride |
| 4 | **Footer** (`footer.php`) | Contact info, social links, address. No service links |
| 5 | **Logo** | Replace with new logo (yellow vehicles + "poolpal" text) |
| 6 | **Animations** | Top-notch CSS/JS animations — particles, scroll reveals, parallax, hover effects |
| 7 | **Static assets** | Images, CSS, fonts — all served from files, no DB |

### OUT OF SCOPE (Remove / Disable)
| # | Item | Reason |
|---|------|--------|
| 1 | **Database connectivity** | No DB needed — static site |
| 2 | **User login/signup** | Moved to mobile app |
| 3 | **Driver login/signup/dashboard** | Moved to mobile app |
| 4 | **Ride search/booking/payment** | Moved to mobile app |
| 5 | **Admin panel** | No longer needed for static site |
| 6 | **Session management** | No auth = no sessions |
| 7 | **Email/SMTP** | No transactional emails needed |
| 8 | **Razorpay/payments** | Moved to mobile app |
| 9 | **Google Maps/Places API** | Moved to mobile app |
| 10 | **Profile management** | Moved to mobile app |
| 11 | **`.env` / config.php DB** | No environment secrets needed |
| 12 | **Composer dependencies** | PHPMailer, Razorpay SDK, Twilio — all removed |

---

## Task Tracker

| # | Task | Status | Notes |
|---|------|--------|-------|
| 1 | Save new logo to `images/logo/` | DONE | New logo with yellow vehicles |
| 2 | Create cleanup BRD | DONE | This document |
| 3 | Rewrite `nav.php` — static, no sessions | TODO | Home + About Us links only |
| 4 | Rewrite `fpage.php` — marketing homepage | TODO | Hero, features, app showcase, download CTA |
| 5 | Rewrite `aboutus.php` — refreshed design | TODO | Founders, mission, values with animations |
| 6 | Rewrite `footer.php` — no service links | TODO | Contact, social, address only |
| 7 | Create new CSS with premium animations | TODO | Particles, scroll reveals, parallax, glassmorphism |
| 8 | Remove/archive DB-dependent PHP files | TODO | login, signup, booking, payment, admin, etc. |
| 9 | Remove `config.php` DB config, `db.php`, `init.php` | TODO | No database needed |
| 10 | Remove `vendor/`, `composer.json`, `.env` | TODO | No PHP dependencies needed |
| 11 | Test all pages render correctly | TODO | No errors, no DB calls |
| 12 | Commit and push to GitHub | TODO | Clean commit |

---

## Design Direction

- **Color Palette**: Gold/Yellow (#ffbf00) primary, dark gray (#2a2a2a) text, white backgrounds
- **Typography**: Poppins (headings) + Inter (body)
- **Animations**: Scroll-triggered reveals, floating particles, parallax backgrounds, smooth hover transitions, gradient shifts, glassmorphism cards
- **Responsive**: Mobile-first, works on all screen sizes
- **Performance**: No external API calls, no DB, fast static pages

---

*Last updated: March 28, 2026*

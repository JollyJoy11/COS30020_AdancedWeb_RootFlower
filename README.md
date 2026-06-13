# RootFlower

A PHP web application for a flower arrangement shop, built for COS30020 Advanced Web Technologies (Swinburne University).

**Live demo:** https://cos30020adancedwebrootflower-production.up.railway.app

## Features

- **Shop** — browse and add flower products to cart, place orders
- **AR Flower Arranger** — drag-and-drop virtual arrangement builder with save/share
- **Workshops** — browse and register for floristry workshops
- **Student Works Gallery** — community gallery for uploading and viewing arrangements
- **User Accounts** — registration, login, profile management, password reset via email
- **Admin Panel** — manage products, workshops, student works, accounts, and newsletter subscribers
- **PDF Receipts** — downloadable order confirmation PDFs via TCPDF

## Tech Stack

- PHP 8.2 (procedural)
- MySQL
- HTML / CSS / JavaScript (vanilla)
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) — password reset emails
- [TCPDF](https://tcpdf.org/) — PDF generation
- Hosted on [Railway](https://railway.app)

## Demo Credentials

| Role  | Email | Password |
|-------|-------|----------|
| Admin | admin@swin.edu.my | admin |
| User  | joannecjx.0111@gmail.com | qwer1234! |

## Notes

- File uploads (profile images, student works, AR arrangements) reset on redeploy as Railway uses an ephemeral filesystem.
- PHPMailer requires SMTP credentials set as environment variables to send emails on the live deployment.

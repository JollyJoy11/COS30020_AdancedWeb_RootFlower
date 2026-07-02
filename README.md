# RootFlower

RootFlower is a PHP-based florist web application that combines e-commerce, creative tools, and community features into one experience. It was developed as part of COS30020 Advanced Web Technologies and showcases a complete online flower shop with user accounts, workshop bookings, an AR-inspired arrangement builder, and an admin dashboard.

Live demo: https://cos30020adancedwebrootflower-production.up.railway.app

## What the website offers

RootFlower gives registered users a way to:

- browse floral products by category and occasion
- add items to a shopping cart and place orders
- view order history and manage their profile
- register for florist workshops and view upcoming sessions
- design and save their own floral arrangements using the AR flower arrangement tool
- contribute flower information by uploading a PDF description and receiving a downloadable generated PDF
- upload and explore student floral creations in the gallery
- subscribe to newsletters and receive account-related emails

## Main features

- Shop experience with product listings, filtering, ratings and basket checkout
- Cart and order flow with order confirmation emails and saved order history
- Flower contribution flow that extracts PDF descriptions and generates downloadable PDFs
- Workshop registration with session-based booking information
- AR Flower Arranger for creating virtual floral arrangements
- Student Works gallery for showcasing student creations
- User account system with login, registration, password reset, and profile updates
- Admin panel for managing products, workshops, student works, accounts, and subscribers

## Tech stack

- PHP 8.2 (procedural style)
- MySQL database
- HTML, CSS, and JavaScript
- PHPMailer for email-based password reset support and order confirmations
- TCPDF for generating downloadable flower contribution PDFs
- Hosted on Railway

## Local setup

1. Place the project in your local web server folder such as XAMPP's htdocs directory.
2. Start Apache and MySQL.
3. Configure the database connection in the project’s database include file.
4. Import your database structure and data if provided.
5. Open the project in your browser via your local server.

## Demo access

A sample admin account is available for testing:

- Email: admin@swin.edu.my
- Password: admin

## Notes

- File uploads such as profile photos, student work submissions, and arrangement images may not persist on cloud hosting if the deployment uses a temporary filesystem.
- Email features require valid SMTP settings in the environment or server configuration.
- Order confirmations are sent by email rather than as downloadable PDF receipts.
- The site is designed as a university project and focuses on demonstrating web development concepts, database interaction, and user role management.

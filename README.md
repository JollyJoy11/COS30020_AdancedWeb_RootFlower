# RootFlower

A PHP web application for a flower arrangement shop, built for COS30020 Advanced Web Technologies (Swinburne University).

## Features

- **Shop** — browse and add flower products to cart, place orders
- **AR Flower Arranger** — drag-and-drop virtual arrangement builder with save/share
- **Workshops** — browse and register for floristry workshops
- **Student Works Gallery** — community gallery for uploading and viewing arrangements
- **User Accounts** — registration, login, profile management, password reset via email
- **Admin Panel** — manage products, workshops, student works, accounts, and newsletter subscribers
- **PDF Receipts** — downloadable order confirmation PDFs via TCPDF

## Tech Stack

- PHP 8.x (procedural)
- MySQL
- HTML/CSS/JavaScript (vanilla)
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) — password reset emails
- [TCPDF](https://tcpdf.org/) — PDF generation

## Setup

### Requirements

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP)

### Installation

1. Clone the repository into your XAMPP `htdocs` folder:
   ```
   git clone https://github.com/<your-username>/COS30020_AdancedWeb_RootFlower.git xampp/htdocs/assign2
   ```

2. Start **Apache** and **MySQL** in the XAMPP Control Panel.

3. Open [phpMyAdmin](http://localhost/phpmyadmin) and create a database named `RootFlower`.

4. Import the provided SQL dump:
   - In phpMyAdmin, select the `RootFlower` database → **Import** → choose the `.sql` file.

5. The database connection in [include/db_connect.php](include/db_connect.php) uses the default XAMPP credentials (`root` / no password). Update if yours differ.

6. Open [http://localhost/assign2](http://localhost/assign2) in your browser.

### Placeholder folders

The following upload directories are excluded from version control. Create them if they don't exist:

```
mkdir ar
mkdir profile_images
mkdir studentworks
mkdir pdfparser
```

The `profile_images` folder ships with `default.png`, `boy.png`, and `girl.png` as defaults.

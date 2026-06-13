<?php
    $servername = getenv('MYSQLHOST')     ?: 'localhost';
    $username   = getenv('MYSQLUSER')     ?: 'root';
    $password   = getenv('MYSQLPASSWORD') ?: '';
    $dbname     = getenv('MYSQLDATABASE') ?: 'RootFlower';
    $port       = (int)(getenv('MYSQLPORT') ?: 3306);

    // Create database only when running locally (Railway already provisions one)
    $conn = mysqli_connect($servername, $username, $password, '', $port);
    if ($conn) {
        mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$dbname`");
        mysqli_close($conn);
    }

    include("db_connect.php");

    //Create database table
    $table = [
        "CREATE TABLE IF NOT EXISTS user_table (
            id INT(4) UNSIGNED ZEROFILL AUTO_INCREMENT UNIQUE NOT NULL,
            email VARCHAR(50) NOT NULL PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            dob DATE NULL,
            gender VARCHAR(6) NOT NULL,
            hometown VARCHAR(50) NOT NULL,
            profile_image VARCHAR(100) NULL,
            newsletter ENUM('yes', 'no') DEFAULT 'no',
            trash ENUM('yes','no') DEFAULT 'no',
            trash_date DATETIME)",

        "CREATE TABLE IF NOT EXISTS account_table (
            id INT(4) UNSIGNED ZEROFILL AUTO_INCREMENT UNIQUE NOT NULL,
            email VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            type ENUM('user','admin') DEFAULT 'user' NOT NULL,
            login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            selector CHAR(16) NULL,
            validator_hash CHAR(64) NULL,
            reset_token VARCHAR(64) NULL,
            reset_expiry DATETIME NULL,
            trash ENUM('yes','no') DEFAULT 'no',
            trash_date DATETIME,
            FOREIGN KEY (email) REFERENCES user_table(email) 
                ON DELETE CASCADE ON UPDATE CASCADE)",

        "CREATE TABLE IF NOT EXISTS flower_table (
            id INT(4) UNSIGNED ZEROFILL AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
            Scientific_Name VARCHAR(50) NOT NULL,
            Common_Name VARCHAR(50) NOT NULL,
            plants_image VARCHAR(100) NULL,
            description VARCHAR(100) NULL,
            trash ENUM('yes','no') DEFAULT 'no',
            trash_date DATETIME)",

        "CREATE TABLE IF NOT EXISTS workshop_table (
            id INT(4) UNSIGNED ZEROFILL AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
            email VARCHAR(50) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            workshop_title VARCHAR(50) NOT NULL,
            date VARCHAR(255) NULL,
            time VARCHAR(255) NULL, 
            no_of_seats INT(2) NOT NULL,
            contact_number VARCHAR(15) NULL,
            submit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            approve_status ENUM('pending','approved','rejected') DEFAULT 'pending',
            pending_seats INT(2) NULL,
            edit_status ENUM('none','pending') DEFAULT 'none',
            trash ENUM('yes','no') DEFAULT 'no',
            trash_date DATETIME,
            UNIQUE KEY unique_workshop (email, workshop_title, date, time))",
            
        "CREATE TABLE IF NOT EXISTS studentworks_table (
            id INT(4) UNSIGNED ZEROFILL AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
            email VARCHAR(50) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            workshop_id INT(4) UNSIGNED ZEROFILL NOT NULL,
            workshop_media VARCHAR(100) NULL,
            caption VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            likes INT(6) DEFAULT 0,
            approve_status ENUM('pending','approved','rejected') DEFAULT 'pending',
            upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            trash ENUM('yes','no') DEFAULT 'no',
            trash_date DATETIME,
            FOREIGN KEY (workshop_id) REFERENCES workshop_table(id))",

        "CREATE TABLE IF NOT EXISTS studentworkcomments_table (
            id INT(4) UNSIGNED ZEROFILL AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
            studentwork_id INT(4) UNSIGNED ZEROFILL NOT NULL,
            email VARCHAR(50) NOT NULL,
            comment_text VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            trash ENUM('yes','no') DEFAULT 'no',
            trash_date DATETIME,
            FOREIGN KEY (studentwork_id) REFERENCES studentworks_table(id)
                ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (email) REFERENCES user_table(email))",

        "CREATE TABLE IF NOT EXISTS studentworklikes_table (
            id INT(4) UNSIGNED ZEROFILL AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
            studentwork_id INT(4) UNSIGNED ZEROFILL NOT NULL,
            email VARCHAR(50) NOT NULL,
            like_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (studentwork_id, email),
            FOREIGN KEY (studentwork_id) REFERENCES studentworks_table(id)
                ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (email) REFERENCES user_table(email)
                ON DELETE CASCADE ON UPDATE CASCADE)",

        "CREATE TABLE IF NOT EXISTS products_table (
            id INT(4) UNSIGNED ZEROFILL AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
            product_name VARCHAR(100) NOT NULL UNIQUE,
            description VARCHAR(255) NULL,
            price DECIMAL(10,2) NOT NULL,
            category VARCHAR(50) NOT NULL,
            occasion VARCHAR(50) NULL,
            product_image VARCHAR(100) NULL,
            rating FLOAT(2,1) DEFAULT 0,
            reviews INT DEFAULT 0,
            trash ENUM('yes','no') DEFAULT 'no',
            trash_date DATETIME)",

        "CREATE TABLE IF NOT EXISTS ar_table (
            id INT(4) UNSIGNED ZEROFILL AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
            email VARCHAR(50) NOT NULL,
            image VARCHAR(100) NOT NULL, 
            flowers VARCHAR(255) NOT NULL,
            upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (email) REFERENCES user_table(email)
                ON DELETE CASCADE ON UPDATE CASCADE)",

        "CREATE TABLE IF NOT EXISTS notification_table (
            id INT(4) UNSIGNED ZEROFILL AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
            email VARCHAR(50) NOT NULL,
            type VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            related_id INT(11) NULL,
            related_table VARCHAR(50) NULL,
            is_read TINYINT(1) DEFAULT 0 NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            FOREIGN KEY (email) REFERENCES user_table(email)
                ON DELETE CASCADE ON UPDATE CASCADE)"
    ];

    foreach ($table as $query) {
        mysqli_query($conn, $query);
    }

    // Create trigger for soft delete
    $soft_delete_trigger_exists = false;
    $trigger_check_soft_delete = mysqli_query($conn, "SHOW TRIGGERS LIKE 'soft_delete_user'");

    if ($trigger_check_soft_delete && mysqli_num_rows($trigger_check_soft_delete) > 0) {
        $soft_delete_trigger_exists = true;
    }

    if (!$soft_delete_trigger_exists) {
        $triggerSQL = "
        CREATE TRIGGER soft_delete_user
        AFTER UPDATE ON user_table
        FOR EACH ROW
        BEGIN
            IF NEW.trash = 'yes' AND OLD.trash = 'no' THEN
                UPDATE account_table SET trash = 'yes', trash_date = NOW() WHERE email = NEW.email;
            END IF;
        END
        ";
    }

    // Create trigger for restoring soft deleted user
    $restore_trigger_exists = false;
    $trigger_check_restore = mysqli_query($conn, "SHOW TRIGGERS LIKE 'restore_user'");

    if ($trigger_check_restore && mysqli_num_rows($trigger_check_restore) > 0) {
        $restore_trigger_exists = true;
    }

    if (!$restore_trigger_exists) {
        $restoreTriggerSQL = "
        CREATE TRIGGER restore_user
        AFTER UPDATE ON user_table
        FOR EACH ROW
        BEGIN
            IF NEW.trash = 'no' AND OLD.trash = 'yes' THEN
                UPDATE account_table SET trash = 'no' WHERE email = NEW.email;
            END IF;
        END
        ";
    }

    // Create triiger for like count
    $trigger_check = mysqli_query($conn, "SHOW TRIGGERS LIKE 'studentworklikes_table'");
    $trigger_exists = false;

    if ($trigger_check && mysqli_num_rows($trigger_check) > 0) {
        while ($row = mysqli_fetch_assoc($trigger_check)) {
            if ($row['Trigger'] === 'update_like_count') {
                $trigger_exists = true;
                break;
            }
        }
    }

    if (!$trigger_exists) {
        $triggerSQL = "
        CREATE TRIGGER update_like_count
        AFTER INSERT ON studentworklikes_table
        FOR EACH ROW
        BEGIN
            UPDATE studentworks_table 
            SET likes = likes + 1 
            WHERE id = NEW.studentwork_id;
        END
        ";

        mysqli_query($conn, $triggerSQL);
    }

    $trigger_check = mysqli_query($conn, "SHOW TRIGGERS LIKE 'studentworklikes_table'");
    $trigger_exists = false;

    if ($trigger_check && mysqli_num_rows($trigger_check) > 0) {
        while ($row = mysqli_fetch_assoc($trigger_check)) {
            if ($row['Trigger'] === 'update_unlike_count') {
                $trigger_exists = true;
                break;
            }
        }
    }

    if (!$trigger_exists) {
        $triggerSQL = "
        CREATE TRIGGER update_unlike_count
        AFTER DELETE ON studentworklikes_table
        FOR EACH ROW
        BEGIN
            UPDATE studentworks_table 
            SET likes = likes - 1 
            WHERE id = OLD.studentwork_id;
        END
        ";

        mysqli_query($conn, $triggerSQL);
    }

    include "dataset.php";

    mysqli_close($conn);
?>
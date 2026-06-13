<?php
include "include/session.php";
include "include/db_connect.php";
header('Content-Type: application/json');

$flowers = '[]'; 
$arrangement_data_json = $_POST['arrangement_data'] ?? '{}';
$arrangement_data = json_decode($arrangement_data_json, true);

// Flower used in the arrangement
if (isset($arrangement_data['flowerList']) && is_array($arrangement_data['flowerList'])) {
    $flowers = implode(',', $arrangement_data['flowerList']);
}

if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
    $temp_path = $_FILES['screenshot']['tmp_name'];
    $filename = uniqid('arrangement_', true) . '.png';
    $target_dir = 'ar/'; 
    $target_path = $target_dir . $filename;

    if (move_uploaded_file($temp_path, $target_path)) {
        $arrangement_data_json = $_POST['arrangement_data'] ?? '{}';
        $arrangement_data = json_decode($arrangement_data_json, true);
        
        // Save the record to the database 
        $query = "INSERT INTO ar_table (email, image, flowers) VALUES ('{$_SESSION['user']}', '$target_path', '$flowers')";
        mysqli_query($conn, $query);

        http_response_code(200);
    } 

} 

mysqli_close();
?>
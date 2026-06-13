<?php
    // Input sanitization function
    function sanitise_input($data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Email sending function using PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;
    
    require 'phpmailer/src/Exception.php';
    require 'phpmailer/src/PHPMailer.php';
    require 'phpmailer/src/SMTP.php';
    
    function sendEmail($to, $subject, $message) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('MAIL_USERNAME');
            $mail->Password = getenv('MAIL_PASSWORD');
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom(getenv('MAIL_USERNAME'));
            $mail->addAddress($to);
    
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $message;
    
            $mail->send();
    
            return true;
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    // Create remember-me token
    function generateRememberMeToken($email, $conn) {
        $selector = bin2hex(random_bytes(8)); 
        $validator = bin2hex(random_bytes(32)); 
        $validatorHash = hash('sha256', $validator); 

        // Update account_table with new token info
        $update_sql = "UPDATE account_table SET selector = ?, validator_hash = ? WHERE email = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "sss", $selector, $validatorHash, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Store selector and validator in the user's cookie for 30 days
        setcookie("remember_me", "$selector|$validator", time() + (86400 * 30), "/", "", false, true);
    }

    // Validate token
    function validateRememberMeToken($conn) {
        if (empty($_COOKIE['remember_me'])) return false;

        list($selector, $validator) = explode('|', $_COOKIE['remember_me']);

        $sql = "SELECT email, validator_hash FROM account_table WHERE selector = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $selector);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (hash_equals($row['validator_hash'], hash('sha256', $validator))) {
                $email = $row['email'];

                // Generate a new token (refresh it)
                generateRememberMeToken($email, $conn);

                // Restore user session
                $user_sql = "SELECT first_name, last_name, gender, profile_image FROM user_table WHERE email = ?";
                $user_stmt = mysqli_prepare($conn, $user_sql);
                mysqli_stmt_bind_param($user_stmt, "s", $email);
                mysqli_stmt_execute($user_stmt);
                $user_result = mysqli_stmt_get_result($user_stmt);

                if ($user = mysqli_fetch_assoc($user_result)) {
                    $_SESSION['name'] = $user['first_name'];
                    $_SESSION['lname'] = $user['last_name'];
                    $_SESSION['gender'] = $user['gender'];
                    $_SESSION['profile'] = $user['profile_image'];
                }

                mysqli_stmt_close($user_stmt);

                $acc_sql = "SELECT type FROM account_table WHERE email = ?";
                $acc_stmt = mysqli_prepare($conn, $acc_sql);
                mysqli_stmt_bind_param($acc_stmt, "s", $email);
                mysqli_stmt_execute($acc_stmt);
                $acc_result = mysqli_stmt_get_result($acc_stmt);

                if ($acc = mysqli_fetch_assoc($acc_result)) {
                    $_SESSION['role'] = $acc['type'];
                }

                mysqli_stmt_close($acc_stmt);
                mysqli_stmt_close($stmt);
                return $email;
            }
        }

        mysqli_stmt_close($stmt);
        return false;
    }

    // Clear tokens (logout)
    function clearRememberMe($email, $conn) {
        // Clear the token data in account_table
        $sql = "UPDATE account_table SET selector = NULL, validator_hash = NULL WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Remove the cookie
        setcookie("remember_me", "", time() - 3600, "/");
    }

    // Workshop Rejection modal
    function rejection_modal($id, $action_type, $base_url) {
        $modal_id = ($action_type == 'edit_reject') ? "rejectModalEdit_$id" : "rejectModalNormal_$id";
        
        $html = <<<HTML
        <div class="modal fade" id="{$modal_id}" tabindex="-1" aria-labelledby="rejectModalLabel_{$id}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mx-auto">
                <div class="modal-content p-2">
                    <div class="modal-header">
                        <h2 class="modal-title" id="rejectModalLabel_{$id}">Provide Rejection Reason</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form method="POST" action="manage_workshop_reg.php{$base_url}">
                        <div class="modal-body">
                            <p class="small text-muted text-wrap text-start">Please provide a reason for rejecting this registration. This reason will be sent to the user.</p>
                            <textarea class="form-control" name="rejection_reason" rows="3" placeholder="e.g. Only 3 seats left"></textarea>
                        </div>

                        <div class="modal-footer">
                            <input type="hidden" name="action_type_modal" value="{$action_type}">
                            <input type="hidden" name="action_reject_modal" value="{$id}">
                            
                            <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger text-light border-danger" name="conf_reject">Confirm Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    HTML;

        return $html;
    }

    // Workshop Edit modal
    function edit_modal($id, $email, $firstName, $lastName, $title, $contact, $seats, $base_url, $workshop_data, $dates, $times) {
        $modal_id = "editModal_$id";
        
        $html = <<<HTML
        <div class="modal fade edit-modal" id="{$modal_id}" tabindex="-1" aria-labelledby="editModalLabel_{$id}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered mx-auto">
                <div class="modal-content p-2">
                    <form method="POST" action="manage_workshop_reg.php{$base_url}">
                        <div class="modal-header">
                            <h2 class="modal-title" id="editModalLabel_{$id}">Edit Workshop - $title</h2>
                            <button type="submit" name="reset" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body text-secondary me-3">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <p class="m-0 fw-bold">Name</p>
                                    <p class="m-0">$firstName</p>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <p class="m-0"><br>$lastName</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <p class="m-0 fw-bold">Contact No</p>
                                    <p class="m-0">$contact</p>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <p class="m-0 fw-bold">Email</p>
                                    <p class="m-0">$email</p>
                                </div>
                            </div>
        HTML;

        if (strpos($title, 'Florist To Be') === false) {
            $current_value = '';
            if ($title === 'Handtied Bouquet') {
                $date1 = $dates[0] ?? '';
                $date2 = $dates[1] ?? '';
                $time1 = $times[0] ?? '';
                $time2 = $times[1] ?? '';
                $current_value = "{$date1}, {$date2}, {$time1}, {$time2}";
            } else { 
                $date1 = $dates[0] ?? '';
                $time1 = $times[0] ?? '';
                $current_value = "{$date1}, {$time1}";
            }
            
            $options_html = '<option value="" disabled>-- Select a new session --</option>';
            $today = date('Y-m-d');

            foreach ($workshop_data as $details) {
                $sessionValue = ''; 
                
                if ($title === 'Hobby Class') {
                    $startDate = $details['date'];
                    $sessionValue = "{$details['date']}, {$details['time']}";
                    
                    $display = date("d M Y", strtotime($details['date'])) . " | " . $details['time'];
                    
                } else { // Handtied Bouquet
                    $day1Data = $details['days'][0] ?? ['date' => '', 'time' => ''];
                    $day2Data = $details['days'][1] ?? ['date' => '', 'time' => ''];
                    
                    $startDate = $day1Data['date']; 

                    $sessionValue = "{$day1Data['date']}, {$day2Data['date']}, {$day1Data['time']}, {$day2Data['time']}";
                    
                    $day1Date = date("d M Y", strtotime($day1Data['date']));
                    $day1Time = $day1Data['time'];
                    $day2Date = date("d M Y", strtotime($day2Data['date']));
                    $day2Time = $day2Data['time'];
                    
                    $display = "Day 1: {$day1Date} at {$day1Time} | Day 2: {$day2Date} at {$day2Time}";
                }

                $selected = ($sessionValue === $current_value) ? 'selected' : '';
                $disabled = ($startDate < $today) ? 'disabled' : '';
                
                if ($startDate < $today) {
                    $display .= " [PASSED]";
                }
                
                $options_html .= "<option value=\"".htmlspecialchars($sessionValue)."\" {$selected} {$disabled}>" . htmlspecialchars($display) . "</option>";
            }
            
            $html .= <<<HTML
                <div class="row">
                    <div class="form-group col-md-8 mb-2">
                        <label for="edit_session_{$id}" class="form-label fw-bold">Session</label>
                        <select class="form-select py-0" id="edit_session_{$id}" name="session_id">
                            {$options_html}
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="seat" class="form-label fw-bold">Number of seat(s)</label>
                        <input type="number" class="form-control w-50" value="{$seats}" min="1" max="20" name="seats_new">
                    </div>
                </div>
            HTML;
        } else {
            $batch = date("F Y", strtotime($dates[0])); // The current batch name (e.g., "November 2025")
            
            // Use the batch name to get the specific batch data
            $batch_data = $workshop_data[$batch] ?? null;

            $html .= <<<HTML
                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="seat" class="form-label fw-bold">Number of seat(s)</label>
                        <input type="number" class="form-control w-50" value="{$seats}" min="1" max="20" name="seats_new">
                    </div>
                </div>
                <div class="mb-2">
                    <strong>Current Batch: $batch</strong>
                    <div class="row">
            HTML;

            if ($batch_data && isset($batch_data['days'])) {
                $general_time_slots = ["8:30 AM - 12:30 PM", "2:30 PM - 6:30 PM"];

                for ($dayNumber = 1; $dayNumber <= 4; $dayNumber++) {
                    $current_date = $dates[$dayNumber - 1] ?? '';
                    $current_time = $times[$dayNumber - 1] ?? '';
                    
                    $day_info = $batch_data['days'][$dayNumber]; 
                    
                    $html .= '<div class="form-group col-md-6 d-flex">';
                    $html .= '<label for="edit_date_day'.$dayNumber.'_'.$id.'" class="w-50 mb-2">Day '.$dayNumber.'</label>';
                    
                    // --- Date Dropdown ---
                    $html .= '<select id="edit_date_day'.$dayNumber.'_'.$id.'" name="edit_date_day'.$dayNumber.'" class="form-select py-0 mb-2">'; 
                    $html .= '<option value="" disabled>-- Select a new date --</option>';
                    
                    foreach ($day_info['dates'] as $date) {
                        $selected = ($date === $current_date) ? 'selected' : '';
                        $display = date("d M Y", strtotime($date));
                        $html .= "<option value=\"{$date}\" {$selected}>{$display}</option>";
                    }
                    $html .= '</select></div>';
                    
                    // --- Time Dropdown ---
                    $html .= '<div class="form-group col-md-6 mb-2">';
                    $html .= '<select id="edit_time_day'.$dayNumber.'_'.$id.'" name="edit_time_day'.$dayNumber.'" class="form-select py-0">';
                    $html .= '<option value="" disabled selected>-- Select a new time --</option>';

                    foreach ($general_time_slots as $time_slot) {
                        $selected = ($time_slot === $current_time) ? 'selected' : '';
                        $html .= "<option value=\"{$time_slot}\" {$selected}>{$time_slot}</option>";
                    }
                    $html .= '</select></div>';
                }
            } 

            $html .= <<<HTML
                    </div>
                </div>
            HTML;
        }
        $html .=    <<<HTML
                        </div>      

                        <div class="modal-footer">
                            <input type="hidden" name="edit_email" value="{$email}">
                            <input type="hidden" name="edit_title" value="{$title}">
                            <input type="hidden" name="edit_id" value="{$id}">
                            <button type="submit" name="reset" class="btn" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" name="action_edit_workshop">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        HTML;

        return $html;
    }

    // Delete permanently modal
    function delete_modal($id, $base_url) {
        $html = <<<HTML
        <div class="modal fade" id="deleteSingleModal_$id" tabindex="-1" aria-labelledby="deleteSingleModalLabel_$id" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mx-auto">
                <div class="modal-content p-2">
                    <div class="modal-header">
                        <h2 class="modal-title" id="deleteSingleModalLabel_$id">Delete Confirmation</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body text-secondary">
                        <p class="lh-1">Are you absolutely sure you want to permanently delete this?</p>
                        <p class="text-danger lh-1">This action cannot be undone. The item will be lost forever.</p>
                    </div>

                    <form method="POST" action="recycle.php{$base_url}">
                        <div class="modal-footer">
                            <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                            
                            <button type="submit" name="action_delete_perm" value="$id" class="btn btn-danger text-light border-0">
                                Permanently Delete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        HTML;

        return $html;
    }

    // Edit User Details Modal
    function editUser_modal($id, $email, $firstName, $lastName, $dob, $gender, $hometown, $newsletter, $profile, $base_url, $errors = []){
        $modal_id = "editUserModal_$id";
        $maleChecked = ($gender == 'Male') ? 'checked' : '';
        $femaleChecked = ($gender == 'Female') ? 'checked' : '';
        $subscribed = ($newsletter == 'yes') ? 'checked' : '';
        $noSubscribed = ($newsletter == 'no') ? 'checked' : '';

        $firstName_class = isset($errors['firstName']) ? 'is-invalid' : '';
        $lastName_class = isset($errors['lastName']) ? 'is-invalid' : '';
        $dob_class = isset($errors['dob']) ? 'is-invalid' : '';
        $hometown_class = isset($errors['hometown']) ? 'is-invalid' : '';
        
        $firstName_error = isset($errors['firstName']) ? "<div class='invalid-feedback'>{$errors['firstName']}</div>" : '';
        $lastName_error = isset($errors['lastName']) ? "<div class='invalid-feedback'>{$errors['lastName']}</div>" : '';
        $dob_error = isset($errors['dob']) ? "<div class='invalid-feedback'>{$errors['dob']}</div>" : '';
        $hometown_error = isset($errors['hometown']) ? "<div class='invalid-feedback'>{$errors['hometown']}</div>" : '';

        $html = <<<HTML
            <div class="modal fade edit-modal" id="{$modal_id}" tabindex="-1" aria-labelledby="editUserModalLabel_{$id}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered mx-auto">
                    <div class="modal-content p-2">
                        <form method="POST" action="manage_accounts.php{$base_url}">
                            <div class="modal-header">
                                <h2 class="modal-title" id="editUserModalLabel_{$id}">Edit User Details</h2>
                                <button type="submit" name="reset" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body text-secondary me-3">
                                <figure class='rounded-circle overflow-hidden border shadow mx-auto mb-2 position-relative' id="profile-img">
            HTML;
                if ($profile !== null) {
                    $html .= <<<HTML
                                    <img src="profile_images/{$profile}" alt="{$firstName}'s Profile Picture" class='img-fluid'>
                                    <input type="checkbox" name="delete-profile" class="d-none delete-profile-checkbox" id="delete-profile_{$id}">
                                    <label for="delete-profile_{$id}" class="delete-profile d-flex justify-content-center align-items-center position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 opacity-0 rounded-circle" role="button">
                                        <i class="bi bi-trash3 text-light"></i>
                                    </label>
            HTML;
                } else if ($gender == "Male") {
                    $html .= "<img src=\"profile_images/boy.png\" alt=\"{$firstName}'s Profile Picture\" class='img-fluid'>";
                } else {
                    $html .= "<img src=\"profile_images/girl.png\" alt=\"{$firstName}'s Profile Picture\" class='img-fluid'>";
                }

                $html .= <<<HTML
                                </figure>
                                <div class="row">
                                    <div class="form-group col-md-6 mb-2">
                                        <label for="firstName_{$id}" class="fw-bold">First Name</label>
                                        <input type="text" class="form-control {$firstName_class}" id="firstName_{$id}" name="edit_firstName" value="{$firstName}">
                                        $firstName_error
                                    </div>

                                    <div class="form-group col-md-6 mb-2">
                                        <label for="lastName_{$id}" class="fw-bold">Last Name</label>
                                        <input type="text" class="form-control {$lastName_class}" id="lastName_{$id}" name="edit_lastName" value="{$lastName}">
                                        $lastName_error
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <p class="m-0 fw-bold">Email</p>
                                    <p class="m-0">$email</p>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6 mb-2">
                                        <label for="dob_{$id}" class="fw-bold">Date of Birth</label>
                                        <input type="date" class="form-control {$dob_class}" id="dob_{$id}" name="edit_dob" value="{$dob}">
                                        $dob_error
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="form-label d-block fw-bold">Gender</label>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input mt-0" type="radio" id="female_{$id}" name="edit_gender" value="Female" $femaleChecked>
                                            <label class="form-check-label" for="female_{$id}">Female</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input mt-0" type="radio" id="male_{$id}" name="edit_gender" value="Male" $maleChecked>
                                            <label class="form-check-label" for="male_{$id}">Male</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6 mb-2">
                                        <label for="hometown_{$id}" class="fw-bold">Hometown</label>
                                        <input type="text" class="form-control {$hometown_class}" id="hometown_{$id}" name="edit_hometown" placeholder="e.g. Kuching, Sarawak" value="{$hometown}">
                                        $hometown_error
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="form-label d-block fw-bold">Newsletter Subscription</label>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input mt-0" type="radio" id="sub_{$id}" name="edit_newsletter_status" value="yes" $subscribed>
                                            <label class="form-check-label" for="sub_{$id}">Subscribed</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input mt-0" type="radio" id="unsub_{$id}" name="edit_newsletter_status" value="no" $noSubscribed>
                                            <label class="form-check-label" for="unsub_{$id}">Not Subscribed</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <input type="hidden" name="edit_id" value="{$id}">
                                <button type="submit" name="reset" class="btn" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" name="action_edit_user">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            HTML;
                return $html;
    }

    // Extract Text from PDF
    require_once 'pdfparser/alt_autoload.php-dist';

    function extractPDFText($filePath) {
        if (!file_exists($filePath)) {
            return "ERROR: PDF file not found.";
        }

        try {
            // Initialize the Parser object
            $parser = new \Smalot\PdfParser\Parser();
            
            // Parse the PDF file
            $pdf = $parser->parseFile($filePath);
            
            // Extract all text from the PDF
            $text = $pdf->getText();
            
            // Clean up excessive whitespace 
            $text = preg_replace('/\s+/', ' ', $text);
            
            if (trim($text) === '') {
                return "ERROR: Successfully parsed the PDF, but no readable text was extracted. The PDF might contain only images or be protected.";
            }
            
            return $text;

        } catch (\Exception $e) {
            // Catch any exception thrown during parsing (e.g., file corruption, unsupported format)
            return "ERROR: PDF parsing failed using Smalot PDF Parser. Details: " . $e->getMessage();
        }
    }

    // Format PDF
    require_once('tcpdf/tcpdf.php');

    function generateFlowerPdf($commonName, $scientificName, $plantImagePath, $descriptionText) {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator('Root Flower');
        $pdf->SetAuthor('Root Flower');
        $pdf->SetTitle($commonName);
        $pdf->SetMargins(20, 20, 20, true);
        $pdf->AddPage();

        // Logo
        $pdf->Image('img/rootflower.jpg', 15, 8, 15); // Adjust position/size

        // Common Name & Scientific Name
        $pdf->SetFont('times', 'B', 20);
        $pdf->SetXY(34, 26);
        $pdf->Cell(0, 8, $commonName, 0, 1);
        $pdf->SetFont('times', 'I', 14);
        $pdf->SetX(34);
        $pdf->Cell(0, 8, $scientificName, 0, 0);

        // Generated Date 
        $dateText = 'Generated: ' . date('d-M-Y');
        $pdf->SetFont('times', '', 10); 
        $pdf->Cell(0, 5, $dateText, 0, 1, 'R');

        // Plant Image
        if (file_exists($plantImagePath)) {
            $pdf->Image($plantImagePath, 15, 50, 60); // Adjust size
        }

        // Description
        $pdf->SetXY(80, 50);
        $pdf->SetFont('times', '', 12);
        $pdf->MultiCell(0, 6, $descriptionText, 0, 'L', false);

        $pdfOutputDir = __DIR__ . '/../flower_description/';
        if (!is_dir($pdfOutputDir)) {
            mkdir($pdfOutputDir, 0777, true); // create if not exist
        }

        $pdfOutputName = 'generated_' . time() . '.pdf';
        $pdfOutputPath = $pdfOutputDir . $pdfOutputName;
        $pdf->Output($pdfOutputPath, 'F');
        return $pdfOutputName;
    }

    // Generate & save user notification
    function generateUserNotification($conn, $name, $email, $type, $id, $table){
        $msg = '';
        $item_name = '';

        if ($table == 'studentworks_table') {
            $sql = "SELECT w.workshop_title FROM studentworks_table s JOIN workshop_table w ON w.id = s.workshop_id WHERE s.id = $id";
            $result = mysqli_query($conn, $sql);
            if ($row = mysqli_fetch_assoc($result)) {
                $item_name = $row['workshop_title'];
            }
        } elseif ($table == 'workshop_table') {
            $sql = "SELECT workshop_title FROM workshop_table WHERE id = $id";
            $result = mysqli_query($conn, $sql);
            if ($row = mysqli_fetch_assoc($result)) {
                $item_name = $row['workshop_title'];
            }
        }

        switch ($type){
            case 'like':
                $msg = "{$name} liked your work for {$item_name}";
                break;
            case 'comment':
                $msg = "{$name} commented on your work for {$item_name}";
                break;
            case 'workshop_app':
                $msg = "Your workshop registration: {$item_name} has been approved.";
                break;
            case 'workshop_rej':
                $msg = "Your workshop registration: {$item_name} was rejected.";
                break;
            case 'seat_app':
                $msg = "Your seat change request for {$item_name} Workshop has been approved.";
                break;
            case 'seat_rej':
                $msg = "Your seat change request for {$item_name} Workshop was rejected.";
                break;
            case 'stuwork_app':
                $msg = "Congratulations! Your work for {$item_name} has been approved and is now featured.";
                break;
            case 'stuwork_rej':
                $msg = "Your work for {$item_name} was not approved.";
                break;
            case 'workshop_update':
                $msg = "Your workshop details for {$item_name} has been updated.";
                break;
            case 'profile_update':
                $msg = "Your profile details has been updated.";
                break;
        }

        $query = "INSERT INTO notification_table (email, type, message, related_id, related_table) VALUES ('$email', '$type', '$msg', $id, '$table')";
        mysqli_query($conn, $query);
    }

    // Generate & save admin notification
    function generateAdminNotification($conn, $email, $type){
        $msg = '';

        switch ($type){
            case 'workshop_pending':
                $msg = "New workshop submission from {$email} is pending approval.";
                break;
            case 'studentwork_pending':
                $msg = "New student work submission from {$email} is pending approval.";
                break;
            case 'new_user':
                $msg = "New user registered: {$email}.";
                break;
            case 'seat_pending':
                $msg = "Seat change request received from {$email}.";
                break;
        }

        $query = "INSERT INTO notification_table (email, type, message) VALUES ('admin@swin.edu.my', '$type', '$msg')";
        mysqli_query($conn, $query);
    }

    // Retrieve all notification for the login user
    function displayNotification($conn, $email){
        $select = "SELECT * FROM notification_table WHERE email='$email' AND is_read = 0 ORDER BY created_at DESC";
        $result = mysqli_query($conn, $select);

        $unread_notifications = [];

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)){
                $row['url'] = '#';

                switch ($row['related_table']){
                    case 'workshop_table':
                        $row['url'] = 'my_workshops.php';
                        break;
                    case 'studentworks_table':
                        $row['url'] = 'studentwork_detail.php?id='.$row['related_id'];
                        break;
                    case 'user_table':
                        $row['url'] = 'update_profile.php';
                        break;
                }

                switch ($row['type']){
                    // User Notification
                    case 'like':
                        $row['icon'] = "bi-person-heart";
                        break;
                    case 'comment':
                        $row['icon'] = "bi-chat-dots";
                        break;
                    case 'workshop_app':
                        $row['icon'] = "bi-check-circle";
                        break;
                    case 'workshop_rej':
                        $row['icon'] = "bi-x-circle";
                        break;
                    case 'seat_app':
                        $row['icon'] = "bi-calendar-check";
                        break;
                    case 'seat_rej':
                        $row['icon'] = "bi-calendar-x";
                        break;
                    case 'stuwork_app':
                        $row['icon'] = "bi-clipboard-check";
                        break;
                    case 'stuwork_rej':
                        $row['icon'] = "bi-clipboard2-x";
                        break;
                    case 'workshop_update':
                        $row['icon'] = "bi-calendar-week";
                        break;
                    case 'profile_update':
                        $row['icon'] = "bi-person-lock";
                        break;

                    // Admin notification
                    case 'workshop_pending':
                        $row['icon'] = "bi-calendar3";
                        $row['url'] = 'manage_workshop_reg.php';
                        break;
                    case 'studentwork_pending':
                        $row['icon'] = "bi-file-earmark-richtext";
                        $row['url'] = 'manage_studentwork.php';
                        break;
                    case 'new_user':
                        $row['icon'] = "bi-person-plus";
                        $row['url'] = 'manage_accounts.php';
                        break;
                    case 'seat_pending':
                        $row['icon'] = "bi-calendar3-range";
                        $row['url'] = 'manage_workshop_reg.php?status=edited';
                        break;
                }

                $unread_notifications[] = $row;
            }
        }

        $unread_count = count($unread_notifications);

        return ['notifications' => $unread_notifications, 'count' => $unread_count];
    }
?>
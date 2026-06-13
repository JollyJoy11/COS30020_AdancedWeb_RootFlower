<?php
$errors = [];
$intended_workshop = null;
$firstName = '';
$lastName = '';
$contact = '';
$email = '';
$f1_submitted_sessions = [];
$f2_submitted_sessions = [];

$hc_seat = $_POST['hc-seat'] ?? '1';
$hb_seat = $_POST['hb-seat'] ?? '1';
$f1_seat = $_POST['f1-seat'] ?? '1';
$f2_seat = $_POST['f2-seat'] ?? '1';

// Common variables for store into database
$date_to_store = NULL; 
$time_to_store = NULL;
$venue = NULL;
$seats_to_store = 0;

if (isset($_POST['action_register_workshop'])) {
    $intended_workshop = $_POST['workshop_selection'] ?? null;
    $firstName = ucwords(strtolower(sanitise_input($_POST['firstName'])));
    $lastName = ucwords(strtolower(sanitise_input($_POST['lastName'])));
    $contact = sanitise_input($_POST['contact']);
    $email = sanitise_input($_POST['email']);
    
    // First Name
    if (empty($firstName)) {
        $errors['firstName'] = "* First name is required.";
    } else if (!preg_match("/^[A-Za-z\s]+$/", $firstName)) {
        $errors['firstName'] = "* Name can contain only letters and white spaces.";
    }

    // Last Name
    if (empty($lastName)) {
        $errors['lastName'] = "* Last name is required.";
    } else if (!preg_match("/^[A-Za-z\s]+$/", $lastName)) {
        $errors['lastName'] = "* Name can contain only letters and white spaces.";
    }

    // Contact No
    if (empty($contact)) {
        $errors['contact'] = "* Contact number is required.";
    } else if (!preg_match("/^0\d\d-\d{7,8}$/", $contact)) {
        $errors['contact'] = "* Invalid contact number format.";
    }

    // Email
    if (empty($email)) {
        $errors['email'] = "* Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "* Invalid email format.";
    }

    // Check for datetime and seat for different workshop type
    if ($intended_workshop){
        if ($intended_workshop == "Hobby Class"){
            $hc_session_value = $_POST['selected_session_hc'] ?? null;

            if (empty($hc_session_value)) {
                $errors['date'] = "* Please select a session.";
            }

            if (empty($hc_seat) || $hc_seat < 1) {
                $errors['seat'] = "* Number of seat is required.";
            }

            $parts = explode(', ', $hc_session_value);
            $date_to_store = $parts[0] ?? NULL; 
            $time_to_store = $parts[1] ?? NULL;
            $venue = (isset($parts[2]) && isset($parts[3])) ? $parts[2] . ', ' . $parts[3] : $parts[2] ?? NULL;
            $seats_to_store = (int)$hc_seat;
            
        } else if ($intended_workshop == "Handtied Bouquet") {
            $hb_session_value = $_POST['selected_session_hb'] ?? null;

            if (empty($hb_session_value)) {
                $errors['date'] = "* Please select a session.";
            }

            if (empty($hb_seat) || $hb_seat < 1) {
                $errors['seat'] = "* Number of seat is required.";
            }
            
            $parts = explode(', ', $hb_session_value);
            $date_to_store = $parts[0] . ', ' . $parts[1]; 
            $time_to_store = $parts[2] . ', ' . $parts[3];
            $venue = (isset($parts[4]) && isset($parts[5])) ? $parts[4] . ', ' . $parts[5] : $parts[4] ?? NULL;
            $seats_to_store = (int)$hb_seat;
           
        } else if ($intended_workshop == "Florist To Be 1") {
            $f1_batch_name = $_POST['selected_batch_name_f1'] ?? '';
            $venue = $workshops[2]['batches'][$f1_batch_name]['venue'];

            if (empty($f1_seat) || $f1_seat < 1) {
                $errors['seat'] = "* Number of seat is required.";
            }
            
            if ($f1_batch_name) {
                $f1_all_sessions_filled = true;
                $tab_id = str_replace(' ', '-', $f1_batch_name);

                if (strtotime($f1_batch_name) < strtotime('first day of this month')){
                    $errors['batch'] = '* This workshop batch is in the past and cannot be selected.';
                }
                
                $f1_submitted_sessions = []; 

                for ($day = 1; $day <= 4; $day++) {
                    $date_field_name = $tab_id . "-day{$day}-date";
                    $time_field_name = $tab_id . "-day{$day}-time";
                    
                    $date_value = $_POST[$date_field_name] ?? '';
                    $time_value = $_POST[$time_field_name] ?? '';
                    
                    if (empty($date_value) || empty($time_value)) {
                        $f1_all_sessions_filled = false;
                    }

                    $f1_submitted_sessions[$day] = ['date' => $date_value, 'time' => $time_value];
                }
                
                if (!$f1_all_sessions_filled) {
                    $errors['date'] = '* Please select a date and time for all 4 days.';
                }
            }

            $all_dates = array_column($f1_submitted_sessions, 'date');
            $all_times = array_column($f1_submitted_sessions, 'time');

            $date_to_store = implode(', ', $all_dates);
            $time_to_store = implode(', ', $all_times);
            $seats_to_store = (int)$f1_seat;
            
        } else if ($intended_workshop == "Florist To Be 2") {
            $f2_batch_name = $_POST['selected_batch_name_f2'] ?? '';
            $venue = $workshops[3]['batches'][$f2_batch_name]['venue'];

            if (empty($f2_seat) || $f2_seat < 1) {
                $errors['seat'] = "* Number of seat is required.";
            }
            
            if ($f2_batch_name) {
                $f2_all_sessions_filled = true;
                $tab_id = str_replace(' ', '-', $f2_batch_name);

                if (strtotime($f2_batch_name) < strtotime('first day of this month')){
                    $errors['batch'] = '* This workshop batch is in the past and cannot be selected.';
                }
                
                $f2_submitted_sessions = []; 

                for ($day = 1; $day <= 4; $day++) {
                    $date_field_name = $tab_id . "-day{$day}-date";
                    $time_field_name = $tab_id . "-day{$day}-time";
                    
                    $date_value = $_POST[$date_field_name] ?? '';
                    $time_value = $_POST[$time_field_name] ?? '';
                    
                    if (empty($date_value) || empty($time_value)) {
                        $f2_all_sessions_filled = false;
                    }

                    $f2_submitted_sessions[$day] = ['date' => $date_value, 'time' => $time_value];
                }
                
                if (!$f2_all_sessions_filled) {
                    $errors['date'] = '* Please select a date and time for all 4 days.';
                }
            }

            $all_dates = array_column($f2_submitted_sessions, 'date');
            $all_times = array_column($f2_submitted_sessions, 'time');

            $date_to_store = implode(', ', $all_dates);
            $time_to_store = implode(', ', $all_times);
            $seats_to_store = (int)$f2_seat;
        }
    }
    
    // Check Duplicate
    $duplicate_query = "SELECT date, time FROM workshop_table WHERE email = '$email' AND workshop_title = '$intended_workshop' AND trash = 'no' AND (approve_status = 'approved' OR approve_status = 'pending')";
    $duplicate_result = mysqli_query($conn, $duplicate_query);

    if (mysqli_num_rows($duplicate_result) > 0) {
        while ($row = mysqli_fetch_array($duplicate_result)){
            $savedDate = $row['date'];
            $savedTime = $row['time'];

            if (strpos($intended_workshop, 'Florist To Be') === false) {
                if ($savedDate == $date_to_store && $savedTime === $time_to_store){
                    $errors['duplicate'] = "* The person has already registered this workshop.";
                    break;
                }
            } else {
                $firstDate = explode(", ", $savedDate)[0];
                $savedMonth = date("F Y", strtotime($firstDate));

                if ($intended_workshop === 'Florist To Be 1') {
                    $submittedBatch = $_POST['selected_batch_name_f1'] ?? null;
                } elseif ($intended_workshop === 'Florist To Be 2') {
                    $submittedBatch = $_POST['selected_batch_name_f2'] ?? null;
                } else {
                    $submittedBatch = null;
                }

                if ($savedMonth === $submittedBatch){
                    $errors['duplicate'] = "* The person has already registered this workshop.";
                    break;
                }
            } 
        }
    }

    // No errors perform saving
    if (empty($errors)){
        $query = "INSERT INTO workshop_table (email, first_name, last_name, workshop_title, date, time, no_of_seats, contact_number, approve_status) VALUES ('$email', '$firstName', '$lastName', '$intended_workshop', '$date_to_store', '$time_to_store', $seats_to_store, '$contact', 'approved')";
        $result = mysqli_query($conn, $query);

        if ($result){
            $to = $email;
            $subject = "Workshop Registration Confirmation - $intended_workshop"; 
            $message = "Hi $firstName, \n\nThank you for registering the $intended_workshop workshop. Your registration has been received and is currently pending for approval. Here are the details of your registration:\n\nWorkshop Title: $intended_workshop\nDate: $date_to_store\nTime: $time_to_store\nVenue: " . $venue . "\nSeat(s) Reserved: $seats_to_store\n\nOur team will review your registration shortly. You’ll receive a confirmation email once your registration has been approved. \n\nPlease note that you may edit the number of seats in your registration up to 3 days before the workshop date.\n\nIf you have any questions in the meantime, please feel free to reach out to us.\n\nBest regards,\nRoot Flower Team"; 

            if (sendEmail($to, $subject, $message)){
                unset($errors);
                header("Location: manage_workshop_reg.php" . $base_url);
                $alert['success'] = "$intended_workshop registered successfully.";
                $_SESSION['alert'] = $alert;
                exit();
            }
        }
    }
} else if (isset($_POST['reset'])) {
    header("Location: manage_workshop_reg.php" . $base_url);
    exit();
}
?>

<div class="modal fade" id="addWorkshopReg" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mx-auto">
        <div class="modal-content p-2">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . $base_url; ?>">
                <div class="modal-header">
                    <h2 class="modal-title">Register Workshop</h2>
                    <button type="submit" name="reset" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <ul class="nav nav-pills" id="workshop-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <?php 
                                $hc_checked = ($intended_workshop === 'Hobby Class' || !isset($_POST['action_register_workshop'])) ? 'checked' : ''; 
                                $hc_active = ($intended_workshop === 'Hobby Class' || !isset($_POST['action_register_workshop'])) ? 'active' : '';
                            ?>
                            
                            <input type="radio" name="workshop_selection" value="Hobby Class" id="radio_hc" class="d-none" <?php echo $hc_checked; ?> required>
                            <label for="radio_hc" class="nav-link me-2 py-1 px-3 text-light <?php echo $hc_active; ?>" id="hc-tab" data-bs-toggle="tab" data-bs-target="#hc-content" role="button" aria-controls="hc-content" aria-selected="<?php echo $hc_active ? 'true' : 'false'; ?>">Hobby Class</label>
                        </li>
                        
                        <li class="nav-item" role="presentation">
                            <?php 
                                $hb_checked = ($intended_workshop === 'Handtied Bouquet') ? 'checked' : ''; 
                                $hb_active = ($intended_workshop === 'Handtied Bouquet') ? 'active' : ''; 
                            ?>
                            
                            <input type="radio" name="workshop_selection" value="Handtied Bouquet" id="radio_hb" class="d-none" <?php echo $hb_checked; ?> required>
                            <label for="radio_hb" class="nav-link me-2 py-1 px-3 text-light <?php echo $hb_active; ?>" id="hb-tab" data-bs-toggle="tab" data-bs-target="#hb-content" role="button" aria-controls="hb-content" aria-selected="<?php echo $hb_active ? 'true' : 'false'; ?>">Handtied Bouquet</label>
                        </li>
                        
                        <li class="nav-item" role="presentation">
                            <?php 
                                $f1_checked = ($intended_workshop === 'Florist To Be 1') ? 'checked' : ''; 
                                $f1_active = ($intended_workshop === 'Florist To Be 1') ? 'active' : ''; 
                            ?>
                            
                            <input type="radio" name="workshop_selection" value="Florist To Be 1" id="radio_f1" class="d-none" <?php echo $f1_checked; ?> required>
                            <label for="radio_f1" class="nav-link me-2 py-1 px-3 text-light <?php echo $f1_active; ?>" id="f1-tab" data-bs-toggle="tab" data-bs-target="#f1-content" role="button" aria-controls="f1-content" aria-selected="<?php echo $f1_active ? 'true' : 'false'; ?>">Florist To Be 1</label>
                        </li>
                        
                        <li class="nav-item" role="presentation">
                            <?php 
                                $f2_checked = ($intended_workshop === 'Florist To Be 2') ? 'checked' : ''; 
                                $f2_active = ($intended_workshop === 'Florist To Be 2') ? 'active' : ''; 
                            ?>
                            
                            <input type="radio" name="workshop_selection" value="Florist To Be 2" id="radio_f2" class="d-none" <?php echo $f2_checked; ?> required>
                            <label for="radio_f2" class="nav-link me-3 py-1 px-3 text-light <?php echo $f2_active; ?>" id="f2-tab" data-bs-toggle="tab" data-bs-target="#f2-content" role="button" aria-controls="f2-content" aria-selected="<?php echo $f2_active ? 'true' : 'false'; ?>">Florist To Be 2</label>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="workshop-tab-content">
                        <div class="row">
                            <div class="form-group col-md-6 mb-2">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control <?php echo isset($_POST['action_register_workshop']) ? (isset($errors['firstName']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="firstName" name="firstName" value="<?php echo isset($_POST['firstName']) ? $firstName : ''; ?>">
                            </div>
                            <div class="form-group col-md-6 mb-2">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control <?php echo isset($_POST['action_register_workshop']) ? (isset($errors['lastName']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="lastName" name="lastName" value="<?php echo isset($_POST['lastName']) ? $lastName : ''; ?>">
                            </div>
                        </div>

                        <?php if (isset($errors['firstName']) || isset($errors['lastName'])): ?>
                            <div class="small text-danger mb-2">
                                <?php
                                    echo ($errors['firstName'] ?? '') .
                                    (!empty($errors['firstName']) && !empty($errors['lastName']) ? '<br>' : '');

                                    if (($errors['firstName'] ?? null) != ($errors['lastName'] ?? null)) {
                                        echo ($errors['lastName'] ?? '');
                                    }
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="form-group col-md-6 mb-2">
                                <label for="contact">Contact No</label>
                                <input type="text" class="form-control <?php echo isset($_POST['action_register_workshop']) ? (isset($errors['contact']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="contact" name="contact" placeholder="e.g. 012-3456789" value="<?php echo isset($_POST['contact']) ? $contact : ''; ?>">
                                <div class="invalid-feedback">
                                    <?php echo $errors['contact'] ?? ''; ?>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="text" class="form-control <?php echo isset($_POST['action_register_workshop']) ? ((isset($errors['email']) || isset($errors['duplicate'])) ? 'is-invalid' : 'is-valid') : ''; ?>" id="email" name="email" placeholder="e.g. abc@gmail.com" value="<?php echo isset($_POST['email']) ? $email : ''; ?>">
                                <div class="invalid-feedback">
                                    <?php
                                        if (isset($errors['email'])) {
                                            echo $errors['email'];
                                        } elseif (isset($errors['duplicate'])) {
                                            echo $errors['duplicate'];
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Hobby Class -->
                        <div class="tab-pane <?php echo ($intended_workshop === 'Hobby Class' || !isset($_POST['action_register_workshop'])) ? 'show active' : ''; ?>" id="hc-content" role="tabpanel" aria-labelledby="hc-tab">
                            <div class="row">
                                <div class="form-group col-md-8 mb-2">
                                    Session
                                    
                                    <select class="form-select py-0 <?php echo (isset($_POST['action_register_workshop']) && $intended_workshop === 'Hobby Class') ? ((isset($errors['date']) || isset($errors['duplicate'])) ? 'is-invalid' : 'is-valid') : ''; ?>" name="selected_session_hc">
                                        <option value="" disabled selected>-- Select a session --</option>

                                        <?php 
                                        $hcWorkshop = $workshops[0];
                                        $today = date('Y-m-d');

                                        foreach ($hcWorkshop['schedule'] as $sessionIndex => $sessionDetails){
                                            $displayDate = date("d M Y", strtotime($sessionDetails['date']));
                                            $displayText = "{$displayDate} | {$sessionDetails['time']}";$venue = $sessionDetails['venue'];
                                            $option_value = "{$sessionDetails['date']}, {$sessionDetails['time']}, {$venue}";
                                            $selected_attr = ($option_value === $hc_session_value) ? 'selected' : '';

                                            if ($sessionDetails['date'] >= $today){
                                                echo "<option value='$option_value' $selected_attr>" . htmlspecialchars($displayText) . "</option>";
                                            } else {
                                                $displayText .= " [PASSED]";
                                                echo "<option value='$option_value' disabled $selected_attr>" . htmlspecialchars($displayText) . "</option>";
                                            }
                                        } ?>
                                    </select>

                                    <?php if (isset($errors['date']) && $intended_workshop === 'Hobby Class'): ?>
                                        <div class="small text-danger mb-2">
                                            <?php echo $errors['date']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group col-md-4 mb-2">
                                    <label for="hc-seat">Number of Seat(s)</label>
                                    <input type="number" class="form-control w-50 <?php echo isset($_POST['action_register_workshop']) ? (isset($errors['seat']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="hc-seat" name="hc-seat" value="<?php echo isset($_POST['hc-seat']) ? $hc_seat : '1'; ?>" min="1" max="20">
                                </div>
                            </div>
                        </div>

                        <!-- Handtied Bouquet -->
                        <div class="tab-pane <?php echo ($intended_workshop === 'Handtied Bouquet') ? 'show active' : ''; ?>" id="hb-content" role="tabpanel" aria-labelledby="hb-tab">
                            <div class="row">
                                <div class="form-group col-md-8 mb-2">
                                    Session

                                    <select class="form-select py-0 <?php echo (isset($_POST['action_register_workshop']) && $intended_workshop === 'Handtied Bouquet') ? ((isset($errors['date']) || isset($errors['duplicate'])) ? 'is-invalid' : 'is-valid') : ''; ?>" name="selected_session_hb">
                                        <option value="" disabled selected>-- Select a session --</option>

                                        <?php 
                                        $hbWorkshop = $workshops[1];
                                        $today = date('Y-m-d');

                                        foreach ($hbWorkshop['schedule'] as $sessionIndex => $sessionDetails){
                                            $firstDayDate = $sessionDetails['days'][0]['date'];
                                            $day1Date = date("d M Y", strtotime($sessionDetails['days'][0]['date']));
                                            $day1Time = $sessionDetails['days'][0]['time'];

                                            $day2Date = date("d M Y", strtotime($sessionDetails['days'][1]['date']));
                                            $day2Time = $sessionDetails['days'][1]['time'];

                                            $venue = $sessionDetails['venue'];

                                            $option_value = "{$sessionDetails['days'][0]['date']}, {$sessionDetails['days'][1]['date']}, {$day1Time}, {$day2Time}, {$venue}";

                                            $displayText = "Day 1: {$day1Date} at {$day1Time} | Day 2: {$day2Date} at {$day2Time}";
                                            $selected_attr = ($option_value === $hb_session_value) ? 'selected' : '';

                                            if ($firstDayDate >= $today){
                                                echo "<option value='$option_value' $selected_attr>" . htmlspecialchars($displayText) . "</option>";
                                            } else {
                                                $displayText .= " [PASSED]";
                                                echo "<option value='$option_value' disabled $selected_attr>" . htmlspecialchars($displayText) . "</option>";
                                            }
                                        } ?>
                                    </select>
                                    
                                    <?php if (isset($errors['date']) && $intended_workshop === 'Handtied Bouquet'): ?>
                                        <div class="small text-danger mb-2">
                                            <?php echo $errors['date']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group col-md-4 mb-2">
                                    <label for="hb-seat">Number of Seat(s)</label>
                                    <input type="number" class="form-control w-50 <?php echo isset($_POST['action_register_workshop']) ? (isset($errors['seat']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="hb-seat" name="hb-seat" value="<?php echo isset($_POST['hb-seat']) ? $hb_seat : '1'; ?>" min="1" max="20">
                                </div>
                            </div>
                        </div>

                        <!-- Florist To Be 1 -->
                        <div class="tab-pane <?php echo ($intended_workshop === 'Florist To Be 1') ? 'show active' : ''; ?>" id="f1-content" role="tabpanel" aria-labelledby="f1-tab">
                            <div class="row">
                                <div class="form-group col-md-6 mb-2">
                                    <label for="f1-seat">Number of Seat(s)</label>
                                    <input type="number" class="form-control w-50 <?php echo isset($_POST['action_register_workshop']) ? (isset($errors['seat']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="f1-seat" name="f1-seat" value="<?php echo isset($_POST['f1-seat']) ? $f1_seat : '1'; ?>" min="1" max="20">
                                </div>
                            </div>

                            <ul class="nav nav-pills fs-6 d-flex justify-content-center my-2" id="f1-batch-tabs" role="tablist">
                                <?php 
                                $selected_batch_f1 = $_POST['selected_batch_name_f1'] ?? array_key_first($workshops[2]['batches']);
                                foreach ($workshops[2]['batches'] as $batchName => $batchData): 
                                    $tab_id = str_replace(' ', '-', $batchName);
                                    $f1_batch_checked = ($batchName === $selected_batch_f1) ? 'checked' : ''; 
                                    $f1_batch_active = ($batchName === $selected_batch_f1) ? 'active' : '';?>
                                    <li class="nav-item" role="presentation">
                                        <input type="radio" name="selected_batch_name_f1" value="<?= $batchName ?>" id="radio_f1_<?= $tab_id ?>" class="d-none" <?= $f1_batch_checked ?> required>
                
                                        <label for="radio_f1_<?= $tab_id ?>" class="nav-link mx-1 py-1 px-2 <?= $f1_batch_active ?>" id="<?= $tab_id ?>-f1-tab-btn" data-bs-toggle="tab" data-bs-target="#<?= $tab_id ?>-f1-content" role="button" aria-controls="<?= $tab_id ?>-f1-content" aria-selected="<?= $f1_batch_active ? 'true' : 'false'; ?>">
                                            <?= $batchName ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if (isset($errors['batch']) && $intended_workshop === 'Florist To Be 1'): ?>
                                <div class="small text-danger mb-2">
                                    <?php echo $errors['batch']; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mb-2">
                                Session
                                
                                <div class="tab-content" id="f1-batch-tab-content">
                                    <?php 
                                    $selectedWorkshop = $workshops[2]; 
                                    foreach ($selectedWorkshop['batches'] as $session => $batchData): ?>
                                    <?php $tab_id = str_replace(' ', '-', $session); ?>
                                        <div class="tab-pane <?= ($session === $selected_batch_f1) ? 'show active' : '' ?>" id="<?= $tab_id ?>-f1-content" role="tabpanel">
                                            <div class="row">
                                                <?php for ($dayNumber = 1; $dayNumber <= 4; $dayNumber++):
                                                    $info = $batchData['days'][$dayNumber]; 
                                                    $selected_date = $f1_submitted_sessions[$dayNumber]['date'] ?? '';$selected_time = $f1_submitted_sessions[$dayNumber]['time'] ?? ''; 
                                                    ?>
                                                    <div class="form-group col-md-6 d-flex">
                                                        <label for="<?= $tab_id ?>-day<?= $dayNumber ?>-date" class="w-50 mb-2">&emsp;Day <?= $dayNumber ?></label>
                                                        <select id="<?= $tab_id ?>-day<?= $dayNumber ?>-date" name="<?= $tab_id ?>-day<?= $dayNumber ?>-date" class="form-select py-0 mb-2 <?php echo (isset($_POST['action_register_workshop']) && $intended_workshop === 'Florist To Be 1') ? ((empty($selected_date) || isset($errors['duplicate'])) ? 'is-invalid' : 'is-valid') : ''; ?>"> 
                                                            <option value="" disabled selected>-- Select a date --</option>
                                                            <?php foreach ($info['dates'] as $date): ?>
                                                                <option value="<?= $date ?>" <?php echo ($date === $selected_date) ? 'selected' : ''; ?>>
                                                                    <?= date("d M Y", strtotime($date)) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="form-group col-md-6 d-flex">
                                                        <select id="<?= $tab_id ?>-day<?= $dayNumber ?>-time" name="<?= $tab_id ?>-day<?= $dayNumber ?>-time" class="form-select py-0 mb-2 <?php echo (isset($_POST['action_register_workshop']) && $intended_workshop === 'Florist To Be 1') ? ((empty($selected_time) || isset($errors['duplicate'])) ? 'is-invalid' : 'is-valid') : ''; ?>">
                                                            <option value="" disabled selected>-- Select a time slot --</option>
                                                            <?php 
                                                            $options = ["8:30 AM - 12:30 PM", "2:30 PM - 6:30 PM"];
                                                            foreach ($options as $time_slot): ?>
                                                                <option value="<?= $time_slot ?>" <?php echo ($time_slot === $selected_time) ? 'selected' : ''; ?>>
                                                                    <?= $time_slot ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                <?php endfor; ?>
                                            </div> 
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (isset($errors['date']) && !isset($errors['batch']) && $intended_workshop === 'Florist To Be 1'): ?>
                                    <div class="small text-danger mb-2">
                                        <?php echo $errors['date']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Florist To Be 2 -->
                        <div class="tab-pane <?php echo ($intended_workshop === 'Florist To Be 2') ? 'show active' : ''; ?>" id="f2-content" role="tabpanel" aria-labelledby="f2-tab">
                            <div class="row">
                                <div class="form-group col-md-6 mb-2">
                                    <label for="f2-seat">Number of Seat(s)</label>
                                    <input type="number" class="form-control w-50 <?php echo isset($_POST['action_register_workshop']) ? (isset($errors['seat']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="f2-seat" name="f2-seat" value="<?php echo isset($_POST['f2-seat']) ? $f2_seat : '1'; ?>" min="1" max="20">
                                </div>
                            </div>

                            <ul class="nav nav-pills fs-6 d-flex justify-content-center my-2" id="f2-batch-tabs" role="tablist">
                                <?php 
                                $selected_batch_f2 = $_POST['selected_batch_name_f2'] ?? array_key_first($workshops[3]['batches']);
                                foreach ($workshops[3]['batches'] as $batchName => $batchData): 
                                    $tab_id = str_replace(' ', '-', $batchName);
                                    $f2_batch_checked = ($batchName === $selected_batch_f2) ? 'checked' : ''; 
                                    $f2_batch_active = ($batchName === $selected_batch_f2) ? 'active' : '';?>
                                    <li class="nav-item" role="presentation">
                                        <input type="radio" name="selected_batch_name_f2" value="<?= $batchName ?>" id="radio_f2_<?= $tab_id ?>" class="d-none" <?= $f2_batch_checked ?> required>
                
                                        <label for="radio_f2_<?= $tab_id ?>" class="nav-link mx-1 py-1 px-2 <?= $f2_batch_active ?>" id="<?= $tab_id ?>-f2-tab-btn" data-bs-toggle="tab" data-bs-target="#<?= $tab_id ?>-f2-content" role="button" aria-controls="<?= $tab_id ?>-f2-content" aria-selected="<?= $f2_batch_active ? 'true' : 'false'; ?>">
                                            <?= $batchName ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if (isset($errors['batch']) && $intended_workshop === 'Florist To Be 2'): ?>
                                <div class="small text-danger mb-2">
                                    <?php echo $errors['batch']; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mb-2">
                                Session
                                
                                <div class="tab-content" id="f2-batch-tab-content">
                                    <?php 
                                    $selectedWorkshop = $workshops[3]; 
                                    foreach ($selectedWorkshop['batches'] as $session => $batchData): ?>
                                    <?php $tab_id = str_replace(' ', '-', $session); ?>
                                        <div class="tab-pane <?= ($session === $selected_batch_f2) ? 'show active' : '' ?>" id="<?= $tab_id ?>-f2-content" role="tabpanel">
                                            <div class="row">
                                                <?php for ($dayNumber = 1; $dayNumber <= 4; $dayNumber++):
                                                    $info = $batchData['days'][$dayNumber];
                                                    $selected_date = $f2_submitted_sessions[$dayNumber]['date'] ?? '';
                                                    $selected_time = $f2_submitted_sessions[$dayNumber]['time'] ?? ''; 
                                                    ?>
                                                    <div class="form-group col-md-6 d-flex">
                                                        <label for="<?= $tab_id ?>-day<?= $dayNumber ?>-date" class="w-50 mb-2">&emsp;Day <?= $dayNumber ?></label>
                                                        <select id="<?= $tab_id ?>-day<?= $dayNumber ?>-date" name="<?= $tab_id ?>-day<?= $dayNumber ?>-date" class="form-select py-0 mb-2 <?php echo (isset($_POST['action_register_workshop']) && $intended_workshop === 'Florist To Be 2') ? ((empty($selected_date) || isset($errors['duplicate'])) ? 'is-invalid' : 'is-valid') : ''; ?>">
                                                            <option value="" disabled selected>-- Select a date --</option>
                                                            <?php foreach ($info['dates'] as $date): ?>
                                                                <option value="<?= $date ?>" <?php echo ($date === $selected_date) ? 'selected' : ''; ?>>
                                                                    <?= date("d M Y", strtotime($date)) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="form-group col-md-6 d-flex">
                                                        <select id="<?= $tab_id ?>-day<?= $dayNumber ?>-time" name="<?= $tab_id ?>-day<?= $dayNumber ?>-time" class="form-select py-0 mb-2 <?php echo (isset($_POST['action_register_workshop']) && $intended_workshop === 'Florist To Be 2') ? ((empty($selected_time) || isset($errors['duplicate'])) ? 'is-invalid' : 'is-valid') : ''; ?>">
                                                            <option value="" disabled selected>-- Select a time slot --</option>
                                                            <?php 
                                                            $options = ["8:30 AM - 12:30 PM", "2:30 PM - 6:30 PM"];
                                                            foreach ($options as $time_slot): ?>
                                                                <option value="<?= $time_slot ?>" <?php echo ($time_slot === $selected_time) ? 'selected' : ''; ?>>
                                                                    <?= $time_slot ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                <?php endfor; ?>
                                            </div> 
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (isset($errors['date']) && !isset($errors['batch']) && $intended_workshop === 'Florist To Be 2'): ?>
                                    <div class="small text-danger mb-2">
                                        <?php echo $errors['date']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" name="reset" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="action_register_workshop">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($_POST['action_register_workshop']) && !empty($errors)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var myModal = new bootstrap.Modal(document.getElementById('addWorkshopReg'));
    myModal.show();

    const activeWorkshopTab = document.querySelector('#workshop-tabs .nav-link.active');

    if (activeWorkshopTab) {
        const tabInstance = new bootstrap.Tab(activeWorkshopTab);
        tabInstance.show();
        const activeBatchTab = document.querySelector('#workshop-tab-content .tab-pane.active .nav-pills .nav-link.active');
        
        if (activeBatchTab) {
            new bootstrap.Tab(activeBatchTab).show();
        }
    }
});
</script>
<?php endif; ?>
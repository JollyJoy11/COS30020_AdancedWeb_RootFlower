<!DOCTYPE html>

<html lang="en">
<!-- Description: About -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 2/10/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>About Assignment | Root Flower</title>
	<meta charset="utf-8"/>
	<meta name="author" content="Joanne Chin Jia Xuan"/>
	<meta name="description" content="Root Flower is a creative florist hub offering fresh floral products, inspiring workshops, and a platform for students to showcase their floral artistry. Discover, learn, and create with us.">	
	<meta name="keywords" content="Root Flower, florist, kuching florist, flower, flower bouquet, florist workshop"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<link rel="icon" type="image/x-icon" href="img/favicon.ico"/> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> <!-- Bootstrap link-->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css"> <!-- Bootstrap icon link-->
	<link rel="stylesheet" type="text/css" href="style/style.css"/>
</head>

<body class="text-secondary fs-5">
	<div id="side-nav" class="fixed-top vh-100">
        <a href="index.php"><img src="img/rootflower.jpg" alt="Root Flower Logo" height="150"></a>
    </div>

    <article class="mx-5 mb-5">
		<h1 class="text-center mt-4">About</h1>
		<ol class="lh-2">
			<li>
				<strong>What tasks have you not attempted or not completed? </strong>
				<ul>
					<li class="pb-2">None – all core assignment requirements have been implemented.</li>
				</ul>
			</li>
			<li>
				<strong>Which parts did you have trouble with? </strong>
				<ul>
					<li class="pb-2">
						<strong>CSS Dynamic Grid Gallery</strong> (flower.php)<br>
						The main challenge was creating a grid where all cards share the same height but have flexible widths based on the image. My first attempt using Masonry.js resulted in uneven row alignment, so I replaced it with a Flexbox-based approach. The container uses <code>flex-wrap: wrap</code> to let items wrap naturally into new rows. Each card also applies <code>flex-grow</code>, <code>flex-shrink</code>, and <code>flex-basis: auto</code>, allowing the card width to adjust to the image while maintaining a consistent height. This produced a clean, responsive gallery layout without external libraries.
					</li>
					<li class="pb-2">
						<strong>Admin Site Individual Record Actions Modals </strong> (manage_accounts.php, manage_workshop_reg.php, recycle.php)<br>
						The main challenge was handling single-record actions (like deleting, editing or rejecting) which requires modal while also supporting bulk actions in the same table. Initially, placing the modal HTML inside each table row caused conflicts with the form for bulk actions. I solved this by creating a PHP function to generate modals dynamically for each record and storing them in an array. After the table form, I printed all modals at once using <code>implode()</code>. This ensures each record has its own modal with a unique ID, allowing the admin to perform individual actions without breaking the bulk actions or the table structure.
					</li>
					<li class="pb-2">
						<strong>Seat Change Request Workflow Design</strong> (my_workshops.php, manage_workshop_reg.php)<br>
						One challenge was managing seat changes for workshops while keeping the approval workflow clear. When a user requests a change, the system first checks the current number of seats and the status of any pending edits. If a pending request exists, the user is notified to wait before submitting another change. If the request differs from the current seat count, the system updates either the <code>pending_seats</code> and <code>edit_status</code> fields (for approved or rejected workshops) or directly sets the workshop to pending approval (for pending workshops). Once the admin reviews the request, they can approve or reject it. Approval updates the seat count, marks the request as completed, and triggers an email notification to the user confirming the new seat allocation. Rejection resets the pending values and notifies the user of the rejection along with the reason. This workflow ensures accurate seat management, prevents conflicting requests, and maintains clear communication between users and admins.
					</li>
					<li class="pb-2">
						<strong>Flower Description PDF Text Extraction</strong> (flower.php)<br>
						Another challenge I faced was extracting readable text from uploaded PDF description files. My initial approach used a custom function with <code>file_get_contents()</code>, but the output became unreadable because modern PDF files often store text in compressed or encoded structures rather than plain text. To solve this, I integrated the Smalot PDF Parser library, which correctly interprets PDF encoding, text streams, and structure. This allowed me to reliably extract clean, usable text for generating flower descriptions and storing the extracted data in my system.
					</li>
				</ul>
			</li>
			<li>
				<strong>What would you like to do better next time? </strong>
				<ul>
					<li class="pb-2">
						<strong>Security</strong><br>
						Currently, I use MySQL procedural functions without prepared statements. In the future, I want to improve security by adopting prepared statements to prevent SQL injection.
					</li>
					<li class="pb-2">
						<strong>Site-wide Search</strong><br>
						The user side does not yet include a global search feature for products, flowers, or student work. I plan to add a unified search bar in the future.
					</li>
					<li class="pb-2">
						<strong>Product Purchase</strong><br>
						The site does not support adding products to a cart or completing purchases. In the future, I hope to implement full e-commerce features, including order management and custom bouquet orders.
					</li>
					<li class="pb-2">
						<strong>Promotion System</strong><br>
						With newsletter subscription already implemented, I plan to extend this to a full promotion system where users can receive special offers via email.
					</li>
					<li class="pb-2">
						<strong>Flower Identification</strong><br>
						I plan to integrate AI-assisted flower recognition to help users identify flowers and see which products involve those species.
					</li>
					<li class="pb-2">
						<strong>Product Customization</strong><br>
						Currently, users can arrange flowers in 3D using the AR feature and take screenshots of their creations. For future improvements, this functionality could be integrated with the product purchasing system, allowing users to customize and order bouquets directly based on their AR designs. This would enhance the user experience by bridging creative design with real-world product ordering.
					</li>
				</ul>
			</li>
			<li>
				<strong>What extension features/extra challenges have you done, or attempted, when creating the site?</strong>
				<ul>
					<li class="pb-2">
						<strong>Likes & Comments </strong>(studentwork_detail.php)<br>
						Users can like and comment on student works, and creators receive notifications for interactions.
					</li>
					<li class="pb-2">
						<strong>Bulk Action </strong>(Admin Side)<br>
						Admins can perform bulk actions, such as delete, approve or reject, by selecting multiple records using checkboxes. They may also use the header checkbox in the <code>&lt;th&gt;</code> to select all records at once before submitting the chosen action.
					</li>
					<li class="pb-2">
						<strong>Workshop Dashboard </strong>(my_workshops.php)<br>
						This dashboard displays all workshops and student works associated with the logged-in user, including pending, approved, and rejected sessions. Users can submit seat change requests for upcoming workshops, which the admin can approve or reject.
					</li>
					<li class="pb-2">
						<strong>Unread Notification Dropdown </strong>(header.php, header_admin.php)<br>
						Unread notifications are displayed as a dropdown, with counts highlighted to alert users. Clicking a notification marks it as read and redirects to the relevant page. Notifications are generated dynamically using backend functions to track user and admin actions.
					</li>
					<li class="pb-2">
						<strong>Downloadable Flower PDFs</strong> (flower.php)<br>
						Uploaded flower description PDFs are parsed with the Smalot PDF parser, and the data is combined with images to generate a clean, downloadable PDF using TCPDF. This feature helps users keep organized records of contributed flowers.
					</li>
					<li class="pb-2">
						<strong>Recycle Bin</strong> (recycle.php)<br>
						All admin-manageable tables include a recycle bin system. Deleted items are temporarily stored with timestamps, allowing recovery if needed. This prevents accidental data loss and maintains database integrity.
					</li>
					<li class="pb-2">
						<strong>Table Search and Sort </strong>(Admin Side)<br>
						Admins can search records efficiently and sort table columns using a dynamic function, improving record management across different tables.
					</li>
					<li class="pb-2">
						<strong>Tab Filter and Form Filter </strong>(Admin Side)<br>
						Bootstrap tab navigation pills is used for classify records by status (Pending, Approved, Rejected), while the form filter refines results by workshop, batch, likes, and date range. The filters dynamically adjust the SQL query to retrieve only the matching records.
					</li>
					<li class="pb-2">
						<strong>AR Flower Arrangement & History Tracking</strong> (ar_flowerarrangement.php, save_arrangement.php, manage_ar.php)<br>
						Users can arrange flowers in 3D using hand gestures via MediaPipe and Three.js. Creations can be saved and downloaded, and the admin can track usage in the AR history page. This feature could be extended to allow product customization and ordering in the future.
					</li>
					<li class="pb-2">
						<strong>Profile Image Cropping </strong> (update_profile.php)<br>
						Cropper.js allows users to crop uploaded profile images to a 1:1 ratio for a consistent display across the site.
					</li>
					<li class="pb-2">
						<strong>Reset Forgot Password  </strong> (forgot_password.php, reset_password.php)<br>
						Users can reset their password via an emailed link containing a unique token, ensuring secure account recovery.
					</li>
					<li class="pb-2">
						<strong>Newsletter Subscribe </strong>(newsletter_subscribe.php)<br>
						Users can subscribe to the newsletter by entering their email.
					</li>
					<li class="pb-2">
						<strong>Login Remember Me </strong> (functions.php)<br>
						A “Remember Me” feature was implemented to keep users logged in across browser sessions. When enabled, the system stores a secure token that automatically restores the user’s session without requiring them to log in again.
					</li>
					<li class="pb-2">
						<strong>Send Email </strong>(functions.php)<br>
						Automated emails are sent during important actions such as successful user registration, workshop confirmations, and other important notifications.
					</li>
					<li class="pb-2">
						<strong>Cart & Order History Pages </strong> (cart.php, order_history.php)<br>
						Although implemented as dummy pages, the cart and order history demonstrate the intended workflow for future e-commerce functionality, including product selection and order tracking.
					</li>
				</ul>
			</li>
		</ol>
		<p class="text-center"><a href="https://youtu.be/y_YWSObQ2BQ" target="_blank" class="btn btn-primary"><i class="bi bi-person-video3 me-2"></i>Video Presentation</a></p>
    </article>

	<?php include "include/nologin_footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>
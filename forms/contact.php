<?php

// Set content type for AJAX response
header('Content-Type: application/json');

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Get form data
  $name = isset($_POST['name']) ? trim($_POST['name']) : '';
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
  $message = isset($_POST['message']) ? trim($_POST['message']) : '';

  // Validate required fields
  if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(['error' => 1, 'message' => 'All fields are required.']);
    exit;
  }

  // Validate email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 1, 'message' => 'Invalid email address.']);
    exit;
  }

  // Email configuration
  $to = 'burukmaedot24@gmail.com';
  $email_subject = "Portfolio Contact: " . $subject;
  $email_body = "You have received a new message from your portfolio contact form.\n\n";
  $email_body .= "Name: " . $name . "\n";
  $email_body .= "Email: " . $email . "\n";
  $email_body .= "Subject: " . $subject . "\n\n";
  $email_body .= "Message:\n" . $message . "\n\n";
  $email_body .= "--\nThis email was sent from your portfolio contact form.";

  // Email headers
  $headers = "From: " . $email . "\r\n";
  $headers .= "Reply-To: " . $email . "\r\n";
  $headers .= "X-Mailer: PHP/" . phpversion();

  // Send email
  if (mail($to, $email_subject, $email_body, $headers)) {
    echo json_encode(['error' => 0, 'message' => 'OK']);
  } else {
    echo json_encode(['error' => 1, 'message' => 'Failed to send message. Please try again.']);
  }

} else {
  echo json_encode(['error' => 1, 'message' => 'Invalid request method.']);
}

?>

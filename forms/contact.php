<?php

// Check if this is an AJAX request
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Set content type based on request type
if ($is_ajax) {
  header('Content-Type: application/json');
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Get form data
  $name = isset($_POST['name']) ? trim($_POST['name']) : '';
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
  $message = isset($_POST['message']) ? trim($_POST['message']) : '';

  // Validate required fields
  if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    if ($is_ajax) {
      echo json_encode(['error' => 1, 'message' => 'All fields are required.']);
    } else {
      // Redirect back with error
      header('Location: ../index.html?status=error&message=' . urlencode('All fields are required.'));
    }
    exit;
  }

  // Validate email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if ($is_ajax) {
      echo json_encode(['error' => 1, 'message' => 'Invalid email address.']);
    } else {
      // Redirect back with error
      header('Location: ../index.html?status=error&message=' . urlencode('Invalid email address.'));
    }
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
    if ($is_ajax) {
      echo json_encode(['error' => 0, 'message' => 'OK']);
    } else {
      // Redirect back with success
      header('Location: ../index.html?status=success&message=' . urlencode('Message sent successfully!'));
    }
  } else {
    if ($is_ajax) {
      echo json_encode(['error' => 1, 'message' => 'Failed to send message. Please try again.']);
    } else {
      // Redirect back with error
      header('Location: ../index.html?status=error&message=' . urlencode('Failed to send message. Please try again.'));
    }
  }

} else {
  if ($is_ajax) {
    echo json_encode(['error' => 1, 'message' => 'Invalid request method.']);
  } else {
    // Redirect back
    header('Location: ../index.html');
  }
}

?>

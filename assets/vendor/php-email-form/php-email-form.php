<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PHP_Email_Form {

  public $to;
  public $from_name;
  public $from_email;
  public $subject;
  public $smtp = null;
  public $ajax = false;
  public $cc = array();
  public $bcc = array();
  public $honeypot = '';
  public $recaptcha_secret = '';
  public $recaptcha_response = '';

  private $messages = array();
  private $attachments = array();

  public function __construct() {
    $this->to = '';
    $this->from_name = '';
    $this->from_email = '';
    $this->subject = '';
  }

  public function add_message($value, $label, $length = 0) {
    $this->messages[] = array('value' => $value, 'label' => $label, 'length' => $length);
  }

  public function add_attachment($file_path, $file_name = '') {
    $this->attachments[] = array('path' => $file_path, 'name' => $file_name);
  }

  public function send() {

    $response = array('error' => 0, 'message' => 'OK');

    try {

      // Validate required fields
      if (empty($this->to) || empty($this->from_name) || empty($this->from_email) || empty($this->subject)) {
        throw new Exception('Required fields are missing');
      }

      // Validate email format
      if (!filter_var($this->from_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
      }

      // Check honeypot
      if (!empty($this->honeypot) && !empty($_POST[$this->honeypot])) {
        throw new Exception('Spam detected');
      }

      // Check reCAPTCHA
      if (!empty($this->recaptcha_secret) && !empty($this->recaptcha_response)) {
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_data = array(
          'secret' => $this->recaptcha_secret,
          'response' => $this->recaptcha_response
        );

        $recaptcha_options = array(
          'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($recaptcha_data)
          )
        );

        $recaptcha_context = stream_context_create($recaptcha_options);
        $recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
        $recaptcha_json = json_decode($recaptcha_result);

        if (!$recaptcha_json->success) {
          throw new Exception('reCAPTCHA verification failed');
        }
      }

      // Create email content
      $email_content = $this->build_email_content();

      // Send email
      $this->send_email($email_content);

    } catch (Exception $e) {
      $response['error'] = 1;
      $response['message'] = $e->getMessage();
    }

    if ($this->ajax) {
      header('Content-Type: application/json');
      echo json_encode($response);
      return;
    }

    return $response;
  }

  private function build_email_content() {
    $content = "You have received a new message from your website contact form.\n\n";

    foreach ($this->messages as $message) {
      $value = $message['value'];
      $label = $message['label'];
      $length = $message['length'];

      if ($length > 0 && strlen($value) > $length) {
        $value = substr($value, 0, $length) . '...';
      }

      $content .= $label . ": " . $value . "\n";
    }

    $content .= "\n--\nThis email was sent from your website contact form.";

    return $content;
  }

  private function send_email($content) {
    $mail = new PHPMailer(true);

    try {
      // Server settings
      if ($this->smtp) {
        $mail->isSMTP();
        $mail->Host = $this->smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->smtp['username'];
        $mail->Password = $this->smtp['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $this->smtp['port'];
      } else {
        $mail->isMail();
      }

      // Recipients
      $mail->setFrom($this->from_email, $this->from_name);
      $mail->addAddress($this->to);

      // CC and BCC
      foreach ($this->cc as $cc_email) {
        $mail->addCC($cc_email);
      }

      foreach ($this->bcc as $bcc_email) {
        $mail->addBCC($bcc_email);
      }

      // Attachments
      foreach ($this->attachments as $attachment) {
        if (file_exists($attachment['path'])) {
          $mail->addAttachment($attachment['path'], $attachment['name']);
        }
      }

      // Content
      $mail->isHTML(false);
      $mail->Subject = $this->subject;
      $mail->Body = $content;

      $mail->send();

    } catch (Exception $e) {
      throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
  }

}

?>

<?php
require_once __DIR__ . '/vendor/autoload.php';

// Define the root URL
$rootUrl = 'http://localhost:8000';

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['subject'], $_POST['message'])) {
    $mail = new PHPMailer\PHPMailer();

    // SMTP configuration (update these values)
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your_email@example.com';
    $mail->Password = 'your_password';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipient
    $mail->setFrom('your_email@example.com', 'Vuyani Magibisela');
    $mail->addAddress('your_email@example.com', 'Vuyani Magibisela');

    // Content
    $mail->isHTML(true);
    $mail->Subject = $_POST['subject'];
    $mail->Body = "Name: {$_POST['name']}<br>Email: {$_POST['email']}<br>Message: {$_POST['message']}";
    
    if ($mail->send()) {
        redirect("$rootUrl/contact?success");
    } else {
        redirect("$rootUrl/contact?error");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact - Vuyani Magibisela</title>
</head>
<body>
    <div class="contact-form">
        <?php if (isset($_GET['success'])) { ?>
            <div class="success-message">
                <h2>Message Sent Successfully!</h2>
                <p>Thank you for your message. I'll get back to you soon.</p>
            </div>
        <?php } elseif (isset($_GET['error'])) { ?>
            <div class="error-message">
                <h2>Message Not Sent</h2>
                <p>There was an error sending your message. Please try again.</p>
            </div>
        <?php } ?>

        <h2>Contact Me</h2>
        <form method="POST" action="<?php echo $rootUrl; ?>/contact">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" required>
            </div>

            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>

            <button type="submit">Send Message</button>
        </form>
    </div>
</body>
</html>

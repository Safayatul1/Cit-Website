<?php
// save_message.php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        die("Please fill in all required fields.");
    }

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: contact.html?success=1");
        exit;
    } else {
        echo "Error saving message: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}

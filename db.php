<?php
$host     = "localhost";
$username = "safazofe_portfolio_user";   // <-- your MySQL username
$password = "S@fa01703229884";        // <-- the password you created
$database = "safazofe_portfolio_db";     // <-- your DB name

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>

<?php
session_start();
require 'db.php'; // uses your existing DB connection

// Change this to any strong password you like
const ADMIN_PASSWORD = 'S@fa0170329884';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle login form submit
$login_error = '';
if (isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['logged_in'] = true;
    } else {
        $login_error = 'Wrong password. Please try again.';
    }
}

// If not logged in, show a simple login form and exit
if (empty($_SESSION['logged_in'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login | Safayatul</title>
  <style>
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background:#f5f6fa; display:flex; align-items:center; justify-content:center;
      min-height:100vh; margin:0;
    }
    .card {
      background:#fff; padding:24px 26px; border-radius:12px;
      box-shadow:0 10px 30px rgba(0,0,0,0.08); width:100%; max-width:360px;
    }
    h1 { font-size:1.3rem; margin-bottom:14px; }
    label { display:block; font-weight:600; margin-bottom:6px; }
    input[type="password"] {
      width:100%; padding:10px 12px; border-radius:8px; border:1px solid #d0d5e2;
      font:inherit;
    }
    button {
      margin-top:12px; width:100%; padding:10px; border-radius:8px; border:none;
      background:#333; color:#fff; font-weight:600; cursor:pointer;
    }
    button:hover { background:#111; }
    .error { color:#b00020; font-size:.9rem; margin-top:8px; }
    .muted { font-size:.85rem; color:#666; margin-top:10px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Admin Login</h1>
    <?php if ($login_error): ?>
      <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
    <?php endif; ?>
    <form method="POST">
      <label for="password">Admin Password</label>
      <input type="password" id="password" name="password" required>
      <button type="submit">Login</button>
    </form>
    <p class="muted">This page lets you view messages sent from your contact form.</p>
  </div>
</body>
</html>
<?php
exit;
endif;

// If we reach here, user is logged in → fetch some data
// Total messages
$totalResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM messages");
$totalRow = mysqli_fetch_assoc($totalResult);
$totalMessages = (int)($totalRow['total'] ?? 0);

// Last 10 messages
$messagesResult = mysqli_query(
    $conn,
    "SELECT id, name, email, subject, message, created_at
     FROM messages
     ORDER BY created_at DESC
     LIMIT 10"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | Safayatul</title>
  <style>
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      margin:0; background:#f4f5fb; color:#222;
    }
    header {
      background:#333; color:#fff; padding:14px 20px;
      display:flex; justify-content:space-between; align-items:center;
    }
    header h1 { font-size:1.2rem; margin:0; }
    header a {
      color:#fff; text-decoration:none; font-size:.9rem;
      border:1px solid rgba(255,255,255,0.4); padding:6px 10px; border-radius:999px;
    }
    header a:hover { background:rgba(255,255,255,0.08); }

    .wrap {
      max-width:1100px; margin:20px auto 30px; padding:0 16px;
    }
    .cards {
      display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
      gap:16px; margin-bottom:22px;
    }
    .card {
      background:#fff; border-radius:12px; padding:14px 16px;
      box-shadow:0 10px 26px rgba(15,23,42,0.06);
      border:1px solid #e3e6f0;
    }
    .card h2 { font-size:.95rem; margin:0 0 6px; color:#555; }
    .card .big {
      font-size:1.8rem; font-weight:700; margin-bottom:4px;
    }
    .card p { margin:0; font-size:.85rem; color:#777; }

    h2.section-title {
      margin:18px 0 8px; font-size:1.05rem;
    }

    table {
      width:100%; border-collapse:collapse; background:#fff;
      border-radius:12px; overflow:hidden;
      box-shadow:0 10px 26px rgba(15,23,42,0.06);
      border:1px solid #e3e6f0;
    }
    th, td {
      padding:10px 12px; font-size:.9rem; text-align:left;
      border-bottom:1px solid #edf0f7;
      vertical-align:top;
    }
    th { background:#f8f9ff; font-weight:600; color:#444; }
    tr:last-child td { border-bottom:none; }
    .msg-body {
      max-width:360px; white-space:pre-wrap; word-wrap:break-word;
      font-size:.9rem;
    }
    .pill {
      display:inline-block; padding:2px 8px; border-radius:999px;
      font-size:.8rem; background:#eef2ff; color:#3949ab;
    }
    .muted { font-size:.85rem; color:#777; margin-top:6px; }
  </style>
</head>
<body>
  <header>
    <h1>Admin Dashboard</h1>
    <a href="admin.php?logout=1">Logout</a>
  </header>

  <div class="wrap">
    <div class="cards">
      <div class="card">
        <h2>Total Messages</h2>
        <div class="big"><?php echo $totalMessages; ?></div>
        <p>Total contact form submissions stored in the database.</p>
      </div>
    </div>

    <h2 class="section-title">Latest Messages</h2>
    <?php if ($totalMessages === 0): ?>
      <p class="muted">No messages yet. Try submitting the contact form on your site.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>When</th>
            <th>Name &amp; Email</th>
            <th>Subject</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($messagesResult)): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td>
              <span class="pill">
                <?php echo htmlspecialchars($row['created_at']); ?>
              </span>
            </td>
            <td>
              <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
              <span class="muted"><?php echo htmlspecialchars($row['email']); ?></span>
            </td>
            <td><?php echo htmlspecialchars($row['subject']); ?></td>
            <td class="msg-body">
              <?php echo nl2br(htmlspecialchars($row['message'])); ?>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>

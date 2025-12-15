<?php
session_start();
require 'db.php';

// Change this password
const ADMIN_PASSWORD = 'SafayAdmin123';

// Logout
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: admin.php');
  exit;
}

// Login
$login_error = '';
if (isset($_POST['password'])) {
  if ($_POST['password'] === ADMIN_PASSWORD) {
    $_SESSION['logged_in'] = true;
  } else {
    $login_error = 'Wrong password. Please try again.';
  }
}

// If not logged in → login form
if (empty($_SESSION['logged_in'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#f5f6fa;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
    .card{background:#fff;padding:24px 26px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.08);width:100%;max-width:360px;}
    h1{font-size:1.3rem;margin:0 0 14px;}
    label{display:block;font-weight:600;margin-bottom:6px;}
    input{width:100%;padding:10px 12px;border-radius:8px;border:1px solid #d0d5e2;font:inherit;}
    button{margin-top:12px;width:100%;padding:10px;border-radius:8px;border:none;background:#333;color:#fff;font-weight:600;cursor:pointer;}
    button:hover{background:#111;}
    .error{color:#b00020;font-size:.9rem;margin-top:8px;}
    .muted{font-size:.85rem;color:#666;margin-top:10px;}
  </style>
</head>
<body>
  <div class="card">
    <h1>Admin Login</h1>
    <?php if ($login_error): ?><div class="error"><?php echo htmlspecialchars($login_error); ?></div><?php endif; ?>
    <form method="POST">
      <label for="password">Admin Password</label>
      <input type="password" id="password" name="password" required>
      <button type="submit">Login</button>
    </form>
    <p class="muted">View and manage contact messages.</p>
  </div>
</body>
</html>
<?php
exit;
endif;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $id = (int)($_POST['id'] ?? 0);
  $status = $_POST['status'] ?? '';

  $allowed = ['in_progress', 'completed', 'declined'];
  if ($id > 0 && in_array($status, $allowed, true)) {
    $stmt = mysqli_prepare($conn, "UPDATE messages SET status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    mysqli_stmt_execute($stmt);
  }

  header('Location: admin.php');
  exit;
}

// Dashboard counts
$countTotal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM messages"))['c'] ?? 0;
$countInProgress = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM messages WHERE status='in_progress'"))['c'] ?? 0;
$countCompleted = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM messages WHERE status='completed'"))['c'] ?? 0;
$countDeclined = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM messages WHERE status='declined'"))['c'] ?? 0;

// Messages list
$result = mysqli_query(
  $conn,
  "SELECT id, created_at, name, email, subject, message, status
   FROM messages
   ORDER BY created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;margin:0;background:#f4f5fb;color:#222;}
    header{background:#333;color:#fff;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;}
    header h1{font-size:1.2rem;margin:0;}
    header a{color:#fff;text-decoration:none;font-size:.9rem;border:1px solid rgba(255,255,255,.4);padding:6px 10px;border-radius:999px;}
    header a:hover{background:rgba(255,255,255,.08);}

    .wrap{max-width:1200px;margin:20px auto 30px;padding:0 16px;}
    .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:18px;}
    .card{background:#fff;border-radius:12px;padding:14px 16px;box-shadow:0 10px 26px rgba(15,23,42,.06);border:1px solid #e3e6f0;}
    .card h2{font-size:.95rem;margin:0 0 6px;color:#555;}
    .card .big{font-size:1.7rem;font-weight:800;margin:0;}

    table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 10px 26px rgba(15,23,42,.06);border:1px solid #e3e6f0;}
    th,td{padding:10px 12px;font-size:.9rem;text-align:left;border-bottom:1px solid #edf0f7;vertical-align:top;}
    th{background:#f8f9ff;font-weight:700;color:#444;}
    tr:last-child td{border-bottom:none;}
    .msg-body{max-width:420px;white-space:pre-wrap;word-wrap:break-word;}

    .pill{display:inline-block;padding:3px 10px;border-radius:999px;font-size:.8rem;font-weight:600;}
    .pill.in_progress{background:#fff3cd;color:#7a5b00;border:1px solid #ffe7a5;}
    .pill.completed{background:#d1fae5;color:#0f5132;border:1px solid #a7f3d0;}
    .pill.declined{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca;}

    .status-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
    .status-actions select{padding:8px 10px;border-radius:10px;border:1px solid #dcdfe6;background:#f9fafc;font:inherit;}
    .status-actions button{padding:8px 10px;border-radius:10px;border:1px solid #d7def1;background:#f7f9ff;color:#0b2f66;cursor:pointer;font-weight:600;}
    .status-actions button:hover{background:#eef4ff;}
    .muted{color:#666;font-size:.85rem;}
  </style>
</head>
<body>
  <header>
    <h1>Admin Dashboard</h1>
    <a href="admin.php?logout=1">Logout</a>
  </header>

  <div class="wrap">
    <div class="cards">
      <div class="card"><h2>Total Messages</h2><p class="big"><?php echo (int)$countTotal; ?></p></div>
      <div class="card"><h2>In Progress</h2><p class="big"><?php echo (int)$countInProgress; ?></p></div>
      <div class="card"><h2>Completed</h2><p class="big"><?php echo (int)$countCompleted; ?></p></div>
      <div class="card"><h2>Declined</h2><p class="big"><?php echo (int)$countDeclined; ?></p></div>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Date</th>
          <th>Name & Email</th>
          <th>Subject</th>
          <th>Message</th>
          <th>Status</th>
          <th>Update</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><span class="muted"><?php echo htmlspecialchars($row['created_at']); ?></span></td>
            <td>
              <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
              <span class="muted"><?php echo htmlspecialchars($row['email']); ?></span>
            </td>
            <td><?php echo htmlspecialchars($row['subject']); ?></td>
            <td class="msg-body"><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
            <td>
              <span class="pill <?php echo htmlspecialchars($row['status']); ?>">
                <?php
                  if ($row['status'] === 'completed') echo 'Completed';
                  elseif ($row['status'] === 'declined') echo 'Declined';
                  else echo 'In Progress';
                ?>
              </span>
            </td>
            <td>
              <form method="POST" class="status-actions">
                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                <select name="status" aria-label="Select status">
                  <option value="in_progress" <?php if ($row['status']==='in_progress') echo 'selected'; ?>>In Progress</option>
                  <option value="completed" <?php if ($row['status']==='completed') echo 'selected'; ?>>Completed</option>
                  <option value="declined" <?php if ($row['status']==='declined') echo 'selected'; ?>>Declined</option>
                </select>
                <button type="submit" name="update_status" value="1">Save</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>

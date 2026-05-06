<?php
session_start();
$db = new SQLite3('../rt.db');

$error = "";

if (isset($_POST['login'])) {
  $user = $_POST['username'];
  $pass = $_POST['password'];

  $data = $db->querySingle("SELECT * FROM users WHERE username='$user'", true);

  if ($data && password_verify($pass, $data['password'])) {
    $_SESSION['login'] = true;
    $_SESSION['user'] = $user;
    header("Location: ../index.php");
    exit;
  } else {
    $error = "Username atau password salah!";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login - Mediajago12</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      height: 100vh;
      background: linear-gradient(135deg, #0f172a, #1e293b);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-box {
      background: #1e293b;
      padding: 40px;
      width: 350px;
      border-radius: 16px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.5);
      text-align: center;
      animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(20px);}
      to {opacity: 1; transform: translateY(0);}
    }

    .login-box h2 {
      color: #fff;
      margin-bottom: 25px;
    }

    .input-group {
      margin-bottom: 15px;
      text-align: left;
    }

    .input-group label {
      color: #cbd5f5;
      font-size: 13px;
    }

    .input-group input {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: none;
      border-radius: 8px;
      background: #334155;
      color: white;
    }

    .input-group input:focus {
      outline: 2px solid #3b82f6;
    }

    .btn {
      width: 100%;
      padding: 12px;
      background: #3b82f6;
      border: none;
      border-radius: 10px;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
      margin-top: 10px;
    }

    .btn:hover {
      background: #2563eb;
    }

    .error {
      color: #f87171;
      margin-bottom: 10px;
      font-size: 14px;
    }

    .footer {
      margin-top: 15px;
      font-size: 13px;
      color: #94a3b8;
    }

    .footer a {
      color: #3b82f6;
      text-decoration: none;
    }
  </style>
</head>

<body>

<div class="login-box">
  <h2>🔐 Mediajago12 Login</h2>

  <?php if ($error): ?>
    <div class="error"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="input-group">
      <label>Username</label>
      <input name="username" required>
    </div>

    <div class="input-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>

    <button class="btn" name="login">Login</button>
  </form>

  <div class="footer">
    Belum punya akun? <a href="register.php">Daftar</a>
  </div>
</div>

</body>
</html>
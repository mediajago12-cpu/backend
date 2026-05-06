<?php
$db = new SQLite3('../rt.db');

if (isset($_POST['register'])) {
  $user = $_POST['username'];
  $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $db->exec("INSERT INTO users (username, password)
             VALUES ('$user', '$pass')");

  echo "Berhasil daftar. <a href='login.php'>Login</a>";
}
?>

<form method="POST">
  <h2>Register</h2>
  <input name="username" placeholder="Username">
  <input type="password" name="password" placeholder="Password">
  <button name="register">Daftar</button>
</form>
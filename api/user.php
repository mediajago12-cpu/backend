<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");

// ================= DB =================
$dbPath = __DIR__ . "/../database/mediajago12.db";

if (!file_exists($dbPath)) {
  echo json_encode([
    "status" => "error",
    "msg" => "Database tidak ditemukan"
  ]);
  exit;
}

$conn = new SQLite3($dbPath);

// ================= METHOD =================
$method = $_SERVER['REQUEST_METHOD'];


// ================= GET =================
if ($method === 'GET') {

  $result = $conn->query("SELECT id, username, role FROM users");

  $data = [];
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $data[] = $row;
  }

  echo json_encode([
    "status" => "success",
    "data" => $data
  ]);
  exit;
}


// ================= POST =================
elseif ($method === 'POST') {

  // support JSON + form-data
  $input = $_POST;
  if (empty($input)) {
    $input = json_decode(file_get_contents("php://input"), true);
  }

  $username = $input['username'] ?? '';
  $password = $input['password'] ?? '';
  $role = $input['role'] ?? 'user';

  if (!$username || !$password) {
    echo json_encode([
      "status" => "error",
      "msg" => "Username & password wajib"
    ]);
    exit;
  }

  // cek duplikat
  $check = $conn->prepare("SELECT id FROM users WHERE username=?");
  $check->bindValue(1, $username);
  $res = $check->execute();

  if ($res->fetchArray()) {
    echo json_encode([
      "status" => "error",
      "msg" => "Username sudah ada"
    ]);
    exit;
  }

  $hash = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
  $stmt->bindValue(1, $username);
  $stmt->bindValue(2, $hash);
  $stmt->bindValue(3, $role);

  $ok = $stmt->execute();

  echo json_encode([
    "status" => $ok ? "success" : "error",
    "msg" => $ok ? "User berhasil ditambahkan" : "Gagal tambah user"
  ]);
  exit;
}


// ================= PUT (FULL UPDATE) =================
elseif ($method === 'PUT') {

  $input = json_decode(file_get_contents("php://input"), true);

  $id       = $input['id'] ?? null;
  $username = $input['username'] ?? '';
  $password = $input['password'] ?? '';
  $role     = $input['role'] ?? 'user';

  if (!$id || !$username) {
    echo json_encode([
      "status" => "error",
      "msg" => "Data tidak lengkap"
    ]);
    exit;
  }

  // ================= UPDATE =================

  if (!empty($password)) {
    // 🔐 kalau password diisi → update semua
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
      UPDATE users 
      SET username = ?, password = ?, role = ?
      WHERE id = ?
    ");

    $stmt->bindValue(1, $username);
    $stmt->bindValue(2, $hash);
    $stmt->bindValue(3, $role);
    $stmt->bindValue(4, $id);

  } else {
    // 🔥 password kosong → jangan diubah
    $stmt = $conn->prepare("
      UPDATE users 
      SET username = ?, role = ?
      WHERE id = ?
    ");

    $stmt->bindValue(1, $username);
    $stmt->bindValue(2, $role);
    $stmt->bindValue(3, $id);
  }

  $ok = $stmt->execute();

  echo json_encode([
    "status" => $ok ? "success" : "error",
    "msg" => $ok ? "User berhasil diupdate" : "Gagal update"
  ]);
  exit;
}

// ================= DELETE =================
elseif ($method === 'DELETE') {

  $id = $_GET['id'] ?? null;

  if (!$id) {
    echo json_encode([
      "status" => "error",
      "msg" => "ID tidak ditemukan"
    ]);
    exit;
  }

  $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
  $stmt->bindValue(1, $id);

  $ok = $stmt->execute();

  echo json_encode([
    "status" => $ok ? "success" : "error",
    "msg" => $ok ? "User berhasil dihapus" : "Gagal hapus"
  ]);
  exit;
}


// ================= INVALID =================
else {
  echo json_encode([
    "status" => "error",
    "msg" => "Method tidak diizinkan"
  ]);
  exit;
}
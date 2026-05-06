<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json");

// ================= CORS =================
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// ================= DB =================
$db = new SQLite3(__DIR__ . '/../database/mediajago12.db');

// ================= PATH =================
$dirKtp = __DIR__ . "/upload/ktp/";
$dirKk  = __DIR__ . "/upload/kk/";

if (!is_dir($dirKtp)) mkdir($dirKtp, 0777, true);
if (!is_dir($dirKk)) mkdir($dirKk, 0777, true);

// ================= RESPONSE =================
function response($status, $message, $data = null) {
  ob_clean();
  echo json_encode([
    "status" => $status,
    "message" => $message,
    "data" => $data
  ]);
  exit;
}

// ================= METHOD =================
$method = $_SERVER['REQUEST_METHOD'];

// ================= GET =================
if ($method === "GET") {

  $res = $db->query("SELECT * FROM warga ORDER BY id DESC");
  $data = [];

  while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $data[] = $row;
  }

  response("success", "Data warga", $data);
}

// ================= POST (ADD) =================
if ($method === "POST") {

  $nama    = $_POST['nama'] ?? '';
  $nik_ktp = $_POST['nik_ktp'] ?? '';
  $nik_kk  = $_POST['nik_kk'] ?? '';
  $no_hp   = $_POST['no_hp'] ?? '';

  if (!$nama || !$nik_ktp || !$nik_kk) {
    response("error", "Data wajib diisi");
  }

  $foto_ktp = "";
  $foto_kk  = "";

  // 🔥 upload KTP
  if (!empty($_FILES['foto_ktp']['name'])) {
    if ($_FILES['foto_ktp']['error'] === 0) {
      $foto_ktp = time() . "_" . $_FILES['foto_ktp']['name'];
      move_uploaded_file($_FILES['foto_ktp']['tmp_name'], $dirKtp . $foto_ktp);
    }
  }

  // 🔥 upload KK
  if (!empty($_FILES['foto_kk']['name'])) {
    if ($_FILES['foto_kk']['error'] === 0) {
      $foto_kk = time() . "_" . $_FILES['foto_kk']['name'];
      move_uploaded_file($_FILES['foto_kk']['tmp_name'], $dirKk . $foto_kk);
    }
  }

  $stmt = $db->prepare("
    INSERT INTO warga (nama, nik_ktp, nik_kk, no_hp, foto_ktp, foto_kk, created_at)
    VALUES (:nama, :nik_ktp, :nik_kk, :no_hp, :foto_ktp, :foto_kk, datetime('now'))
  ");

  $stmt->bindValue(':nama', $nama);
  $stmt->bindValue(':nik_ktp', $nik_ktp);
  $stmt->bindValue(':nik_kk', $nik_kk);
  $stmt->bindValue(':no_hp', $no_hp);
  $stmt->bindValue(':foto_ktp', $foto_ktp);
  $stmt->bindValue(':foto_kk', $foto_kk);

  $stmt->execute();

  response("success", "Berhasil tambah warga");
}

// ================= PUT (UPDATE) =================
if ($method === "PUT") {

  parse_str(file_get_contents("php://input"), $_PUT);

  $id      = $_PUT['id'] ?? '';
  $nama    = $_PUT['nama'] ?? '';
  $nik_ktp = $_PUT['nik_ktp'] ?? '';
  $nik_kk  = $_PUT['nik_kk'] ?? '';
  $no_hp   = $_PUT['no_hp'] ?? '';

  if (!$id) response("error", "ID wajib");

  $stmt = $db->prepare("
    UPDATE warga SET
      nama = :nama,
      nik_ktp = :nik_ktp,
      nik_kk = :nik_kk,
      no_hp = :no_hp
    WHERE id = :id
  ");

  $stmt->bindValue(':nama', $nama);
  $stmt->bindValue(':nik_ktp', $nik_ktp);
  $stmt->bindValue(':nik_kk', $nik_kk);
  $stmt->bindValue(':no_hp', $no_hp);
  $stmt->bindValue(':id', $id);

  $stmt->execute();

  response("success", "Berhasil update");
}

// ================= DELETE =================
if ($method === "DELETE") {

  $id = $_GET['id'] ?? '';

  if (!$id) response("error", "ID kosong");

  $db->exec("DELETE FROM warga WHERE id = $id");

  response("success", "Berhasil hapus");
}

// ================= FALLBACK =================
response("error", "Method tidak dikenali");
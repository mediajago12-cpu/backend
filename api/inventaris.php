<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// HANDLE PREFLIGHT
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ================= KONEKSI DB =================
$dbPath = __DIR__ . '/../database/mediajago12.db';

if (!file_exists($dbPath)) {
  echo json_encode([
    "status" => "error",
    "msg" => "Database tidak ditemukan"
  ]);
  exit;
}

$db = new SQLite3($dbPath);


// ================= GET =================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  $res = $db->query("SELECT * FROM inventaris ORDER BY id DESC");

  $data = [];
  while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $data[] = $row;
  }

  echo json_encode([
    "status" => "success",
    "data" => $data
  ]);
  exit;
}


// ================= POST =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // AMBIL DATA AMAN
  $id      = $_POST['id'] ?? null;
  $nama    = $_POST['nama_barang'] ?? '';
  $jumlah  = $_POST['jumlah'] ?? '';
  $kondisi = $_POST['kondisi'] ?? 'Baik';
  $ket     = $_POST['keterangan'] ?? '';

  // ================= DELETE =================
  if (isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {

    if (!$id) {
      echo json_encode([
        "status" => "error",
        "msg" => "ID tidak ditemukan"
      ]);
      exit;
    }

    $stmt = $db->prepare("DELETE FROM inventaris WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    if (!$stmt->execute()) {
      echo json_encode([
        "status" => "error",
        "msg" => $db->lastErrorMsg()
      ]);
      exit;
    }

    echo json_encode([
      "status" => "success",
      "msg" => "Data berhasil dihapus"
    ]);
    exit;
  }


  // ================= VALIDASI =================
  if ($nama === '' || $jumlah === '') {
    echo json_encode([
      "status" => "error",
      "msg" => "Nama & jumlah wajib diisi"
    ]);
    exit;
  }

  if (!is_numeric($jumlah)) {
    echo json_encode([
      "status" => "error",
      "msg" => "Jumlah harus angka"
    ]);
    exit;
  }


  // ================= UPDATE =================
  if ($id) {

    $stmt = $db->prepare("
      UPDATE inventaris SET
        nama_barang = :nama,
        jumlah = :jumlah,
        kondisi = :kondisi,
        keterangan = :ket
      WHERE id = :id
    ");

    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':nama', $nama, SQLITE3_TEXT);
    $stmt->bindValue(':jumlah', $jumlah, SQLITE3_INTEGER);
    $stmt->bindValue(':kondisi', $kondisi, SQLITE3_TEXT);
    $stmt->bindValue(':ket', $ket, SQLITE3_TEXT);

    if (!$stmt->execute()) {
      echo json_encode([
        "status" => "error",
        "msg" => $db->lastErrorMsg()
      ]);
      exit;
    }

    echo json_encode([
      "status" => "success",
      "msg" => "Data berhasil diupdate"
    ]);
    exit;
  }


  // ================= INSERT =================
  $stmt = $db->prepare("
    INSERT INTO inventaris (nama_barang, jumlah, kondisi, keterangan)
    VALUES (:nama, :jumlah, :kondisi, :ket)
  ");

  $stmt->bindValue(':nama', $nama, SQLITE3_TEXT);
  $stmt->bindValue(':jumlah', $jumlah, SQLITE3_INTEGER);
  $stmt->bindValue(':kondisi', $kondisi, SQLITE3_TEXT);
  $stmt->bindValue(':ket', $ket, SQLITE3_TEXT);

  if (!$stmt->execute()) {
    echo json_encode([
      "status" => "error",
      "msg" => $db->lastErrorMsg()
    ]);
    exit;
  }

  echo json_encode([
    "status" => "success",
    "msg" => "Data berhasil ditambahkan"
  ]);
  exit;
}
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0);
}

try {

  // ================= DB =================
  $dbPath = __DIR__ . '/../database/mediajago12.db';

  if (!file_exists($dbPath)) {
    throw new Exception("Database tidak ditemukan");
  }

  $db = new SQLite3($dbPath);
  $method = $_SERVER['REQUEST_METHOD'];

  // ================= GET =================
  if ($method === "GET") {

    $res = $db->query("SELECT * FROM kas ORDER BY id DESC");

    $data = [];
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
      $data[] = $row;
    }

    $saldo = $db->querySingle("
      SELECT IFNULL(SUM(
        CASE 
          WHEN jenis = 'masuk' THEN jumlah
          WHEN jenis = 'keluar' THEN -jumlah
          ELSE 0
        END
      ),0) FROM kas
    ");

    echo json_encode([
      "status" => "success",
      "data" => $data,
      "saldo" => $saldo
    ]);
    exit;
  }

  // ================= POST (ADD & EDIT) =================
  if ($method === "POST") {

    // 🔥 ambil dari FORM / JSON
    $input = $_POST;

    if (empty($input)) {
      $input = json_decode(file_get_contents("php://input"), true) ?? [];
    }

    $id          = $input['id'] ?? null;
    $nama        = trim($input['nama'] ?? '');
    $keterangan  = trim($input['keterangan'] ?? '');
    $jumlah      = $input['jumlah'] ?? 0;

    // 🔥 SUPPORT jenis / tipe
    $jenis       = $input['jenis'] ?? $input['tipe'] ?? 'masuk';

    $tanggal     = $input['tanggal'] ?? date("Y-m-d");

    // ================= VALIDASI =================
    if (!$nama || !$jumlah) {
      throw new Exception("Nama & jumlah wajib!");
    }

    if (!is_numeric($jumlah)) {
      throw new Exception("Jumlah harus angka");
    }

    if (!in_array($jenis, ['masuk', 'keluar'])) {
      throw new Exception("Jenis tidak valid");
    }

    // ================= EDIT =================
    if ($id) {

      $stmt = $db->prepare("
        UPDATE kas SET
          nama = :nama,
          keterangan = :keterangan,
          jumlah = :jumlah,
          jenis = :jenis,
          tanggal = :tanggal
        WHERE id = :id
      ");

      $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
      $stmt->bindValue(':nama', $nama, SQLITE3_TEXT);
      $stmt->bindValue(':keterangan', $keterangan, SQLITE3_TEXT);
      $stmt->bindValue(':jumlah', $jumlah, SQLITE3_INTEGER);
      $stmt->bindValue(':jenis', $jenis, SQLITE3_TEXT);
      $stmt->bindValue(':tanggal', $tanggal, SQLITE3_TEXT);

      if (!$stmt->execute()) {
        throw new Exception($db->lastErrorMsg());
      }

      echo json_encode([
        "status" => "success",
        "msg" => "Data berhasil diupdate"
      ]);
      exit;
    }

    // ================= INSERT =================
    $stmt = $db->prepare("
      INSERT INTO kas (nama, keterangan, jumlah, jenis, tanggal)
      VALUES (:nama, :keterangan, :jumlah, :jenis, :tanggal)
    ");

    $stmt->bindValue(':nama', $nama, SQLITE3_TEXT);
    $stmt->bindValue(':keterangan', $keterangan, SQLITE3_TEXT);
    $stmt->bindValue(':jumlah', $jumlah, SQLITE3_INTEGER);
    $stmt->bindValue(':jenis', $jenis, SQLITE3_TEXT);
    $stmt->bindValue(':tanggal', $tanggal, SQLITE3_TEXT);

    if (!$stmt->execute()) {
      throw new Exception($db->lastErrorMsg());
    }

    echo json_encode([
      "status" => "success",
      "msg" => "Data berhasil ditambahkan"
    ]);
    exit;
  }

  // ================= DELETE =================
  if ($method === "DELETE") {

    parse_str($_SERVER['QUERY_STRING'], $query);
    $id = $query['id'] ?? null;

    if (!$id) {
      throw new Exception("ID tidak ditemukan");
    }

    $stmt = $db->prepare("DELETE FROM kas WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    if (!$stmt->execute()) {
      throw new Exception($db->lastErrorMsg());
    }

    echo json_encode([
      "status" => "success",
      "msg" => "Data berhasil dihapus"
    ]);
    exit;
  }

} catch (Exception $e) {
  echo json_encode([
    "status" => "error",
    "msg" => $e->getMessage()
  ]);
}
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// ================= PREFLIGHT =================
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// ================= KONEKSI DB =================
try {
  $dbPath = __DIR__ . "/../database/mediajago12.db";

  $conn = new PDO("sqlite:" . $dbPath);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (Exception $e) {
  echo json_encode([
    "error" => "DB Error",
    "msg" => $e->getMessage()
  ]);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {

  switch ($method) {

    // ================= GET =================
    case 'GET':

      if (isset($_GET['user_id'])) {
        $stmt = $conn->prepare("
          SELECT surat.*, users.username AS nama
          FROM surat
          LEFT JOIN users ON users.id = surat.user_id
          WHERE surat.user_id = ?
          ORDER BY surat.id DESC
        ");
        $stmt->execute([$_GET['user_id']]);
      } else {
        $stmt = $conn->query("
          SELECT surat.*, users.username AS nama
          FROM surat
          LEFT JOIN users ON users.id = surat.user_id
          ORDER BY surat.id DESC
        ");
      }

      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      break;


    // ================= POST =================
    case 'POST':
      $data = json_decode(file_get_contents("php://input"), true);

      if (!$data) {
        echo json_encode(["error" => "Invalid JSON"]);
        exit;
      }

      // 🔥 NOMOR OTOMATIS
      $tahun = date("Y");

      $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM surat 
        WHERE strftime('%Y', tanggal) = ?
      ");
      $stmt->execute([$tahun]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      $no = str_pad($row['total'] + 1, 3, "0", STR_PAD_LEFT);
      $nomor = $no . "/RT01/" . $tahun;

      // 🔥 INSERT
      $stmt = $conn->prepare("
        INSERT INTO surat (user_id, jenis, keterangan, tanggal, nomor, status, created_at)
        VALUES (?, ?, ?, date('now'), ?, 'Diproses', datetime('now'))
      ");

      $stmt->execute([
        $data['user_id'] ?? null,
        $data['jenis'] ?? '',
        $data['keterangan'] ?? '',
        $nomor
      ]);

      echo json_encode([
        "message" => "Surat berhasil dibuat",
        "nomor" => $nomor
      ]);
      break;


    // ================= PUT =================
    case 'PUT':
      $data = json_decode(file_get_contents("php://input"), true);

      if (!$data || !isset($data['id'])) {
        echo json_encode(["error" => "Data tidak valid"]);
        exit;
      }

      $stmt = $conn->prepare("
        UPDATE surat SET status = ?
        WHERE id = ?
      ");

      $stmt->execute([
        $data['status'] ?? 'Diproses',
        $data['id']
      ]);

      echo json_encode([
        "message" => "Status berhasil diupdate"
      ]);
      break;


    // ================= DELETE =================
    case 'DELETE':
      $data = json_decode(file_get_contents("php://input"), true);

      if (!$data || !isset($data['id'])) {
        echo json_encode(["error" => "ID tidak ditemukan"]);
        exit;
      }

      $stmt = $conn->prepare("
        DELETE FROM surat WHERE id = ?
      ");
      $stmt->execute([$data['id']]);

      echo json_encode([
        "message" => "Surat berhasil dihapus"
      ]);
      break;


    default:
      echo json_encode([
        "error" => "Method tidak didukung"
      ]);
      break;
  }

} catch (Exception $e) {
  echo json_encode([
    "error" => "Server Error",
    "msg" => $e->getMessage()
  ]);
}
?>
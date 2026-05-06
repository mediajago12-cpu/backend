<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ================= GET DATA =================
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    $data = $_POST;
}

$action   = $data["action"] ?? "";
$username = trim($data["username"] ?? "");
$password = trim($data["password"] ?? "");

// ================= VALIDASI =================
if ($action !== "login" || !$username || !$password) {
    echo json_encode([
        "status" => "error",
        "msg" => "Request tidak valid"
    ]);
    exit;
}

try {
    // ================= KONEK SQLITE =================
    $dbPath = __DIR__ . "/../database/mediajago12.db";

    if (!file_exists($dbPath)) {
        throw new Exception("Database tidak ditemukan: " . $dbPath);
    }

    $db = new PDO("sqlite:" . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ================= QUERY =================
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "status" => "error",
            "msg" => "User tidak ditemukan"
        ]);
        exit;
    }

    // ================= CEK PASSWORD =================
    if (!password_verify($password, $user["password"])) {
        echo json_encode([
            "status" => "error",
            "msg" => "Password salah"
        ]);
        exit;
    }

    unset($user["password"]);

    echo json_encode([
        "status" => "success",
        "user" => $user
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "msg" => $e->getMessage()
    ]);
}
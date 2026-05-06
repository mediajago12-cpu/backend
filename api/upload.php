<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// 🔥 folder tujuan (sesuai struktur kamu sekarang)
$folder = __DIR__ . "/upload/media/";

// kalau folder belum ada → buat otomatis
if (!file_exists($folder)) {
    mkdir($folder, 0777, true);
}

// cek file
if (!isset($_FILES['file'])) {
    echo json_encode([
        "status" => "error",
        "msg" => "File tidak ditemukan"
    ]);
    exit;
}

$file = $_FILES['file'];

// validasi sederhana
if ($file['error'] !== 0) {
    echo json_encode([
        "status" => "error",
        "msg" => "Upload error"
    ]);
    exit;
}

// 🔥 bikin nama unik
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$name = "media_" . time() . "." . $ext;

// path simpan
$path = $folder . $name;

// pindahkan file
if (move_uploaded_file($file['tmp_name'], $path)) {

    // 🔥 URL PUBLIC (INI YANG PENTING)
    $url = "http://10.128.170.106:8000/api/upload/media/" . $name;

    echo json_encode([
        "status" => "success",
        "url" => $url
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Gagal simpan file"
    ]);
}
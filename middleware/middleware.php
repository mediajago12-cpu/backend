<?php

$SECRET = "RAHASIA_SUPER_123";

// decode base64url
function base64url_decode($data) {
  return base64_decode(strtr($data, '-_', '+/'));
}

// verify JWT
function verifyJWT($token, $secret) {
  $parts = explode('.', $token);
  if (count($parts) !== 3) return false;

  list($header, $payload, $signature) = $parts;

  $valid_signature = rtrim(strtr(
    base64_encode(
      hash_hmac('sha256', "$header.$payload", $secret, true)
    ),
    '+/', '-_'
  ), '=');

  if ($signature !== $valid_signature) return false;

  $data = json_decode(base64url_decode($payload), true);

  if (!$data) return false;

  // cek expired
  if (isset($data['exp']) && time() > $data['exp']) {
    return false;
  }

  return $data;
}
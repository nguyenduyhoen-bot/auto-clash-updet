<?php
// ========================================================
// AutoClash - Pure PHP RSA-PSS SHA256 Signer & Key Packager
// Tương thích 100% với Python cryptography PSS SHA256
// ========================================================

function mgf1_sha256($seed, $maskLen) {
    $t = '';
    $counter = 0;
    while (strlen($t) < $maskLen) {
        $c = pack('N', $counter);
        $t .= hash('sha256', $seed . $c, true);
        $counter++;
    }
    return substr($t, 0, $maskLen);
}

function emsa_pss_encode_sha256($mHash, $emBits = 2047, $sLen = 32) {
    $emLen = (int)ceil($emBits / 8);
    $hLen = 32;
    if ($emLen < $hLen + $sLen + 2) {
        throw new Exception("Encoding error: emLen too small");
    }
    $salt = random_bytes($sLen);
    $mPrime = "\x00\x00\x00\x00\x00\x00\x00\x00" . $mHash . $salt;
    $h = hash('sha256', $mPrime, true);
    $psLen = $emLen - $sLen - $hLen - 2;
    $db = str_repeat("\x00", $psLen) . "\x01" . $salt;
    $dbMask = mgf1_sha256($h, $emLen - $hLen - 1);
    $maskedDB = $db ^ $dbMask;
    
    $firstByteMask = 0xFF >> (8 * $emLen - $emBits);
    $maskedDB[0] = chr(ord($maskedDB[0]) & $firstByteMask);
    
    return $maskedDB . $h . "\xbc";
}

function rsa_pss_sign_message($messageBytes, $privateKeyPem, $keyBits = 2048) {
    $mHash = hash('sha256', $messageBytes, true);
    $em = emsa_pss_encode_sha256($mHash, $keyBits - 1, 32);
    if (strlen($em) < (int)($keyBits / 8)) {
        $em = str_pad($em, (int)($keyBits / 8), "\x00", STR_PAD_LEFT);
    }
    
    $sig = '';
    $res = openssl_private_encrypt($em, $sig, $privateKeyPem, OPENSSL_NO_PADDING);
    if (!$res) {
        throw new Exception("Lỗi ký RSA-PSS: " . openssl_error_string());
    }
    return $sig;
}

/**
 * Tạo License Key String tương thích 100% với hàm verify_license của AutoClash
 * @param string $hwid Mã máy tính (Hardware ID)
 * @param string $expiresAt Ngày hết hạn dạng 'YYYY-MM-DD HH:MM:SS'
 * @param string $privateKeyPath Đường dẫn tới file private_key.pem
 * @return string Chuỗi Base64 đóng gói sẵn để dán vào ô License Key của tool hoặc lưu thành license.key
 */
function generate_autoclash_license($hwid, $expiresAt, $privateKeyPath = null) {
    if (!$privateKeyPath) {
        $privateKeyPath = __DIR__ . '/../private_key.pem';
    }
    if (!file_exists($privateKeyPath)) {
        throw new Exception("Không tìm thấy file private_key.pem trên máy chủ!");
    }
    $privateKeyPem = file_get_contents($privateKeyPath);
    
    $payloadData = [
        'hwid' => trim($hwid),
        'expiry' => trim($expiresAt)
    ];
    $payloadBytes = json_encode($payloadData);
    $signatureBytes = rsa_pss_sign_message($payloadBytes, $privateKeyPem);
    
    $licenseEnvelope = [
        'payload' => base64_encode($payloadBytes),
        'signature' => base64_encode($signatureBytes)
    ];
    
    return base64_encode(json_encode($licenseEnvelope));
}

<?php
// === Minimal JWT implementation ===
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function getKey() {
    // permanent server-side secret
    return "yh3f97hf847y5h3";
}

function issueJwt($user = "anon", $ttl = 900) {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload = [
        'iat' => time(),
        'exp' => time() + $ttl,
        'user' => $user,
    ];

    $headerEnc  = base64url_encode(json_encode($header));
    $payloadEnc = base64url_encode(json_encode($payload));
    $signature  = hash_hmac('sha256', "$headerEnc.$payloadEnc", getKey(), true);
    $sigEnc     = base64url_encode($signature);

    return "$headerEnc.$payloadEnc.$sigEnc";
}

function isKeyValid($dieOnFail = true) {
    global $argv;
    $jwt = $_GET['key'] ?? ($argv[1] ?? null);

    if (!$jwt) {
        if ($dieOnFail) {
            http_response_code(401);
            die("Missing token");
        }
        return false;
    }

    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        if ($dieOnFail) { http_response_code(400); die("Malformed token"); }
        return false;
    }

    list($headerEnc, $payloadEnc, $sigEnc) = $parts;
    $expectedSig = base64url_encode(hash_hmac('sha256', "$headerEnc.$payloadEnc", getKey(), true));

    if (!hash_equals($expectedSig, $sigEnc)) {
        if ($dieOnFail) { http_response_code(403); die("Invalid signature"); }
        return false;
    }

    $payload = json_decode(base64url_decode($payloadEnc), true);
    if (!$payload || time() >= $payload['exp']) {
        if ($dieOnFail) { http_response_code(403); die("Expired or invalid token"); }
        return false;
    }

    return true;
}

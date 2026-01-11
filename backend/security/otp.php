<?php
declare(strict_types=1);
require_once __DIR__. '/../bootstrap.php';

function generateOtp(int $length = 6): string {
    return str_pad((string)random_int(0, 999999), $length, '0', STR_PAD_LEFT);
}

function otpExpiry(): string {
    return date('Y-m-d H:i:s', time() + 300); // OTP expires in 5 minutes
}
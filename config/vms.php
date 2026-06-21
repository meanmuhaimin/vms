<?php

return [
    'otp_ttl_seconds' => (int) env('OTP_TTL_SECONDS', 180),
    'otp_resend_limit' => (int) env('OTP_RESEND_LIMIT', 3),
    'default_printer_id' => env('VMS_DEFAULT_PRINTER_ID', 'LOBBY_LANE_01_PRINTER'),
];

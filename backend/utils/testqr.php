<?php
require_once __DIR__ . '/phpqrcode.php';
QRcode::png('test-qr', __DIR__ . '/../uploads/qrcodes/test-qr.png');
echo 'Done';

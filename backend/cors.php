<?php
// Dedicated CORS preflight handler
header('Access-Control-Allow-Origin: https://accesio.vercel.app');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');
http_response_code(204);
exit();

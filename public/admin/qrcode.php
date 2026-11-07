<?php
declare(strict_types=1);

/* US5 — QR Code e cartaz para a recepção. */

require dirname(__DIR__, 2) . '/src/bootstrap.php';

exigir_login();

render('admin/qrcode', [
    'titulo' => 'QR Code',
    'menu' => 'qrcode',
    'linkQr' => url_absoluta('?origem=qrcode'),
    'linkTotem' => url_absoluta('?modo=totem'),
    'urlDefinida' => env('APP_URL') !== null,
]);

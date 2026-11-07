<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

/** QR Code e cartaz para a recepção. */
final class QrCodeController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();
        $this->view('admin/qrcode', [
            'titulo' => 'QR Code',
            'menu' => 'qrcode',
            'linkQr' => url_absoluta('?origem=qrcode'),
            'linkTotem' => url_absoluta('?modo=totem'),
            'urlDefinida' => env('APP_URL') !== null,
        ]);
    }
}

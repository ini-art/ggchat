<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class Home extends Controller
{
    public function index()
    {
        helper(['jwe','cookie']);
        // read access token from session (we store it there)
        $token = session()->get('access_token');
        if (!$token) {
            return redirect()->to('/auth/login')->with('error','Silakan login terlebih dahulu.');
        }

        $rsaPrivateKeyPem = file_get_contents(WRITEPATH . 'keys/private.pem');
        $decrypted = jwe_decrypt($token, $rsaPrivateKeyPem);
        if (!$decrypted) {
            session()->remove('access_token');
            delete_cookie('refresh_token');
            return redirect()->to('/auth/login')->with('error','Sesi tidak valid, silakan login ulang.');
        }

        $payload = json_decode($decrypted['payload'], true);
        // log_message('debug', 'Payload JWE: ' . print_r($payload, true));
        if (!$payload || ($payload['exp'] ?? 0) < time()) {
            session()->remove('access_token');
            delete_cookie('refresh_token');
            return redirect()->to('/auth/login')->with('error','Token kadaluarsa, silakan login ulang.');
        }

    // Ambil daftar user lain
        $userModel = new UserModel();
        $users = $userModel->where('id !=', $payload['uid'])->findAll();

        return view('home', [
            'title' => 'Chat',
            'user'  => [
                'id'    => $payload['uid'],
                'name'  => $payload['name'],
                'email' => $payload['email'] ?? '',
            ],
            'users' => $users
        ]);
    }
}
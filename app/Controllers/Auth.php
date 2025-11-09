<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserSessionModel;
use CodeIgniter\Controller;

class Auth extends Controller
{
    protected $userModel;
    protected $sessionModel;

    public function __construct()
    {
        helper(['form','url','cookie','jwe']);
        $this->userModel = new UserModel();
        $this->sessionModel = new UserSessionModel();
    }

    public function register() {
        echo view('auth/register');
    }

    public function attemptRegister()
    {
        helper(['form', 'url', 'cookie']);
        $rules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data tidak valid');
        }

        $userModel = new \App\Models\UserModel();
        $hash = password_hash($this->request->getPost('password'), PASSWORD_ARGON2ID);

        $userModel->insert([
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => $hash
        ]);

        return redirect()->to('auth/login')->with('success', 'Registrasi berhasil, silakan login.');
    }

    // show login form
    public function login()
    {
        return view('auth/login');
    }

    // handle POST login
    public function attemptLogin()
    {
        // validation rules
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[8]'
        ];

        if (!$this->validate($rules)) {
            // include validation messages in flashdata
            return redirect()->back()->withInput()->with('error', 'Email atau password tidak valid.');
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->where('email', $email)->first();
        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah.');
        }

        // Build JWE access token (use server public key)
        $rsaPublicKeyPem = file_get_contents(WRITEPATH . 'keys/public.pem');
        $header = ['alg'=>'RSA-OAEP','enc'=>'XC20P','typ'=>'JWE'];
        $payload = json_encode([
            'uid'   => $user['id'],
            'email' => $user['email'],
            'name'  => $user['name'],
            'iat'   => time(),
            'exp'   => time() + 3600 // 1 jam
        ]);

        try {
            $jweToken = jwe_encrypt($header, $payload, $rsaPublicKeyPem);
        } catch (\Throwable $e) {
            log_message('error', 'JWE encrypt failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan internal (encryption).');
        }

        // generate refresh token (plain -> hashed stored)
        $refreshTokenPlain = generate_refresh_token();
        $refreshHash = hash_refresh_token($refreshTokenPlain);

        // create server session record
        $sessionId = $this->sessionModel->insert([
            'user_id' => $user['id'],
            'session_uuid' => bin2hex(random_bytes(16)),
            'refresh_token_hash' => $refreshHash,
            'user_agent' => substr($this->request->getUserAgent()->getAgentString() ?? '', 0, 512),
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', time() + (60*60*24*30)), // 30 hari
            'revoked' => 0
        ], true); // return id

        // Store access token in server session (avoid cookie-size limit for JWE)
        session()->set('access_token', $jweToken);
        // store refresh token as HttpOnly cookie (we only keep hashed in DB)
        set_cookie([
            'name' => 'refresh_token',
            'value' => $refreshTokenPlain,
            'expire' => 60*60*24*30,
            'httponly' => true,
            'secure' => $this->request->isSecure(),
            'samesite' => 'Strict',
            'path' => '/auth/refresh'
        ]);

        session()->setFlashdata('success', 'Login berhasil!');
        return redirect()->to('/home');
    }

    // logout: revoke session if possible
    public function logout()
    {
        helper('cookie');
        $refreshToken = get_cookie('refresh_token');
        if ($refreshToken) {
            $h = hash_refresh_token($refreshToken);
            $sess = $this->sessionModel->where('refresh_token_hash', $h)->first();
            if ($sess) {
                $this->sessionModel->update($sess['id'], ['revoked' => 1]);
            }
        }

        // remove cookies and session
        delete_cookie('refresh_token');
        session()->remove('access_token');
        session()->setFlashdata('success', 'Berhasil logout.');
        return redirect()->to('/auth/login');
    }

    // refresh endpoint (rotate refresh token + issue new access token)
    public function refresh()
    {
        helper(['cookie','jwe']);
        $refreshToken = get_cookie('refresh_token');
        if (!$refreshToken) {
            return redirect()->to('/auth/login')->with('error','Refresh token tidak ditemukan.');
        }

        $h = hash_refresh_token($refreshToken);
        $sess = $this->sessionModel->where('refresh_token_hash', $h)->first();
        if (!$sess || $sess['revoked'] || strtotime($sess['expires_at']) < time()) {
            // invalidate
            delete_cookie('refresh_token');
            session()->remove('access_token');
            return redirect()->to('/auth/login')->with('error', 'Refresh token tidak valid atau sudah kedaluwarsa.');
        }

        // find user
        $user = $this->userModel->find($sess['user_id']);
        if (!$user) {
            return redirect()->to('/auth/login')->with('error','User tidak ditemukan.');
        }

        // issue new access token
        $rsaPublicKeyPem = file_get_contents(WRITEPATH . 'keys/public.pem');
        $payload = json_encode([
            'uid' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + 3600
        ]);
        $header = ['alg'=>'RSA-OAEP','enc'=>'XC20P','typ'=>'JWE'];
        try {
            $jweToken = jwe_encrypt($header, $payload, $rsaPublicKeyPem);
        } catch (\Throwable $e) {
            log_message('error','JWE encrypt failed in refresh: '.$e->getMessage());
            return redirect()->to('/auth/login')->with('error','Gagal buat token.');
        }

        // rotate refresh token (best practice)
        $newRefreshPlain = generate_refresh_token();
        $newRefreshHash = hash_refresh_token($newRefreshPlain);
        $this->sessionModel->update($sess['id'], ['refresh_token_hash' => $newRefreshHash]);

        // store access in session and set new refresh cookie
        session()->set('access_token', $jweToken);
        set_cookie([
            'name' => 'refresh_token',
            'value' => $newRefreshPlain,
            'expire' => 60*60*24*30,
            'httponly' => true,
            'secure' => $this->request->isSecure(),
            'samesite' => 'Strict',
            'path' => '/auth/refresh'
        ]);

        session()->setFlashdata('success','Token berhasil diperbarui.');
        return redirect()->to('/home');
    }
}

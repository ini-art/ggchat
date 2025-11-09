<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['cookie','jwe']);
        $rsaPrivateKeyPem = file_get_contents(WRITEPATH . 'keys/private.pem');

        // prefer session (we store access_token there); fallback to cookie if you change later
        $token = session()->get('access_token') ?: get_cookie('access_token');

        if (!$token) {
            log_message('debug', 'AuthFilter: token not found.');
            return redirect()->to('/auth/login');
        }

        $decrypted = jwe_decrypt($token, $rsaPrivateKeyPem);
        if (!$decrypted) {
            log_message('error', 'AuthFilter: failed to decrypt JWE token.');
            // remove whatever stored token so subsequent requests start clean
            delete_cookie('access_token');
            session()->remove('access_token');
            return redirect()->to('/auth/login');
        }

        $payload = json_decode($decrypted['payload'], true);
        if (!$payload || ($payload['exp'] ?? 0) < time()) {
            log_message('debug', 'AuthFilter: token expired or payload invalid.');
            delete_cookie('access_token');
            session()->remove('access_token');
            return redirect()->to('/auth/login');
        }

        // optional: check server session existence / revocation by session_uuid if you store it
        // store user info in request for controllers
        $request->user = $payload;
        log_message('debug', 'AuthFilter success for uid='.$payload['uid'] ?? 'unknown');
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}

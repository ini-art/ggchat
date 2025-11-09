<?php namespace App\Models;

use CodeIgniter\Model;

class UserSessionModel extends Model
{
    protected $table = 'user_sessions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'session_uuid', 'refresh_token_hash', 'user_agent', 'ip_address', 'expires_at', 'revoked'
    ];
    protected $useTimestamps = false;
    protected $returnType = 'array';
}

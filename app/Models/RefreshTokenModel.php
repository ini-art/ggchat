<?php namespace App\Models;

use CodeIgniter\Model;

class RefreshTokenModel extends Model {
    protected $table = 'refresh_tokens';
    protected $allowedFields = ['user_id','token_hash','expires_at','last_used_at','revoked','rotation_count'];
    protected $useTimestamps = true;
}

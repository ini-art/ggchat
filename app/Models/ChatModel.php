<?php

namespace App\Models;
use CodeIgniter\Model;

class ChatModel extends Model
{
    protected $table = 'chats';
    protected $allowedFields = ['sender_id', 'receiver_id', 'message', 'created_at'];
    protected $useTimestamps = true;

    public function getConversation($userId, $receiverId)
    {
        return $this->where("(sender_id = $userId AND receiver_id = $receiverId) 
                             OR (sender_id = $receiverId AND receiver_id = $userId)")
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }
}

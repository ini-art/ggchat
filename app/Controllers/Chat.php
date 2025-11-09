<?php

namespace App\Controllers;
use App\Models\ChatModel;
use App\Models\UserModel;

class Chat extends BaseController
{
    public function getMessages($receiver_id)
    {
        helper('jwe_helper');
        $token = session()->get('token');
        $userData = verify_jwe($token);
        if (!$userData) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $chatModel = new ChatModel();
        $messages = $chatModel->getConversation($userData['id'], $receiver_id);
        return $this->response->setJSON($messages);
    }

    public function sendMessage()
    {
        helper('jwe_helper');
        $token = session()->get('token');
        $userData = verify_jwe($token);
        if (!$userData) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $chatModel = new ChatModel();
        $message = [
            'sender_id' => $userData['id'],
            'receiver_id' => $this->request->getPost('receiver_id'),
            'message' => $this->request->getPost('message'),
        ];
        $chatModel->insert($message);
        return $this->response->setJSON(['status' => 'success']);
    }
}

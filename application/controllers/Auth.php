<?php

use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;

class Auth extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('m_user', 'user');
    }
    public function index_post()
    {
        $key = '1234567890';
        $date = new DateTime();
        $username = $this->post('username');
        $password = $this->post('password');
        $encrypt_pass = hash('sha512', $password . $key);

        $datauser = $this->user->doLogin($username, $encrypt_pass);
        if ($datauser) {
            $payload = [
                'id' => $datauser[0]->id,
                'name' => $datauser[0]->name,
                'username' => $datauser[0]->username,
                'iat' => $date->getTimestamp(), // waktu token di generate
                'exp' => $date->getTimestamp() + (60 * 3) // token berlaku 3 menit
            ];
            $token = JWT::encode($payload, $key, 'HS256');
            $this->response([
                'status' => true,
                'message' => 'Login berhasil.',
                'result' => [
                    'id' => $datauser[0]->id,
                    'name' => $datauser[0]->name,
                    'username' => $datauser[0]->username
                ],
                'token' => $token
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Periksa kembali username dan password!'
            ], self::HTTP_FORBIDDEN);
        }
    }
}

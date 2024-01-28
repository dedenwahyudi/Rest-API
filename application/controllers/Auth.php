<?php

use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use PhpParser\Node\Stmt\TryCatch;

class Auth extends RestController
{
    private $key;

    function __construct()
    {
        parent::__construct();
        $this->load->model('m_user', 'user');
        $this->key = '1234567890';
    }

    public function index_post()
    {
        $date = new DateTime();
        $username = $this->post('username');
        $password = $this->post('password');
        $encrypt_pass = hash('sha512', $password . $this->key);

        $datauser = $this->user->doLogin($username, $encrypt_pass);
        if ($datauser) {
            $payload = [
                'id' => $datauser[0]->id,
                'name' => $datauser[0]->name,
                'username' => $datauser[0]->username,
                'iat' => $date->getTimestamp(), // waktu token di generate
                'exp' => $date->getTimestamp() + (60 * 10) // token berlaku 3 menit
            ];
            $token = JWT::encode($payload, $this->key, 'HS256');
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

    protected function cektoken()
    {
        $jwt = $this->input->get_request_header('Authorization');
        try {
            JWT::decode($jwt, new Key($this->key, 'HS256'));
        } catch (Exception $e) {
            $this->response([
                'status' => false,
                'message' => 'Invalid Tokan!'
            ], self::HTTP_UNAUTHORIZED);
        }
    }
}

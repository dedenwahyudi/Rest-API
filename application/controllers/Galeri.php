<?php

use chriskacerguis\RestServer\RestController;

class Galeri extends RestController
{
    public function index_post()
    {
        $this->load->model('M_galeri', 'galeri');
        $path = 'uploads/gallery/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $count_image = count($_FILES['galeri']['name']);
        $data = [];
        for ($i = 0; $i < $count_image; $i++) {
            if (!empty($_FILES['galeri']['name'][$i])) {
                $config['upload_path'] = './' . $path;
                $config['allowed_types'] = 'jpeg|jpg|png|gif';
                $config['file_name'] = time();
                $config['max_size'] = 1024;
                $this->upload->initialize($config);

                $_FILES['image']['name'] = $_FILES['galeri']['name'][$i];
                $_FILES['image']['type'] = $_FILES['galeri']['type'][$i];
                $_FILES['image']['tmp_name'] = $_FILES['galeri']['tmp_name'][$i];
                $_FILES['image']['error'] = $_FILES['galeri']['error'][$i];
                $_FILES['image']['size'] = $_FILES['galeri']['size'][$i];

                if ($this->upload->do_upload('image')) {
                    $uploadData = $this->upload->data();
                    $data[] = array(
                        'filename' => $uploadData['file_name'],
                        'type' => $uploadData['file_type'],
                        'size' => $uploadData['file_size'],
                        'path' => './' . $path . $uploadData['file_name']
                    );
                }
            }
        }
        $saved = $this->galeri->insertData($data);
        if ($saved > 0) {
            $this->response([
                'status' => true,
                'message' => 'Berhasil menambahkan data galeri.'
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Tidak ada data galeri yang ditambahkan.'
            ], self::HTTP_BAD_REQUEST);
        }
    }
}

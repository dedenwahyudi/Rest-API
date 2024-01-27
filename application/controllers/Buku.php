<?php

use chriskacerguis\RestServer\RestController;
// Import Use
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Buku extends RestController
{
    function __construct()
    {
        parent::__construct();
        // Load model dan berikan alias 'buku'
        $this->load->model('m_buku', 'buku');
    }

    // READ
    public function index_get()
    {
        // Buat variable yang berisi id_buku yang di post dari user
        $id = $this->get('id_buku');
        // Jalankan fungsi getData yang di ambil dari model
        $data_buku = $this->buku->getData($id);
        // Cek apakah ada data di dalam variable $data_buku
        if ($data_buku) {
            // Jika ada berikan pesan atau respon berikut ini
            $this->response([
                'status' => true,
                'message' => 'Berhasil mendapatkan data.',
                'result' => $data_buku
            ], self::HTTP_OK);
        } else {
            // Jika tidak ada berikan pesan atau respon berikut ini
            $this->response([
                'status' => false,
                'message' => 'Data tidak ditemukan.'
            ], self::HTTP_NOT_FOUND);
        }
    }

    // CREATE
    public function index_post()
    {
        // Baca file
        $file = $_FILES['file'];
        // Cek apakah file tersebut ada atau tidak menggunakan isset
        $filename = $file['name'];
        // Cek apakan variable $filename ada isinya
        if ($filename != '') {
            // Jika ada lakukan proses import excel. Panggil function import_post()
            $this->import_post();
        } else {
            // Jika tidak ada lakukan proses input biasa

            // Cek apakah validasi bernilai false atau true
            if ($this->_validationCheck() === false) {
                // Jika false tambahkan pesan atau response
                $this->response([
                    'status' => false,
                    'message' => strip_tags(validation_errors())
                ], self::HTTP_BAD_REQUEST);
            } else {
                // Jika true, jalankan fungsi berikut ini

                // Ambil inputan dengan name 'cover'
                $file = $_FILES['cover'];
                // Buatkan path atau alamat penyimpanan file yang di upload
                $path = 'uploads/buku/';
                // Cek apakah folder penyimpanan gambar yang di upload sudah ada atau belum, jika belum buatkan dengan fungsi mk_dir()
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                // Inisialisasi path file
                $path_file = '';
                // Cek apakah nama file dari file yang di input itu tidak kosong, jika ya jalankan fungsi berikut ini
                if (!empty($file['name'])) {
                    // Path atau alamat penyimpanan
                    $config['upload_path'] = './' . $path;
                    // Ekstensi file yang didukung
                    $config['allowed_types'] = 'jpeg|jpg|png|gif';
                    // Nama file
                    $config['file_name'] = time();
                    // Maksimal ukuran file
                    $config['max_size'] = 1024;
                    // Gunakan library upload, dan inisialisasikan variable $config kedalam library upload tersebut
                    $this->upload->initialize($config);
                    if ($this->upload->do_upload('cover')) {
                        // Untuk mendapatkan file yang berhasil di upload.
                        $uploadData = $this->upload->data();
                        $path_file = './' . $path . $uploadData['file_name'];
                    }
                }
                // Siapkan data yang akan di simpan ke database dari inputan yang di post user
                $data = [
                    'judul' => $this->post('judul'),
                    'penulis' => $this->post('penulis'),
                    'tahun' => $this->post('tahun'),
                    'penerbit' => $this->post('penerbit'),
                    'cover' => $path_file,
                    'stock' => $this->post('stock'),
                    'harga_beli' => $this->post('harga_beli'),
                    'harga_jual' => $this->post('harga_jual'),
                    'kategori' => $this->post('id_kategori')
                ];
                // Jalankan fungsi simpan dan simpan kedalam variable untuk mengecek nilai affected rows
                $saved = $this->buku->insertData($data);
                // Cek apakan affected rows bernilai > 0
                if ($saved > 0) {
                    $this->response([
                        'status' => true,
                        'message' => 'Berhasil menambahkan data.'
                    ], self::HTTP_CREATED);
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'Gagal menambahkan data.'
                    ], self::HTTP_BAD_REQUEST);
                }
            }
        }
    }

    // UPDATE
    public function index_put()
    {
        // Pastikan data yang di ambil adalah data yang dikirim dari fungsi PUT yang dikirim dari user
        // $this->form_validation->set_data($this->put());
        // Cek apakah validasi bernilai false atau true
        if ($this->_validationCheck() === false) {
            // Jika false, jalankan fungsi berikut ini
            $this->response([
                'status' => false,
                'message' => strip_tags(validation_errors())
            ], self::HTTP_BAD_REQUEST);
        } else {
            // Jika true, jalankan fungsi berikut ini
            $id = $this->input->post('id_buku');
            // Ambil data lama atau nama file yang lama sebelum di update file ke Model
            $data_buku = $this->buku->getData($id);
            // Ambil inputan dengan name 'cover'
            $file = $_FILES['cover'];
            // Buatkan path atau alamat penyimpanan file yang di upload
            $path = 'uploads/buku/';
            // Cek apakah folder penyimpanan gambar yang di upload sudah ada atau belum, jika belum buatkan dengan fungsi mk_dir()
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            // Inisialisasi path file
            $path_file = '';
            // Cek apakah nama file dari file yang di input itu tidak kosong, jika ya jalankan fungsi berikut ini
            if (!empty($file['name'])) {
                // Path atau alamat penyimpanan
                $config['upload_path'] = './' . $path;
                // Ekstensi file yang didukung
                $config['allowed_types'] = 'jpeg|jpg|png|gif';
                // Nama file
                $config['file_name'] = time();
                // Maksimal ukuran file
                $config['max_size'] = 1024;
                // Gunakan library upload, dan inisialisasikan variable $config kedalam library upload tersebut
                $this->upload->initialize($config);
                if ($this->upload->do_upload('cover')) {
                    // Fungsi untuk menghapus file lama dan mengganti dengan file baru
                    @unlink($data_buku[0]['cover']);
                    // Untuk mendapatkan file yang berhasil di upload.
                    $uploadData = $this->upload->data();
                    $path_file = './' . $path . $uploadData['file_name'];
                    $data['cover'] = $path_file;
                }
            }
            // Siapkan $data[] yang akan di simpan ke database dari inputan yang di post user
            // Seharusnya menggunaakan PUT $this->input->put(), tetapi karena didalam postman terdapat kekurangan pada saat menggunakan method PUT tidak bisa menyisipkan gambar maka, ini cara yang di lakukan untuk mengakali agar function index_put tetap dapat berjalan. hal ini merupakan kekurangan dari postman.
            $data['judul'] =  $this->input->post('judul');
            $data['penulis'] = $this->input->post('penulis');
            $data['tahun'] = $this->input->post('tahun');
            $data['penerbit'] = $this->input->post('penerbit');
            $data['stock'] = $this->input->post('stock');
            $data['harga_beli'] = $this->input->post('harga_beli');
            $data['harga_jual'] = $this->input->post('harga_jual');
            $data['kategori'] = $this->input->post('id_kategori');

            // Jalankan fungsi updated dan simpan kedalam variable untuk mengecek nilai affected rows
            $updated = $this->buku->updateData($data, $id);
            // Cek apakan affected rows bernilai > 0
            if ($updated > 0) {
                $this->response([
                    'status' => true,
                    'message' => 'Berhasil memperbaharui data.'
                ], self::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Gagal memperbaharui data.'
                ], self::HTTP_BAD_REQUEST);
            }
        }
    }

    // VALIDASI INPUT & UPDATE DATA
    private function _validationCheck()
    {
        // Start Form Validation
        $this->form_validation->set_rules('judul', 'Judul buku', 'required', array('required' => '{field} wajib diisi.'));
        $this->form_validation->set_rules('penulis', 'Penulis buku', 'required', array('required' => '{field} wajib diisi.'));
        $this->form_validation->set_rules(
            'tahun',
            'Tahun terbit',
            'required|numeric',
            array(
                'required' => '{field} wajib diisi.',
                'numeric' => '{field} harus berisi angka.'
            )
        );
        $this->form_validation->set_rules('penerbit', 'Penerbit buku', 'required', array('required' => '{field} wajib diisi.'));
        $this->form_validation->set_rules(
            'stock',
            'Stock buku',
            'required|numeric',
            array(
                'required' => '{field} wajib diisi.',
                'numeric' => '{field} harus berisi angka.'
            )
        );
        $this->form_validation->set_rules(
            'harga_beli',
            'Harga beli buku',
            'required|numeric',
            array(
                'required' => '{field} wajib diisi.',
                'numeric' => '{field} harus berisi angka.'
            )
        );
        $this->form_validation->set_rules(
            'harga_jual',
            'Harga jual buku',
            'required|numeric',
            array(
                'required' => '{field} wajib diisi.',
                'numeric' => '{field} harus berisi angka.'
            )
        );
        $this->form_validation->set_rules(
            'id_kategori',
            'Kategori buku',
            'required|numeric',
            array(
                'required' => '{field} wajib diisi.',
                'numeric' => '{field} harus berisi angka.'
            )
        );
        // End Form Validation

        return $this->form_validation->run();
    }

    public function index_delete()
    {
        // Buat variable yang berisi id_buku yang di post dari user
        $id = $this->delete('id_buku');
        // Cek apakah id tersebut bernilai null
        if ($id === null) {
            // Jika ya
            $this->response([
                'status' => false,
                'message' => 'Silahkan masukkan id buku.'
            ], self::HTTP_NOT_FOUND);
        } else {
            // Jika tidak

            // Ambil data lama atau nama file yang lama sebelum di update file ke Model
            $data_buku = $this->buku->getData($id);
            // Fungsi untuk menghapus file lama dan mengganti dengan file baru
            @unlink($data_buku[0]['cover']);

            $deleted = $this->buku->deleteData($id);
            if ($deleted > 0) {
                $this->response([
                    'status' => true,
                    'message' => 'Berhasil menghapus data.'
                ], self::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Gagal menghapus data.'
                ], self::HTTP_BAD_REQUEST);
            }
        }
    }

    public function import_post()
    {
        // Baca file
        $file = $_FILES['file'];
        // Cek apakah file tersebut ada atau tidak menggunakan isset
        $filename = $file['name'];
        if (isset($filename)) {
            // Jika ada jalankan fungsi Import

            // Tampung nama extension yang di upload
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if ($extension == 'xls') {
                $reader = new Xls();
            } else {
                $reader = new Xlsx();
            }

            $path = $file['tmp_name'];
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet()->toArray();
            $data = [];
            foreach ($sheet as $key => $value) {
                if ($key == 0) continue;
                $judul = $value[1];
                $penulis = $value[2];
                $tahun_terbit = $value[3];
                $penerbit = $value[4];
                $stock = $value[5];
                $harga_beli = $value[6];
                $harga_jual = $value[7];
                $kategori = $value[8];

                // Lakukan pengecekan apakah semua kolom yang di kirim wajib di isi atau tidak
                if ($judul != '' && $penulis != '' && $tahun_terbit != '' && $penerbit != '' && $stock != '' && $harga_jual != '' && $harga_beli != '' && $kategori != '') {
                    $data[] = [
                        'judul' => $judul,
                        'penulis' => $penulis,
                        'tahun' => $tahun_terbit,
                        'penerbit' => $penerbit,
                        'stock' => $stock,
                        'harga_beli' => $harga_beli,
                        'harga_jual' => $harga_jual,
                        'kategori' => $kategori
                    ];
                }
            }
            // Lakukan fungsi import data
            $import = $this->buku->importData($data);
            if ($import > 0) {
                $this->response([
                    'status' => true,
                    'message' => 'Berhasil mengimport data.'
                ], self::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Tidak ada tada yang di import.'
                ], self::HTTP_BAD_REQUEST);
            }
        } else {
            // Jika tidak ada berikan pesan atau response
            $this->response([
                'status' => false,
                'message' => 'File tidak ditemukan.'
            ], self::HTTP_NOT_FOUND);
        }
    }

    // SOURCODE UPDATE SEBELUMNYA
    // public function index_put()
    // {
    //     // Pastikan data yang di ambil adalah data yang dikirim dari fungsi PUT yang dikirim dari user
    //     // $this->form_validation->set_data($this->put());
    //     // Cek apakah validasi bernilai false atau true
    //     if ($this->_validationCheck() === false) {
    //         // Jika false, jalankan fungsi berikut ini
    //         $this->response([
    //             'status' => false,
    //             'message' => strip_tags(validation_errors())
    //         ], self::HTTP_BAD_REQUEST);
    //     } else {
    //         // Jika true, jalankan fungsi berikut ini
    //         $id = $this->input->post('id_buku');
    //         // Ambil data lama atau nama file yang lama sebelum di update file ke Model
    //         $data_buku = $this->buku->getData($id);
    //         // Ambil inputan dengan name 'cover'
    //         $file = $_FILES['cover'];
    //         // Buatkan path atau alamat penyimpanan file yang di upload
    //         $path = 'uploads/buku/';
    //         // Cek apakah folder penyimpanan gambar yang di upload sudah ada atau belum, jika belum buatkan dengan fungsi mk_dir()
    //         if (!is_dir($path)) {
    //             mkdir($path, 0777, true);
    //         }
    //         // Inisialisasi path file
    //         $path_file = '';
    //         // Cek apakah nama file dari file yang di input itu tidak kosong, jika ya jalankan fungsi berikut ini
    //         if (!empty($file['name'])) {
    //             // Path atau alamat penyimpanan
    //             $config['upload_path'] = './' . $path;
    //             // Ekstensi file yang didukung
    //             $config['allowed_types'] = 'jpeg|jpg|png|gif';
    //             // Nama file
    //             $config['file_name'] = time();
    //             // Maksimal ukuran file
    //             $config['max_size'] = 1024;
    //             // Gunakan library upload, dan inisialisasikan variable $config kedalam library upload tersebut
    //             $this->upload->initialize($config);
    //             if ($this->upload->do_upload('cover')) {
    //                 // Fungsi untuk menghapus file lama dan mengganti dengan file baru
    //                 @unlink($data_buku[0]['cover']);
    //                 // Untuk mendapatkan file yang berhasil di upload.
    //                 $uploadData = $this->upload->data();
    //                 $path_file = './' . $path . $uploadData['file_name'];
    //             }
    //         }
    //         // Siapkan data yang akan di simpan ke database dari inputan yang di post user
    //         $data = [
    //             // Seharusnya menggunaakan PUT $this->input->put(), tetapi karena didalam postman terdapat kekurangan pada saat menggunakan method PUT tidak bisa menyisipkan gambar maka, ini cara yang di lakukan untuk mengakali agar function index_put tetap dapat berjalan. hal ini merupakan kekurangan dari postman.
    //             'judul' => $this->input->post('judul'),
    //             'penulis' => $this->input->post('penulis'),
    //             'tahun' => $this->input->post('tahun'),
    //             'penerbit' => $this->input->post('penerbit'),
    //             'cover' => $path_file,
    //             'stock' => $this->input->post('stock'),
    //             'harga_beli' => $this->input->post('harga_beli'),
    //             'harga_jual' => $this->input->post('harga_jual'),
    //             'kategori' => $this->input->post('id_kategori'),
    //         ];
    //         // Jalankan fungsi updated dan simpan kedalam variable untuk mengecek nilai affected rows
    //         $updated = $this->buku->updateData($data, $id);
    //         // Cek apakan affected rows bernilai > 0
    //         if ($updated > 0) {
    //             $this->response([
    //                 'status' => true,
    //                 'message' => 'Berhasil memperbaharui data.'
    //             ], self::HTTP_OK);
    //         } else {
    //             $this->response([
    //                 'status' => false,
    //                 'message' => 'Gagal memperbaharui data.'
    //             ], self::HTTP_BAD_REQUEST);
    //         }
    //     }
    // }
}

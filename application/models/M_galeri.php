<?php

class M_galeri extends CI_Model
{
    private $_tbl_galeri = 'galeri';

    public function insertData($data)
    {
        $this->db->insert_batch($this->_tbl_galeri, $data);
        return $this->db->affected_rows();
    }
}

<?php

class M_user extends CI_Model
{
    private $_tbl_user = 'user';

    function doLogin($username, $password)
    {
        $query = $this->db->get_where($this->_tbl_user, ['username' => $username, 'password' => $password]);
        if ($query->num_rows() == 1) {
            return $query->result();
        } else {
            return false;
        }
    }
}

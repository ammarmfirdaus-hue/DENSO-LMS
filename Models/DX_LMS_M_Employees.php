<?php
/**
 * Master Employee Model
 * Mmerepresentasikan tabel LMS_M_Employee di database DX_LMS
 */
class DX_LMS_M_Employees extends DX_LMS_M_Model {
    protected $table = 'LMS_M_Employee';
    protected $primaryKey = 'npk';

    /**
     * Contoh fungsi khusus untuk mendapatkan karyawan yang aktif
     */
    public function getActiveEmployees() {
        return $this->where('status', '=', 'Active');
    }
}

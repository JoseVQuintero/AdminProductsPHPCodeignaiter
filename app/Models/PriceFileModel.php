<?php

namespace App\Models;

use CodeIgniter\Model;

class PriceFileModel extends Model
{
    public $country=null;
    public $table = null;
    public $primaryKey = null;
    public $allowedFields =null;

    public $useTimestamps = null;
    public $updatedField = null;
    public $createdField = null;

    public function getArrayById($id)
    {
        $PriceFile = array_column($this->db->query("select `".$id."` FROM products_".$this->country)->getResultArray(),$id);
        if (!$PriceFile) {
            throw new \Exception('Could not find client for specified ID');
        }

        return $PriceFile;
    }

    
}
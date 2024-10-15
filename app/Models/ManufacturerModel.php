<?php

namespace App\Models;

use CodeIgniter\Model;

class ManufacturerModel extends Model
{
    protected $table = 'manufacturer';
    protected $allowedFields = [
        'name', 'email', 'retainer_fee'
    ];

    protected $useTimestamps = true;
    protected $updatedField = 'updated_at';

    public function findClientById($id)
    {
        $manufacturer = $this->asArray()->where(['id' => $id])->first();

        if (!$manufacturer) {
            throw new \Exception('Could not find manufacturer for specified ID');
        }

        return $manufacturer;
    }
}
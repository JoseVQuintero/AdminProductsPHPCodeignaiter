<?php

namespace App\Controllers\Api;

use Exception;
use App\Models\ManufacturerModel;
use App\Controllers\BaseControllerApi;
use CodeIgniter\HTTP\ResponseInterface;

class Manufacturer extends BaseControllerApi
{
    public function index()
    {
        $model = new ManufacturerModel();
        return $this->getResponse([
            'message' => 'Manufacturers retrieved successfully',
            'manufacturers' => $model->findAll()
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|min_length[6]|max_length[50]|valid_email|is_unique[manufacturer.email]',
            'retainer_fee' => 'required|max_length[255]'
        ];

        $input = $this->getRequestInput($this->request);

        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        $manufacturerEmail = $input['email'];

        $model = new ManufacturerModel();
        $model->save($input);


        $manufacturer = $model->where('email', $manufacturerEmail)->first();

        return $this->getResponse([
            'message' => 'Manufacturer added successfully',
            'manufacturer' => $manufacturer
        ]);
    }

    public function show($id)
    {
        try {

            $model = new ManufacturerModel();
            $manufacturer = $model->findManufacturerById($id);

            return $this->getResponse([
                'message' => 'Manufacturer retrieved successfully',
                'manufacturer' => $manufacturer
            ]);

        } catch (Exception $e) {
            return $this->getResponse([
                'message' => 'Could not find manufacturer for specified ID'
            ], ResponseInterface::HTTP_NOT_FOUND);
        }
    }

    public function update($id)
    {
        try {

            $model = new ManufacturerModel();
            $model->findManufacturerById($id);

            $input = $this->getRequestInput($this->request);


            $model->update($id, $input);
            $manufacturer = $model->findManufacturerById($id);

            return $this->getResponse([
                'message' => 'Manufacturer updated successfully',
                'manufacturer' => $manufacturer
            ]);

        } catch (Exception $exception) {

            return $this->getResponse([
                'message' => $exception->getMessage()
            ], ResponseInterface::HTTP_NOT_FOUND);
        }
    }

    public function destroy($id)
    {
        try {

            $model = new ManufacturerModel();
            $manufacturer = $model->findManufacturerById($id);
            $model->delete($manufacturer);

            return $this->getResponse([
                'message' => 'Manufacturer deleted successfully',
            ]);

        } catch (Exception $exception) {
            return $this->getResponse([
                'message' => $exception->getMessage()
            ], ResponseInterface::HTTP_NOT_FOUND);
        }
    }
}

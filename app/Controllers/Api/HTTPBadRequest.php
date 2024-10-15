<?php

namespace App\Controllers\Api;

use Exception;
use App\Controllers\BaseControllerApi;
use CodeIgniter\HTTP\ResponseInterface;

class HTTPBadRequest extends BaseControllerApi
{
    public function index()
    {
        return $this->getResponse([
            'message' => 'Could not ENTRY'
        ], ResponseInterface::HTTP_NOT_FOUND);
    }
}

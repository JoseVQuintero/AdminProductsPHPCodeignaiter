<?php

namespace App\Controllers\Cron;

use Exception;
use App\Controllers\BaseControllerCron;
use CodeIgniter\HTTP\ResponseInterface;

class HTTPBadRequest extends BaseControllerCron
{
    public function index()
    {
        return $this->getResponse([
            'message' => 'Could not ENTRY'
        ], ResponseInterface::HTTP_NOT_FOUND);
    }
}

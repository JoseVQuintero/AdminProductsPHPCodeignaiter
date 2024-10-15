<?php

namespace App\Controllers;

use App\Controllers\BaseControllerApi;
use CodeIgniter\HTTP\ResponseInterface;

class AuthCron extends BaseControllerApi
{
   
    public function login()
    {
        $rules = [
            'username' => 'required|min_length[6]|max_length[80]',
            'password' => 'required|min_length[8]|max_length[255]'
        ];

        
        $errors = [
            'password' => [
                'validateUser' => 'Invalid login credentials provided'
            ]
        ];
        $input = $this->getRequestInput($this->request);

        if (!$this->validateRequest($input, $rules, $errors)) {
            return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        return $this->getCronJWTForUser($input['username'],$input['password']);
    }

    private function getCronJWTForUser(string $username, string $password, int $responseCode = ResponseInterface::HTTP_OK)
    {
        
        try {
            helper('jwtcron'); 
            if($username==token()['username']&&$password==token()['tokePass']){         
                $response = 
                    [
                        'message' => 'User authenticated successfully',
                        'access_token' => getSignedCronJWTForUser($username)
                    ];
               
            }else{
                $response = 
                    [
                        'message' => 'User authenticated failed',
                        'access_token' => null
                    ];
            }
            return $this->getResponse($response);
        } catch (\Exception $e) {
            return $this->getResponse([
                'error' => $e->getMessage()
            ], $responseCode);
        }
    }
}

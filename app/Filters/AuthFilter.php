<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\API\ResponseTrait;

class AuthFilter implements FilterInterface
{
	use ResponseTrait;

	public function before(RequestInterface $request, $arguments = null)
	{
		$key        = Services::getSecretKey();
		$header 	= $request->getHeader("Authorization");
		$token		= null;

		// extract the token from the header
        if(!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        // check if token is null or empty
        if(is_null($token) || empty($token)) {
			$response = service('response');
			$data = ['status' => false,
					'error' => 'Token is null or empty'];
			$response->setJSON($data);
			$response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
			return $response;
        }

		try
		{
			$decoded = JWT::decode($token, new Key($key, 'HS256'));
			$request->user = $decoded;
			error_log($request->getUri());
			return $request;
		}
		catch (\Exception $e)
		{
			// return Services::response()->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
			$response = service('response');
			$data = ['status' => false,
					'error' => 'Access denied'];
			$response->setJSON($data);
			$response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
			return $response;
		}
	}

	//--------------------------------------------------------------------

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		// Do something here
	}
}

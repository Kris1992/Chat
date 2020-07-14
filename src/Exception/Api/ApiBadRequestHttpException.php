<?php declare(strict_types=1);

namespace App\Exception\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiBadRequestHttpException extends HttpException
{

    /**
     * ApiBadRequestHttpException Constructor
     * 
     * @param string     $message  The internal exception message
     * @param \Throwable $previous The previous exception
     * @param int        $code     The internal exception code
     * @param array      $headers  Array with headers
     */
    public function __construct(string $message = null, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(400, $message, $previous, $headers, $code);
    }
    
}
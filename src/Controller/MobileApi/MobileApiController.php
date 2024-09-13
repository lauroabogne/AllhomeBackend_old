<?php 

namespace App\Controller\MobileApi;
use Cake\Controller\Controller;
use Cake\Http\Response;

class MobileApiController extends Controller
{
    
    public function initialize(): void
    {
        parent::initialize();
        // Initialization specific to MobileApi
        $this->loadComponent('RequestHandler');
    }

    /**
 * Checks if the request data is empty.
 *
 * @param array $requestData The request data.
 * @return bool
 */
public function isRequestDataEmpty(array $requestData): bool
{
    return empty($requestData) || count($requestData) === 0;
}

/**
 * Creates a success response with the provided message.
 *
 * @param string $message The success message.
 * @return \Cake\Http\Response
 */
public function createSuccessResponse(string $message): Response
{
    $responseData = [
        'is_success' => true,
        'message' => $message,
    ];

    return $this->createJsonResponse($responseData);
}

public function createSuccessResponseWithArray(string $message, array $successMessageArray): Response
{
    $responseData = [
        'is_success' => true,
        'message' => $message,
        'data' => $successMessageArray
    ];

    return $this->createJsonResponse($responseData);
}

/**
 * Creates an error response with the provided message.
 *
 * @param string $message The error message.
 * @return \Cake\Http\Response
 */
public function createErrorResponse(string $message): Response
{
    $responseData = [
        'is_success' => false,
        'message' => $message,
    ];

    return $this->createJsonResponse($responseData);
}


public function createErrorResponseForArray(string $message, array $errorArray):Response
{

    return $this->response->withType('application/json')
        ->withStringBody(json_encode([
            'is_success' => false,
            'message' => $message,
            'errors' => $errorArray
        ]));
}
/**
 * Creates a JSON response with the provided data.
 *
 * @param array $data The response data.
 * @return \Cake\Http\Response
 */
private function createJsonResponse(array $data): Response
{
    return new Response([
        'body' => json_encode($data),
        'type' => 'application/json',
        'status' => 200,
    ]);
}
    

}
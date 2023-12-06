<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class ApiErrorResponse implements Responsable
{
    /**
     * Class representing an API error response.
     *
     * This class is responsible for constructing an API error response object
     * with the provided message, optional exception, status code, and headers.
     */
    public function __construct(
        private $message,
        private ?Throwable $exception = null,
        private int $status = Response::HTTP_INTERNAL_SERVER_ERROR,
        private array $headers = ["Content-Type" => "application/json", "Accept" => "application/json"]
    ) {}

    /**
     * Convert the exception to a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        $response = ['message' => $this->message];

        if (!is_null($this->exception) && config('app.debug')) {
            $response['debug'] = [
                'message'   => $this->exception->getMessage(),
                'file'      => $this->exception->getFile(),
                'line'      => $this->exception->getLine(),
                'trace'     => $this->exception->getTraceAsString(),
            ];
        }

        return response()->json([
            $response,
            'status' => $this->status,
            'timestamp' => now()->toDateTimeString(),
        ], $this->status, $this->headers);
    }

}

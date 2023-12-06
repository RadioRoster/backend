<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

class ApiSuccessResponse implements Responsable
{
    /**
     * Class representing a successful API response.
     *
     * This class is responsible for constructing a successful API response
     * with the provided data, status code, and headers.
     */
    public function __construct(
        private $data,
        private int $status = Response::HTTP_OK,
        private array $headers = ["Content-Type" => "application/json", "Accept" => "application/json"]
    ) {}

    /**
     * Convert the response instance to a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response|void
     */
    public function toResponse($request)
    {
        return response()->json(
            [
                'status' => $this->status,
                'data' => $this->data,
                'timestamp' => now()->toDateTimeString(),
            ],
            $this->status,
            $this->headers
        );
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Validates the given parameters based on the provided casts and rules.
     *
     * @param array $params The parameters to be validated.
     * @param array $casts The casting rules for the parameters.
     * @param array $rules The validation rules for the parameters.
     * @return array The validated parameters.
     */
    protected function validateParams($params, $casts, $rules): array
    {
        foreach ($casts as $key => $cast) {
            if (isset($params[$key])) {
                if ($cast === 'boolean') {
                    $params[$key] = filter_var($params[$key], FILTER_VALIDATE_BOOLEAN);
                } else {
                    settype($params[$key], $cast);
                }
            }
        }

        return Validator::make($params, $rules)->validate();
    }
}

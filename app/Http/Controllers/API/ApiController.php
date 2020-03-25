<?php

namespace App\Http\Controllers\API;

use App\Http\MessengerResponse;
use Illuminate\Http\JsonResponse;
use Request;

trait ApiController
{
    /*
    |--------------------------------------------------------------------------
    | API Controller
    |--------------------------------------------------------------------------
    |
    | This trait provides accessories which can help controllers do business
    | logically uniformally.
    |
    */

    /**
     * Takes input and provides a response.
     * This will switch between a standard JSON output and a Messenger respnse
     * depending on request parameters.
     *
     * @param mixed $data Input data.
     *
     * @return Illuminate\Http\JsonResponse|App\Http\MessengerResponse
     */
    public function apiResponse($data = null, $status = 200, $headers = array())
    {
        if ((bool) Request::input('messenger', 0)) {
            return new MessengerResponse($data, $status, $headers);
        }

        return new JsonResponse($data, $status, $headers);
    }
}

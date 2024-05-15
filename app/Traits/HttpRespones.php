<?php

namespace App\Traits;

trait HttpRespones {

    protected function success($message, $data = [], $code = 200) {

        return response([
            'status' => 'Request was successful.',
            'message' => $message,
            'data' => $data
        ], $code);
    }


    protected function error($message, $code = 500, $data = []) {

        return response([
            'status' => 'Request failed.',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}

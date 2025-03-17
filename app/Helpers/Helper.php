<?php
namespace App\Helpers;
error_reporting(-1);
ini_set('display_errors', 'On');

use Illuminate\Http\Response;
use Dotenv\Validator;

class Helper{
    public static function returnApi($messages, $status){
        $response = ['status' => '0', 'register' => 'Validation error'];
        $response['status'] = $status;
        $response['register'] = $messages;
        return response()->json($response);
    }

    public static function errorApis($request, $rules_ai,$messages_ai){
        $response = ['success' => '0', 'register' => 'Validation error'];
        $rules = [$rules_ai];
        $messages = [$messages_ai];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = '';
            $messages_l = json_decode(json_encode($validator->messages()), true);
            foreach ($messages_l as $msg) {
                $message .= $msg[0].', ';
            }
            $response['register'] = $message;
        }

        return Response::json($response);
    }
}

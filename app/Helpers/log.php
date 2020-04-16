<?php

use App\Log;
use App\Support\IP;
use App\Auth\Contracts\Permittable;

if (!function_exists('record')) {
    function record(Permittable $user, $boardUri, $action, array $actionDetails = [], ?IP $ip = null)
    {
        // ['action_name', 'action_details', 'user_id', 'user_ip', 'board_uri'];
        return Log::create([
            'board_uri' => $boardUri,
            'action_name' => $action,
            'action_details' => json_encode($actionDetails),
            'user_id' => $user->user_id,
            'user_ip' => $ip ?? new IP,
        ]);
    }
}

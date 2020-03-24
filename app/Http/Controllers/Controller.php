<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use GuzzleHttp\Client;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function pushToExpo($to, $body, $title) {
        $data = [
            'to'    => $to,
            'sound' => 'default',
            'body'  => $body,
            'title' => $title,
        ];
        $client = new Client();
        $client->request('POST', 'https://exp.host/--/api/v2/push/send', [
            'headers' => [
                'host'            => 'exp.host',
                'Accept'          => 'application/json',
                'Content-Type'    => 'application/json',
                'Accept-Encoding' => ['gzip', 'deflate'],
            ],
            'body' => json_encode($data)
        ]);
    }
}

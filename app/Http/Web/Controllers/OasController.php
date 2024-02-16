<?php

namespace App\Http\Web\Controllers;

use Illuminate\Http\JsonResponse;

class OasController
{
    public function list(): JsonResponse
    {
        $urls = [];
        foreach (config('serve-stoplight.urls') as $url) {
            $urls[] = [
                'url' => url($url['url']),
                'name' => $url['name'],
            ];
        }

        return response()->json(['urls' => $urls], 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}

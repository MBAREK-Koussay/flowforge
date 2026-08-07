<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SPA fallback
|--------------------------------------------------------------------------
| FlowForge ships as a React SPA. Its production build is written to
| `public/dist` by `npm run build` in the `frontend/` directory. Every
| non-API GET is answered with the single-page shell so the frontend
| router can do its job. The API lives under `api/*`.
|--------------------------------------------------------------------------
*/

Route::get('/{path?}', function (Request $request, string $path = null) {
    $shell = public_path('dist/index.html');

    if (! file_exists($shell)) {
        return view('welcome');
    }

    return response(file_get_contents($shell), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
})->where('path', '.*');
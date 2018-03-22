<?php

namespace App\Http\Controllers;

use App\Jobs\UpgradePackage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class HookController extends Controller
{
    public function gitlab(Request $request)
    {
        $repository = $request->input('repository.url');

        if (!$repository) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Artisan::queue(
            'satis:upgrade',
            [
                'repository-url' => $repository,
            ]
        );

        return 'ok';
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\UpgradePackage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class HookController extends Controller
{

    public function hook(Request $request, $type = 'gitlab')
    {
        $url = '';

        switch ($type) {
            case 'gitlab':
                $url = $request->input('repository.url');
                break;
            case 'gitea':
                $url = $request->input('repository.ssh_url');
                break;
            default:
                abort(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->checkAndSendRepositoryUrl($url);

        return 'ok.';
    }

    protected function checkAndSendRepositoryUrl($url)
    {
        if (!$url) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        Artisan::queue(
            'satis:upgrade',
            [
                'repository-url' => $url,
            ]
        );
    }
}

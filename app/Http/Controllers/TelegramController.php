<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function webhook(Request $request, TelegramService $tg)
    {
        $tg->handleUpdate($request->all());
        return response('OK');
    }
}

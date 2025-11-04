<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BizValidatorService;

class A2AController extends Controller
{
    public function handle(Request $req, BizValidatorService $agent)
    {
        $payload = $req->json()->all();
        $userMsg = $payload['content']['text'] ?? '';

        $response = $agent->validateIdea($userMsg);

        return response()->json([
            'type' => 'response',
            'content' => [ 'text' => $response ]
        ]);
    }
}

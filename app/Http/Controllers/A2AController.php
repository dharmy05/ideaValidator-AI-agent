<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BizValidatorService;

class A2AController extends Controller
{
    public function handle(Request $req, BizValidatorService $agent)
    {
        $payload = $req->json()->all();
        $id = $payload['id'] ?? uniqid();
        $text = $payload['params']['message']['parts'][0]['text'] ?? '';

        $replyText = $agent->validateIdea($text);

        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'message' => [
                    'role' => 'agent',
                    'parts' => [
                        [
                            'kind' => 'text',
                            'text' => $replyText
                        ]
                    ]
                ]
            ]
        ]);
    }
}


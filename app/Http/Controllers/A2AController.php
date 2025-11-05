<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use App\Services\BizValidatorService;

class A2AController extends Controller
{
    public function handle(Request $request, BizValidatorService $agent)
    {
        $body = $request->all();

        //  Validate minimal JSON-RPC fields
        $jsonrpc = $body['jsonrpc'] ?? null;
        $id = $body['id'] ?? null;
        $params = $body['params'] ?? [];
        $message = $params['message'] ?? null;

        if ($jsonrpc !== '2.0' || !$id || !$message) {
            return response()->json([
                "jsonrpc" => "2.0",
                "id" => $id ?? null,
                "error" => [
                    "code" => -32600,
                    "message" => "Invalid A2A JSON-RPC Request"
                ]
            ], 400);
        }

        // Extract user text input
        $text = $message['parts'][0]['text'] ?? '';

        try {

            $replyText = $agent->validateIdea($text);

           //Extract structured data from AI response
            $structured = $this->parseValidation($replyText);

           // Generate task and artifact IDs
            $taskId       = $message["taskId"] ?? Str::uuid()->toString();
            $contextId    = Str::uuid()->toString();
            $messageId    = Str::uuid()->toString();
            $artifactMsg  = Str::uuid()->toString();
            $artifactTool = Str::uuid()->toString();

         //Build A2A-compliant JSON-RPC response
            return response()->json([
                "jsonrpc" => "2.0",
                "id" => $id,
                "result" => [
                    "id" => $taskId,
                    "contextId" => $contextId,
                    "status" => [
                        "state" => "completed",
                        "timestamp" => now()->toISOString(),
                        "message" => [
                            "messageId" => $messageId,
                            "role" => "agent",
                            "parts" => [
                                [
                                    "kind" => "text",
                                    "text" => $replyText
                                ]
                            ],
                            "kind" => "message"
                        ]
                    ],
                    // Include artifacts for richer A2A support
                    "artifacts" => [
                        [
                            "artifactId" => $artifactMsg,
                            "name" => "bizValidatorAnalysis",
                            "parts" => [
                                [
                                    "kind" => "data",
                                    "data" => $structured
                                ]
                            ]
                        ],
                        [
                            "artifactId" => $artifactTool,
                            "name" => "ToolResults",
                            "parts" => [
                                [
                                    "kind" => "data",
                                    "data" => [
                                        "type" => "tool-result",
                                        "runId" => Str::uuid()->toString(),
                                        "from" => "AGENT",
                                        "payload" => [
                                            "args" => [
                                                "text" => $text
                                            ],
                                            "toolName" => "BizValidator",
                                            "result" => [
                                                "success" => true,
                                                "parsedFields" => array_keys($structured),
                                                "responseLength" => strlen($replyText)
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "history" => [$message],
                    "kind" => "task"
                ]
            ]);
        } catch (Throwable $e) {
            Log::error('A2A BizValidator failed', ['error' => $e->getMessage()]);
            return response()->json([
                "jsonrpc" => "2.0",
                "id" => $id,
                "error" => [
                    "code" => -32603,
                    "message" => "Internal error",
                    "data" => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Parse key fields from BizValidator's response text.
     */
    private function parseValidation(string $text): array
    {
        $fields = [
            'summary' => $this->extract($text, 'Summary'),
            'market_fit' => $this->extract($text, 'Market Fit'),
            'target_audience' => $this->extract($text, 'Target Audience'),
            'monetization' => $this->extract($text, 'Monetization Options'),
            'competitive_advantage' => $this->extract($text, 'Competitive Advantage'),
            'risks' => $this->extract($text, 'Potential Risks'),
            'score' => $this->extract($text, 'Validation Score'),
            'verdict' => $this->extract($text, 'Verdict'),
        ];

        return array_filter($fields);
    }

    /**
     * Extract text between label and next heading or end.
     */
    private function extract(string $text, string $label): ?string
    {
        if (preg_match("/{$label}:(.*?)(?=\n[A-Z]|$)/s", $text, $match)) {
            return trim($match[1]);
        }
        return null;
    }
}

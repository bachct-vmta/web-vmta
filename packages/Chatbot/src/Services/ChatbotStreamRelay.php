<?php

namespace Packages\Chatbot\Src\Services;

use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatbotStreamRelay
{
    /**
     * Relay upstream SSE stream to browser without buffering.
     * Calls $onComplete when a "done" event is detected.
     */
    public function relay(StreamInterface $upstream, \Closure $onComplete): StreamedResponse
    {
        return response()->stream(function () use ($upstream, $onComplete) {
            // Long LLM streams can exceed PHP's default max_execution_time (30s).
            // Disable the cap inside the streaming callback so the connection
            // stays alive until upstream signals "done" or the client disconnects.
            @set_time_limit(0);
            ignore_user_abort(false);

            $done = false;

            while (! $upstream->eof()) {
                if (connection_aborted()) {
                    break;
                }

                $chunk = $upstream->read(1024);

                if ($chunk === '' || $chunk === false) {
                    break;
                }

                echo $chunk;

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();

                // Detect upstream "done" event to trigger post-stream callback
                if (! $done && str_contains($chunk, '"type":"done"')) {
                    $done = true;
                }
            }

            $upstream->close();

            if ($done) {
                $onComplete();
            }
        }, 200, $this->sseHeaders());
    }

    /**
     * Emit a single SSE error event and close the stream.
     */
    public function error(string $message): StreamedResponse
    {
        return response()->stream(function () use ($message) {
            echo 'data: '.json_encode(['type' => 'error', 'message' => $message])."\n\n";

            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        }, 200, $this->sseHeaders());
    }

    private function sseHeaders(): array
    {
        return [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ];
    }
}

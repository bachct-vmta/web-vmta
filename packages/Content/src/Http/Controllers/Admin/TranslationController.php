<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class TranslationController extends Controller
{
    public function translate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fields'   => ['required', 'array', 'min:1', 'max:20'],
            'fields.*' => ['nullable', 'string', 'max:50000'],
        ]);

        $key  = config('services.google_translate.key', '');
        $url  = config('services.google_translate.url');

        // Filter out empty/null fields — Google rejects empty strings.
        // Keep an index map so we can reassemble results in original positions.
        $allFields   = $data['fields'];
        $indexMap    = [];   // result-array-pos → original-pos
        $toTranslate = [];

        foreach ($allFields as $i => $field) {
            if (isset($field) && $field !== '') {
                $indexMap[]    = $i;
                $toTranslate[] = $field;
            }
        }

        // Nothing to translate — return array of empty strings immediately.
        if (empty($toTranslate)) {
            return response()->json(['translated' => array_fill(0, count($allFields), '')]);
        }

        // Protobuf-style payload: [[ fields[], source, target ], format ]
        $payload = json_encode([[$toTranslate, 'vi', 'en'], 'te']);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type'   => 'application/json+protobuf',
                    'X-Goog-Api-Key' => $key,
                ])
                ->withBody($payload, 'application/json+protobuf')
                ->post($url);
        } catch (ConnectionException) {
            return response()->json(['error' => 'Translation service unavailable'], 502);
        }

        if (! $response->successful()) {
            return response()->json(['error' => 'Translation service unavailable'], 502);
        }

        // Response shape: [["translated1","translated2",...]]
        $results = $response->json()[0] ?? [];

        // Reassemble: place translated strings back at their original positions.
        $translated = array_fill(0, count($allFields), '');
        foreach ($indexMap as $resultPos => $originalPos) {
            $translated[$originalPos] = $results[$resultPos] ?? '';
        }

        return response()->json(['translated' => $translated]);
    }
}

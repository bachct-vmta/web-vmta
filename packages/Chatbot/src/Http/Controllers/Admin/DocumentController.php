<?php

namespace Packages\Chatbot\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Chatbot\Src\Exceptions\UpstreamUnavailableException;
use Packages\Chatbot\Src\Services\TourismApiClient;

class DocumentController extends Controller
{
    public function __construct(private readonly TourismApiClient $client) {}

    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->toString() ?: null;

        $documents = [];
        $groups = [];
        $upstreamError = null;

        try {
            $documents = $this->client->listDocuments($q);
            $groups = $this->client->listDocumentGroups();
        } catch (UpstreamUnavailableException $e) {
            $upstreamError = $e->getMessage();
        }

        return view('chatbot::admin.documents.index', [
            'documents' => $documents,
            'groups' => $groups,
            'q' => $q,
            'upstreamError' => $upstreamError,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:doc,docx,pdf,txt,md,markdown|max:51200',
            'group_code' => 'required|string|max:50',
        ]);

        try {
            $response = $this->client->uploadDocument(
                $request->file('file'),
                strtoupper($request->input('group_code')),
            );
        } catch (UpstreamUnavailableException $e) {
            return back()->withErrors(['upstream' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.chatbot.documents.index')
            ->with('status', __('chatbot::chatbot.documents.uploaded', [
                'filename' => $response['filename'] ?? $request->file('file')->getClientOriginalName(),
            ]));
    }

    public function scan(Request $request): RedirectResponse
    {
        $request->validate(['group_code' => 'required|string|max:50']);

        try {
            $new = $this->client->scanDocuments(strtoupper($request->input('group_code')));
        } catch (UpstreamUnavailableException $e) {
            return back()->withErrors(['upstream' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.chatbot.documents.index')
            ->with('status', __('chatbot::chatbot.documents.scanned', ['count' => count($new)]));
    }

    public function process(string $documentId): RedirectResponse
    {
        try {
            $this->client->processDocument($documentId);
        } catch (UpstreamUnavailableException $e) {
            return back()->withErrors(['upstream' => $e->getMessage()]);
        }

        return back()->with('status', __('chatbot::chatbot.documents.processing'));
    }

    public function processAll(): RedirectResponse
    {
        try {
            $this->client->processAllDocuments();
        } catch (UpstreamUnavailableException $e) {
            return back()->withErrors(['upstream' => $e->getMessage()]);
        }

        return back()->with('status', __('chatbot::chatbot.documents.processing_all'));
    }

    public function destroy(string $documentId): RedirectResponse
    {
        try {
            $this->client->deleteDocument($documentId);
        } catch (UpstreamUnavailableException $e) {
            return back()->withErrors(['upstream' => $e->getMessage()]);
        }

        return back()->with('status', __('chatbot::chatbot.documents.deleted'));
    }

    public function chunks(string $documentId): View
    {
        $chunks = [];
        $upstreamError = null;

        try {
            $chunks = $this->client->documentChunks($documentId);
        } catch (UpstreamUnavailableException $e) {
            $upstreamError = $e->getMessage();
        }

        return view('chatbot::admin.documents.chunks', [
            'documentId' => $documentId,
            'chunks' => $chunks,
            'upstreamError' => $upstreamError,
        ]);
    }
}

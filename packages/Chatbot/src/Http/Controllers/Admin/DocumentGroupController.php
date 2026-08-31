<?php

namespace Packages\Chatbot\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Chatbot\Src\Exceptions\UpstreamUnavailableException;
use Packages\Chatbot\Src\Services\TourismApiClient;

class DocumentGroupController extends Controller
{
    public function __construct(private readonly TourismApiClient $client) {}

    public function index(): View
    {
        try {
            $groups = $this->client->listDocumentGroups();
        } catch (UpstreamUnavailableException $e) {
            $groups = [];
            session()->flash('upstream_error', $e->getMessage());
        }

        return view('chatbot::admin.document-groups.index', compact('groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|regex:/^[A-Za-z0-9_-]+$/',
            'name' => 'required|string|max:200',
        ]);

        try {
            $this->client->createDocumentGroup(strtoupper($data['code']), $data['name']);
        } catch (UpstreamUnavailableException $e) {
            return back()->withErrors(['upstream' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.chatbot.document-groups.index')
            ->with('status', __('chatbot::chatbot.document_groups.created', ['code' => strtoupper($data['code'])]));
    }

    public function destroy(string $code): RedirectResponse
    {
        try {
            $this->client->deleteDocumentGroup($code);
        } catch (UpstreamUnavailableException $e) {
            return back()->withErrors(['upstream' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.chatbot.document-groups.index')
            ->with('status', __('chatbot::chatbot.document_groups.deleted', ['code' => $code]));
    }
}

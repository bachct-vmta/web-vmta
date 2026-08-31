<?php

namespace Packages\Newsletter\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Newsletter\Src\Models\NewsletterSubscriber;
use Packages\Newsletter\Src\Tables\SubscriberTable;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends BaseController
{
    public function index(SubscriberTable $table)
    {
        return view('newsletter::admin.subscribers.index', ['table' => $table]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);
        $subscriber->delete();

        return back()->with('status', __('newsletter::newsletter.admin.deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = (array) $request->input('ids', []);
        $ids = array_filter(array_map('intval', $ids));

        if (! empty($ids)) {
            NewsletterSubscriber::whereIn('id', $ids)->delete();
        }

        return back()->with('status', __('newsletter::newsletter.admin.bulk_deleted', ['count' => count($ids)]));
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'newsletter-subscribers-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            // BOM cho Excel mở UTF-8 đúng.
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Email', 'Locale', 'Status', 'Subscribed At', 'Confirmed At', 'Unsubscribed At', 'IP']);

            $query = NewsletterSubscriber::query();
            if ($status = $request->query('status')) {
                $query->where('status', $status);
            }

            $query->orderBy('id')->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->email,
                        $row->locale,
                        $row->status?->value,
                        optional($row->subscribed_at)->toIso8601String(),
                        optional($row->confirmed_at)->toIso8601String(),
                        optional($row->unsubscribed_at)->toIso8601String(),
                        $row->ip_address,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

<?php

namespace Packages\Catalog\Src\Http\Controllers\Public;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Packages\Catalog\Src\Http\Requests\Public\StoreSpecialtyLeadRequest;
use Packages\Catalog\Src\Mail\SpecialtyLeadReceivedMail;
use Packages\Catalog\Src\Models\SpecialtyLead;
use Packages\Catalog\Src\Repositories\Interfaces\SpecialtyRepositoryInterface;

/**
 * Public Specialty Lead intake.
 *
 * Honeypot + throttle (route-level) + queued admin notification mail.
 * Honeypot hit returns the same "thank you" flash to avoid leaking detection.
 */
class SpecialtyLeadController extends Controller
{
    public function __construct(
        private readonly SpecialtyRepositoryInterface $specialties,
    ) {}

    public function store(StoreSpecialtyLeadRequest $request, string $slug): RedirectResponse
    {
        $locale = app()->getLocale();
        $specialty = $this->specialties->findPublishedBySlug($slug, $locale);

        abort_if($specialty === null, 404);

        if (filled($request->input('hp_field'))) {
            return back()->with('specialty_lead_success', __('catalog::public.specialties.lead.success'));
        }

        $data = $request->validated();

        $lead = SpecialtyLead::create([
            'specialty_id' => $specialty->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'demand' => $data['demand'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'new',
            'locale' => $locale,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        $recipient = config('catalog.lead_recipient');
        if ($recipient) {
            try {
                Mail::to($recipient)->queue(new SpecialtyLeadReceivedMail($lead));
            } catch (\Throwable $e) {
                Log::error('Specialty lead mail dispatch failed', [
                    'lead_id' => $lead->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()
            ->with('specialty_lead_success', __('catalog::public.specialties.lead.success'))
            ->withFragment('lead-form');
    }
}

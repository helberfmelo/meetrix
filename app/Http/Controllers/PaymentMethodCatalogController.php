<?php

namespace App\Http\Controllers;

use App\Models\SchedulingPage;
use App\Services\Payments\CheckoutPaymentMethodService;
use Illuminate\Http\Request;

class PaymentMethodCatalogController extends Controller
{
    public function __construct(
        private readonly CheckoutPaymentMethodService $checkoutPaymentMethodService
    ) {
    }

    /**
     * Public catalog of checkout payment methods by currency/page context.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'currency' => ['nullable', 'string', 'size:3'],
            'scheduling_page_id' => ['nullable', 'integer', 'exists:scheduling_pages,id'],
            'slug' => ['nullable', 'string', 'max:255'],
            'context' => ['nullable', 'in:booking,subscription'],
        ]);

        $page = null;

        if (!empty($validated['scheduling_page_id'])) {
            $page = SchedulingPage::query()
                ->with('user:id,account_mode,currency')
                ->find($validated['scheduling_page_id']);
        } elseif (!empty($validated['slug'])) {
            $page = SchedulingPage::query()
                ->with('user:id,account_mode,currency')
                ->where('slug', (string) $validated['slug'])
                ->where('is_active', true)
                ->first();
        }

        $currency = strtoupper((string) ($validated['currency'] ?? ''));
        if ($currency === '') {
            $currency = strtoupper((string) ($page?->user?->currency ?? 'BRL'));
        }

        $catalog = $this->checkoutPaymentMethodService->buildCatalog(
            $currency,
            $page?->user,
            (string) ($validated['context'] ?? 'booking')
        );

        return response()->json([
            ...$catalog,
            'scheduling_page_id' => $page?->id,
        ]);
    }
}

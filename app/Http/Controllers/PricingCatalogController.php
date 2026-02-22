<?php

namespace App\Http\Controllers;

use App\Services\GeoPricingCatalogService;
use Illuminate\Http\Request;

class PricingCatalogController extends Controller
{
    public function __construct(private readonly GeoPricingCatalogService $catalogService)
    {
    }

    /**
     * Public pricing catalog by resolved region/mode.
     */
    public function index(Request $request)
    {
        $localeHint = $request->query('locale')
            ?: $request->query('browser_locale')
            ?: $request->getPreferredLanguage();

        $pricingContext = $this->catalogService->resolvePricingContext(
            $request->query('country_code'),
            $localeHint,
            $request->ip()
        );

        $catalog = $this->catalogService->getCatalogForRegion($pricingContext['region']);

        return response()->json([
            'region' => $catalog['region'],
            'currency' => $catalog['currency'],
            'plans' => $catalog['plans'],
            'resolution' => [
                'source' => $pricingContext['source'],
                'country_code' => $pricingContext['country_code'],
                'detected_country_code' => $pricingContext['detected_country_code'],
                'locale' => $pricingContext['locale'],
            ],
        ]);
    }
}

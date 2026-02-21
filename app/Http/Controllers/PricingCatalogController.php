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
        $region = $this->catalogService->resolveRegion(
            $request->query('country_code'),
            $request->query('locale')
        );

        $catalog = $this->catalogService->getCatalogForRegion($region);

        return response()->json([
            'region' => $catalog['region'],
            'currency' => $catalog['currency'],
            'plans' => $catalog['plans'],
        ]);
    }
}

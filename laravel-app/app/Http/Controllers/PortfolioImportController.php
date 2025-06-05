<?php

namespace App\Http\Controllers;

use App\Http\Requests\PortfolioImportRequest;
use App\Services\PortfolioImportService;
use Illuminate\Http\JsonResponse;

class PortfolioImportController extends Controller
{
    public function __construct(
        private readonly PortfolioImportService $portfolioImportService
    ) {}

    /**
     * Import portfolio data from a given URL
     */
    public function import(PortfolioImportRequest $request, string $username): JsonResponse
    {
        try {
            $url = $request->input('url');

            $result = $this->portfolioImportService->importPortfolio($username, $url);

            return response()->json([
                'success' => true,
                'message' => 'Portfolio imported successfully',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import portfolio',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

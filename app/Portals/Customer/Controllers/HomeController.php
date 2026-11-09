<?php

namespace App\Portals\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CatalogAndInventory\UseCases\GetHomeCatalogUseCase;
use App\Modules\CatalogAndInventory\UseCases\SearchCatalogUseCase;
use App\Portals\Customer\Requests\SearchCatalogRequest;
use App\Portals\Customer\Resources\CatalogItemResource;
use App\Portals\Customer\Resources\CategoryResource;
use App\Portals\Customer\Resources\TenantSummaryResource;
use App\Portals\Customer\Resources\UserProfileResource;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Display the public landing page with dynamic catalog, tenants, and categories.
     */
    public function index(
        SearchCatalogRequest $request,
        GetHomeCatalogUseCase $getHomeCatalogUseCase,
        SearchCatalogUseCase $searchCatalogUseCase
    ): View {
        $categoriesData = $getHomeCatalogUseCase->getCategories();
        $tenantsData = $getHomeCatalogUseCase->getTenants();

        $searchParams = [
            'keyword' => $request->input('keyword'),
            'category_id' => $request->input('category_id'),
            'tenant_id' => $request->input('tenant_id'),
            'location' => $request->input('location'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'type' => $request->input('type', 'camera'),
        ];

        $hasSearch = $request->filled('keyword') ||
            $request->filled('category_id') ||
            $request->filled('tenant_id') ||
            $request->filled('location') ||
            $request->filled('start_date') ||
            $request->filled('end_date') ||
            ($request->filled('type') && $request->input('type') !== 'camera');

        if ($hasSearch) {
            $productsData = $searchCatalogUseCase->execute($searchParams);
        } else {
            $productsData = $getHomeCatalogUseCase->getFeaturedProducts();
        }

        $categories = CategoryResource::collection($categoriesData)->resolve();
        $tenants = TenantSummaryResource::collection($tenantsData)->resolve();
        $featuredProducts = CatalogItemResource::collection($productsData)->resolve();

        $authUser = Auth::user();
        $userProfile = $authUser ? (new UserProfileResource($authUser))->resolve() : null;

        return view('home.index', [
            'categories' => $categories,
            'tenants' => $tenants,
            'featuredProducts' => $featuredProducts,
            'searchParams' => $searchParams,
            'hasSearch' => $hasSearch,
            'user' => $userProfile,
        ]);
    }
}

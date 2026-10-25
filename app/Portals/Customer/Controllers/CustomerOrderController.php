<?php

namespace App\Portals\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\BookingAndOrdering\UseCases\GetCustomerOrdersUseCase;
use App\Portals\Customer\Requests\GetCustomerOrdersRequest;
use App\Portals\Customer\Resources\OrderListResource;
use App\Portals\Customer\Resources\UserProfileResource;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    /**
     * Display customer orders listing.
     */
    public function index(GetCustomerOrdersRequest $request, GetCustomerOrdersUseCase $useCase): View
    {
        /** @var User|null $user */
        $user = Auth::user();

        // For preview or testing when user is not logged in, provide fallback demo user
        if (! $user) {
            $user = User::first() ?? new User([
                'name' => 'Raditya Pratama',
                'email' => 'raditya@example.com',
            ]);
        }

        $filter = $request->input('filter', 'all');
        $sort = $request->input('sort', 'terbaru');

        $result = $useCase->execute($user, $filter, $sort);

        $orders = OrderListResource::collection($result['orders'])->resolve();
        $counts = $result['counts'];
        $userProfile = (new UserProfileResource($user))->resolve();

        return view('customer.orders.index', [
            'orders' => $orders,
            'counts' => $counts,
            'activeFilter' => $filter,
            'activeSort' => $sort,
            'user' => $userProfile,
        ]);
    }
}

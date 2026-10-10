<?php

namespace App\Http\Controllers;

use App\Models\GroupBuyingGroup;
use App\Models\Product;
use App\Services\GroupBuyingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupBuyingController extends Controller
{
    protected GroupBuyingService $groupService;

    public function __construct(GroupBuyingService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function index()
    {
        $activeGroups = GroupBuyingGroup::with(['product.seller', 'creator', 'participants.user'])
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest()
            ->paginate(12);

        $eligibleProducts = Product::with('seller')
            ->where('status', 'published')
            ->where('stock', '>', 5)
            ->take(8)
            ->get();

        return view('group-buying.index', compact('activeGroups', 'eligibleProducts'));
    }

    public function show(string $groupCode)
    {
        $group = GroupBuyingGroup::with(['product.seller', 'creator', 'participants.user'])
            ->where('group_code', $groupCode)
            ->firstOrFail();

        $userAlreadyJoined = Auth::check() && $group->participants->contains('user_id', Auth::id());

        return view('group-buying.show', compact('group', 'userAlreadyJoined'));
    }

    public function create(Request $request, Product $product)
    {
        $request->validate([
            'target_buyers' => 'required|integer|in:2,5,10',
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')->with('info', 'Please sign in to start a Group Buy.');
        }

        try {
            $group = $this->groupService->createGroup(
                Auth::user(),
                $product,
                $request->integer('target_buyers')
            );

            return redirect()->route('group-buying.show', $group->group_code)
                ->with('success', "Group Buy created! Share this group link with your friends to unlock ₹" . number_format($group->discounted_price) . " deal!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function join(Request $request, string $groupCode)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('info', 'Please sign in to join this Group Buy.');
        }

        try {
            $group = $this->groupService->joinGroup(Auth::user(), $groupCode);

            return redirect()->route('group-buying.show', $group->group_code)
                ->with('success', "Congratulations! You joined the Group Buy. Only {$group->remaining_buyers} more buyers needed to finalize!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

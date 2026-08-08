<?php

namespace App\Http\Controllers;

use App\Models\Usage;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UsageController extends Controller
{
    public function index(Request $request)
    {   
        $user = auth()->user();
        $unitId = $user->unit_id;
        $perPage = 10;

        // Scoped to the unit admin's own unit; an administrator sees every unit.
        $usages = Usage::query();

        if (! $user->seesAllUnits()) {
            $usages->whereHas('booking', function ($query) use ($unitId) {
                $query->whereHas('item', function ($query) use ($unitId) {
                    $query->where('unit_id', $unitId);
                });
            });
        }

        $items = Item::query();
        $items->join('booking', 'booking.item_id', '=', 'items.id')
            ->join('usages', 'usages.booking_id', '=', 'booking.id')
            ->join('users', 'users.id', '=', 'booking.user_id')
            ->select(
                'usages.id as usage_id',
                'booking.id as booking_id',
                'items.name as item_name',
                'items.category as item_category',
                'users.name as user_name',
                'users.email as user_email',
                'usages.status',
                'booking.start_date',
                'booking.end_date',
                'usages.due_date',
                'usages.note',
                'usages.created_at as usage_created_at',
                'booking.created_at as booking_created_at'
            );
            
        $status = $request->query('status');
        $search = $request->query('search');

        if ($status) {
            $usages->where('status', $status);
        }

        if ($search) {
            $usages->where(function ($query) use ($search) {
                $query->where('id', 'LIKE', '%' . $search . '%')
                    ->orWhere('status', 'LIKE', '%' . $search . '%')
                    ->orWhere('due_date', 'LIKE', '%' . $search . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('booking', function ($query) use ($search) {
                        $query->where('id', 'LIKE', '%' . $search . '%')
                            ->orWhere('start_date', 'LIKE', '%' . $search . '%')
                            ->orWhere('end_date', 'LIKE', '%' . $search . '%')
                            ->orWhere('created_at', 'LIKE', '%' . $search . '%')
                            ->orWhereHas('user', function ($query) use ($search) {
                                $query->where('id', 'LIKE', '%' . $search . '%')
                                    ->orWhere('name', 'LIKE', '%' . $search . '%')
                                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                                    ->orWhere('phone', 'LIKE', '%' . $search . '%');
                            })
                            ->orWhereHas('item', function ($query) use ($search) {
                                $query->where('id', 'LIKE', '%' . $search . '%')
                                    ->orWhere('name', 'LIKE', '%' . $search . '%')
                                    ->orWhereHas('category', function ($query) use ($search) {
                                        $query->where('name', 'LIKE', '%' . $search . '%');
                                    });
                            });
                    });
            });
        }

        $totalUsages = $usages->count();
        $totalItems = $usages->pluck('booking_id')->unique()->count('item_id');
        $totalBorrowers = $usages->pluck('booking_id')->unique()->count('user_id');

        $usages = $usages->latest()->paginate($perPage);
        $usages->appends(['status' => $status, 'search' => $search]);

        return view('unitadmin.usages.index', compact('usages', 'status', 'totalUsages', 'totalItems', 'totalBorrowers'));
    }

    public function show(Usage $usage)
    {
        $user = auth()->user();
        if ($user->isUnitAdmin() || $user->isAdministrator()) {
            if (! $user->seesAllUnits() && $usage->booking->item->unit_id !== $user->unit_id) {
                abort(403, 'Forbidden');
            }
            return view('unitadmin.usages.show', compact('usage'));
        } else {
            abort(403, 'Forbidden');
        }
    }

    /**
     * A unit admin may only touch usages belonging to its own unit; an
     * administrator may touch any of them. These write actions previously had
     * no check at all, so any unit admin could edit another unit's usage.
     */
    private function authoriseUsage(Usage $usage): void
    {
        $user = auth()->user();

        if ($user->isAdministrator()) {
            return;
        }

        if (! $user->isUnitAdmin() || $usage->booking->item->unit_id !== $user->unit_id) {
            abort(403, 'Forbidden');
        }
    }

    public function edit(Usage $usage)
    {
        $this->authoriseUsage($usage);

        return view('unitadmin.usages.edit', compact('usage'));
    }

    public function update(Request $request, Usage $usage)
    {
        $this->authoriseUsage($usage);

        $validatedData = $request->validate([
            'due_date' => 'required',
            'note' => 'nullable|string',
        ]);

        $usage->update($validatedData);

        return redirect()->route('usages.index')->with('success', 'Usage updated successfully.');
    }

    public function setUsed(Request $request, Usage $usage)
    {
        $this->authoriseUsage($usage);


        if ($usage->status == 'used') {
            return redirect()->back()->with('error', 'Item in this usage already set as used.');
        } elseif ($usage->status == 'returned') {
            return redirect()->back()->with('error', 'Borrower need to book the item(s) again.');
        } else if ($usage->status == 'expired') {
            return redirect()->back()->with('error', 'Borrower need to book the item(s) again.');
        } else if ($usage->status == 'expired') {
            return redirect()->back()->with('error', 'Borrower need to return the item(s) to you as soon as possible.');
        }

        $usage->status = 'used';
        $usage->save();
        
        return redirect()->route('usages.index')->with('success', 'Item in usage set as used successfully.');
    }

    public function updateExpiredAndLateUsages()
    {
        $expiredAndLateUsages = Usage::whereIn('status', ['awaiting use', 'used'])
            ->whereDate('due_date', '<', Carbon::today()->toDateString())
            ->get();

        foreach ($expiredAndLateUsages as $usage) {
            $booking = $usage->booking;
            $item = $booking->item;

            if ($usage->status === 'awaiting use') {
                $usage->status = 'expired';
                $usage->save();

                $item->quantity += $booking->quantity;
                $item->save();
            } elseif ($usage->status === 'used') {
                $usage->status = 'late';
                $usage->save();
            }
        }

        return response()->json(['message' => 'Expired and late usages updated successfully.']);
    }
}

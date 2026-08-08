<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
use App\Models\Booking;
use App\Models\Usage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $perPage = 10;

        if ($user->isUnitAdmin() || $user->isAdministrator()) {
            // A unit admin sees its own unit; an administrator sees every unit.
            $unitId = $user->unit_id;
            $items = Item::query();

            if (! $user->seesAllUnits()) {
                $items->where('unit_id', $unitId);
            }

            // No stock-status sweep here any more: Item keeps status in step with
            // quantity on every save.
            $status = $request->query('status');
            $search = $request->query('search');

            if ($status) {
                $items->where('status', $status);
            }

            if ($search) {
                $items->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', '%' . $search . '%')
                        ->orWhere('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('brand', 'LIKE', '%' . $search . '%')
                        ->orWhere('serial_number', 'LIKE', '%' . $search . '%')
                        ->orWhere('quantity', 'LIKE', '%' . $search . '%')
                        ->orWhere('status', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('category', function ($query) use ($search) {
                            $query->where('name', 'LIKE', '%' . $search . '%');
                        });
                });
            }

            $totalItems = $items->count();
            $totalCategories = $items->pluck('category_id')->unique()->count();
            $totalBrands = $items->pluck('brand')->unique()->count();

            $items = $items->paginate($perPage);
            $items->appends(['status' => $status, 'search' => $search]);

            return view('unitadmin.items.index', compact('items', 'status', 'totalItems', 'totalCategories', 'totalBrands'));
        }
        elseif ($user->isBorrower()) {

            $items = Item::where('status', Item::STATUS_AVAILABLE);
            $search = $request->query('search');

            if ($search) {
                $items->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', '%' . $search . '%')
                        ->orWhere('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('brand', 'LIKE', '%' . $search . '%')
                        ->orWhere('serial_number', 'LIKE', '%' . $search . '%')
                        ->orWhere('quantity', 'LIKE', '%' . $search . '%')
                        ->orWhere('status', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('category', function ($query) use ($search) {
                            $query->where('name', 'LIKE', '%' . $search . '%');
                        });
                });
            }

            $totalItems = $items->count();
            $totalCategories = $items->pluck('category_id')->unique()->count();
            $totalBrands = $items->pluck('brand')->unique()->count();

            $items = $items->paginate($perPage);

            return view('borrower.items.index', compact('items', 'totalItems', 'totalCategories', 'totalBrands'));
        } else {
            abort(403, 'Forbidden');
        }
    }

public function create()
{
    $user = auth()->user();

    if (! ($user->isAdministrator() || $user->isUnitAdmin())) {
        abort(403, 'Forbidden');
    }

    $categories = Category::all();

    // A unit admin files items under its own unit. An administrator belongs to
    // no unit, so it has to be told which one the item goes to.
    $units = $user->isAdministrator() ? Unit::orderBy('name')->get() : collect();

    return view('unitadmin.items.create', compact('categories', 'units'));
}

    public function show(Item $item) {
        $user = auth()->user();
        if ($user->isUnitAdmin() || $user->isAdministrator()) {
            // Unit admins are confined to their own unit; administrators are not.
            if (! $user->seesAllUnits() && $item->unit_id !== $user->unit_id) {
                abort(403, 'Forbidden');
            }
            return view('unitadmin.items.show', compact('item'));

        } elseif ($user->isBorrower()) {

            if ($item->status !== 'available') {
                abort(403, 'Forbidden');
            }

            $unitId = $item->unit_id;
            $unitAdmins = User::where('unit_id', $unitId)->where('role', 'unitadmin')->get();

            return view('borrower.items.show', compact('item', 'unitAdmins'));
        } else {
            abort(403, 'Forbidden');
        }
    }

   public function store(Request $request)
{
    $user = auth()->user();

    if (! ($user->isAdministrator() || $user->isUnitAdmin())) {
        abort(403, 'Forbidden');
    }

    $rules = [
        'category_id' => 'required',
        'name' => 'required',
        'brand' => 'required',
        'quantity' => 'required|integer|min:1',
        'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'status' => 'required|in:available,not available',
        'description' => 'nullable',
    ];

    // An administrator has no unit of its own, so it must name the target unit;
    // otherwise the item would be saved with a null unit and disappear from
    // every unit-scoped listing.
    if ($user->isAdministrator()) {
        $rules['unit_id'] = 'required|exists:units,id';
    }

    $validatedData = $request->validate($rules);

    if ($request->has('serial_number') && $request->filled('serial_number')) {
        $request->merge(['quantity' => 1]);
    }


    $unitId = $user->isAdministrator() ? $validatedData['unit_id'] : $user->unit_id;

    $photoPath = $request->file('photo')->store('public/img/items');

    $item = new Item([
        'category_id' => $validatedData['category_id'],
        'unit_id' => $unitId,
        'name' => $validatedData['name'],
        'brand' => $validatedData['brand'],
        'serial_number' => $request->serial_number,
        'photo' => Storage::url($photoPath),
        'quantity' => $request->quantity,
        'status' => $validatedData['status'],
        'description' => $validatedData['description'],
    ]);

    $success = $item->save();

    if ($success) {
        return redirect()->route('items.index')->with('success', 'Item has been created.');
    } else {
        return redirect()->route('items.index')->with('error', 'Item failed to create.');
    }
}


  public function edit(Item $item)
{
    $user = auth()->user();
    $categories = Category::all();

    if ($user->isAdministrator()) {
        // Administrators may edit any item, and may move it between units.
        $units = Unit::orderBy('name')->get();

        return view('unitadmin.items.edit', compact('item', 'categories', 'units'));
    } elseif ($user->isUnitAdmin()) {
        // Unit admins are limited to items in their own unit.
        if ($item->unit_id !== $user->unit_id) {
            abort(403, 'Forbidden');
        }

        return view('unitadmin.items.edit', compact('item', 'categories') + ['units' => collect()]);
    } else {
        abort(403, 'Forbidden');
    }
}


    public function update(Request $request, Item $item)
    {
        $user = auth()->user();

        if ($user->isUnitAdmin() && $item->unit_id !== $user->unit_id) {
            abort(403, 'Forbidden');
        } elseif (! ($user->isAdministrator() || $user->isUnitAdmin())) {
            abort(403, 'Forbidden');
        }

        $rules = [
            'category_id' => 'required',
            'name' => 'required',
            'brand' => 'required',
            'quantity' => 'required|integer',
            'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            // "empty" is derived from the quantity by the model, so it is not an
            // accepted input here.
            'status' => 'required|in:available,not available',
            'description' => 'nullable',
        ];

        // Only an administrator may move an item to a different unit.
        if ($user->isAdministrator()) {
            $rules['unit_id'] = 'nullable|exists:units,id';
        }

        $validatedData = $request->validate($rules);

        // Keep the item where it already is unless an administrator explicitly
        // reassigns it. The previous code wrote the *editing user's* unit here,
        // which blanked the unit on every item an administrator touched.
        $unitId = $user->isAdministrator()
            ? ($validatedData['unit_id'] ?? $item->unit_id)
            : $item->unit_id;

        if ($request->has('serial_number') && $request->filled('serial_number') && $request->quantity != 0) {
            $request->merge(['quantity' => 1]);
        }

    
        $item->category_id = $validatedData['category_id'];
        $item->unit_id = $unitId;
        $item->name = $validatedData['name'];
        $item->brand = $validatedData['brand'];
        $item->description = $request->description;

        if ($request->has('serial_number')) {
            $item->serial_number = $request->serial_number;
        }
    
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('public/img/items');
            $item->photo = Storage::url($photoPath);
        }
    
        $item->quantity = $request->quantity;
        $item->status = $request->status;
        $item->save();
    
        return redirect('/items')->with('success', 'Item updated successfully.');
    }    

    public function destroy(Item $item)
    {
        $isItemBorrowed = Booking::where('status', 'pending')
            ->where('item_id', $item->id)
            ->exists();

        $isItemInUse = Usage::whereIn('status', ['awaiting use', 'used', 'late'])
            ->whereHas('booking', function ($query) use ($item) {
                $query->where('item_id', $item->id);
            })
            ->exists();

        if ($item->status == 'not available' && !$isItemBorrowed && !$isItemInUse) {
            $item->delete();
            return redirect('/items')->with('success', 'Item deleted successfully.');
        } elseif ($isItemBorrowed) {
            return redirect('/items')->with('error', 'Item failed to delete. There is a borrower who is applying for a loan of this item.');
        } elseif ($isItemInUse) {
            return redirect('/items')->with('error', 'Item failed to delete. Item is currently in use.');
        } else {
            return redirect('/items')->with('error', 'Item failed to delete. You need to change the status to "not available" first.');
        }
    }
}

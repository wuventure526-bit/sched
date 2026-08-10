<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Administrator-only management of every account in the system.
 *
 * Deactivation is a soft delete rather than a separate flag: the users table
 * already has deleted_at, and the login query filters on it, so a deactivated
 * account cannot sign in while all of its bookings, usages and request history
 * stay intact and the account can be brought back unchanged.
 */
class UserController extends Controller
{
    /** Role value => label shown in the UI. */
    public const ROLES = [
        'administrator' => 'Administrator',
        'unitadmin'     => 'Unit Admin',
        'borrower'      => 'Borrower',
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! auth()->check() || ! auth()->user()->isAdministrator()) {
                abort(403, 'Forbidden');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        // Trashed rows are deactivated accounts, not deleted ones, so they
        // belong in the list rather than hidden from it.
        $users = User::withTrashed()->with('unit');

        $role   = $request->query('role');
        $status = $request->query('status');
        $search = $request->query('search');

        if ($role && array_key_exists($role, self::ROLES)) {
            $users->where('role', $role);
        }

        if ($status === 'active') {
            $users->whereNull('deleted_at');
        } elseif ($status === 'inactive') {
            $users->whereNotNull('deleted_at');
        }

        if ($search) {
            $users->where(function ($query) use ($search) {
                $query->where('id', 'LIKE', '%' . $search . '%')
                    ->orWhere('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search . '%')
                    ->orWhere('address', 'LIKE', '%' . $search . '%')
                    ->orWhere('city', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('unit', function ($q) use ($search) {
                        $q->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('location', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        $counts = [
            'total'         => User::withTrashed()->count(),
            'active'        => User::count(),
            'inactive'      => User::onlyTrashed()->count(),
            'administrator' => User::where('role', 'administrator')->count(),
            'unitadmin'     => User::where('role', 'unitadmin')->count(),
            'borrower'      => User::where('role', 'borrower')->count(),
        ];

        $users = $users->orderBy('name')->paginate(10);
        $users->appends($request->only('role', 'status', 'search'));

        return view('administrator.users.index', [
            'users'  => $users,
            'counts' => $counts,
            'roles'  => self::ROLES,
            'role'   => $role,
            'status' => $status,
        ]);
    }

    public function show(User $user)
    {
        return view('administrator.users.show', [
            'user'  => $user,
            'roles' => self::ROLES,
        ]);
    }

    public function create()
    {
        return view('administrator.users.create', [
            'units' => Unit::orderBy('name')->get(),
            'roles' => self::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $user = new User([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
            'phone'    => $validated['phone'] ?? null,
            'role'     => $validated['role'],
            'address'  => $validated['address'] ?? null,
            'city'     => $validated['city'] ?? null,
            // Only a unit admin belongs to a unit; the column stays null for
            // everyone else so unit-scoped queries cannot match them by accident.
            'unit_id'  => $validated['role'] === 'unitadmin' ? $validated['unit_id'] : null,
        ]);

        $user->save();

        return redirect()->route('users.index')
            ->with('success', self::ROLES[$user->role] . ' "' . $user->name . '" created.');
    }

    public function edit(User $user)
    {
        return view('administrator.users.edit', [
            'user'  => $user,
            'units' => Unit::orderBy('name')->get(),
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate($this->rules($user));

        // Refuse to strip the administrator role from the only one left, for the
        // same reason as deactivation: nobody could administer the system after.
        if ($user->isAdministrator()
            && $validated['role'] !== 'administrator'
            && $this->otherActiveAdministrators($user) === 0) {
            return redirect()->route('users.edit', $user)
                ->with('error', 'This is the only active administrator. Promote another account before changing this one\'s role.');
        }

        $user->name    = $validated['name'];
        $user->email   = $validated['email'];
        $user->phone   = $validated['phone'] ?? null;
        $user->address = $validated['address'] ?? null;
        $user->city    = $validated['city'] ?? null;
        $user->role    = $validated['role'];
        $user->unit_id = $validated['role'] === 'unitadmin' ? $validated['unit_id'] : null;

        // Blank means "leave the current password alone".
        if (! empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Account "' . $user->name . '" updated.');
    }

    /**
     * Deactivate: the account can no longer sign in, but nothing it owns is
     * removed and it can be reactivated later.
     */
    public function deactivate(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        if ($user->isAdministrator() && $this->otherActiveAdministrators($user) === 0) {
            return redirect()->route('users.index')
                ->with('error', 'This is the only active administrator. Promote another account first, or nobody could administer the system.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Account "' . $user->name . '" deactivated and can no longer sign in.');
    }

    public function reactivate(User $user)
    {
        if (! $user->trashed()) {
            return redirect()->route('users.index')
                ->with('error', 'That account is already active.');
        }

        $user->restore();

        return redirect()->route('users.index')
            ->with('success', 'Account "' . $user->name . '" reactivated.');
    }

    /**
     * Validation shared by store and update. On update the password becomes
     * optional and the email uniqueness check ignores the row being edited.
     */
    private function rules(?User $user = null): array
    {
        return [
            'name'  => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                // Deactivated accounts keep their address reserved, so a
                // reactivated user does not collide with a newer one.
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => $user
                ? 'nullable|min:8|confirmed'
                : 'required|min:8|confirmed',
            'role'    => ['required', Rule::in(array_keys(self::ROLES))],
            'unit_id' => 'required_if:role,unitadmin|nullable|exists:units,id',
            'phone'   => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city'    => 'nullable|string|max:255',
        ];
    }

    /** Active administrators other than the given account. */
    private function otherActiveAdministrators(User $user): int
    {
        return User::where('role', 'administrator')
            ->where('id', '!=', $user->id)
            ->count();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\RequestDocument;
use Illuminate\Http\Request;

class RequestApprovalController extends Controller
{
    public function index(Request $request)
    {
        // Approver inbox: submitted/noted requests
        $docs = RequestDocument::with('user')
            ->whereIn('status', ['submitted', 'noted'])
            ->latest()
            ->paginate(15);

        return view('requests.approval.index', compact('docs'));
    }

    public function note(Request $request, RequestDocument $requestDocument)
    {
        $this->authorizeApprover();

        if ($requestDocument->status !== 'submitted') {
            return back()->withErrors(['status' => 'Only submitted requests can be noted.']);
        }

        $requestDocument->update([
            'status' => 'noted',
            'noted_at' => now(),
            'noted_by' => auth()->id(),
        ]);

        return back()->with('success', 'Request noted.');
    }

    public function approve(Request $request, RequestDocument $requestDocument)
    {
        $this->authorizeApprover();

        if (!in_array($requestDocument->status, ['submitted', 'noted'], true)) {
            return back()->withErrors(['status' => 'Only submitted/noted requests can be approved.']);
        }

        $requestDocument->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejection_reason' => null,
            'rejected_at' => null,
            'rejected_by' => null,
        ]);

        return back()->with('success', 'Request approved.');
    }

    public function reject(Request $request, RequestDocument $requestDocument)
    {
        $this->authorizeApprover();

        $data = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        if (!in_array($requestDocument->status, ['submitted', 'noted'], true)) {
            return back()->withErrors(['status' => 'Only submitted/noted requests can be rejected.']);
        }

        $requestDocument->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => $data['reason'],
        ]);

        return back()->with('success', 'Request rejected.');
    }

    private function authorizeApprover(): void
    {
        // Adjust this to match your middleware/role system.
        // If you have custom middleware `unitadmin`, this controller will be behind it anyway.
        if (!auth()->user()->isUnitAdmin() && !auth()->user()->isAdministrator()) {
            abort(403, 'Unauthorized');
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\RequestDocument;
use App\Models\LiquidationDetail;
use App\Models\PaymentDetail;
use App\Models\RequestItem;
use App\Models\LiquidationPurpose;
use App\Models\BusinessTripDetail;
use App\Models\RevolvingFundDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RequestDocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $status = $request->query('status');

        $query = RequestDocument::with([
            'user',
            'items',
            'paymentDetail',
            'liquidationDetail',
            'businessTripDetail',
        ]);

        // Approvers see workflow statuses
        if ($this->isApprover($user)) {
            $query->whereIn('status', ['submitted', 'noted', 'approved', 'rejected']);
        } else {
            $query->where('user_id', $user->id);
        }

        if ($status && in_array($status, ['submitted', 'noted', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $docs = $query->latest()->paginate(10);

        return view('requests.index', compact('docs', 'status'));
    }

    public function create()
    {
        return view('requests.create');
    }

    public function store(Request $request)
    {
        $type = $request->input('type');

      if (!in_array($type, ['liquidation', 'payment', 'business_trip', 'revolving_fund'], true)) {
            return back()->withErrors(['type' => 'Invalid request type.'])->withInput();
        }

        return match ($type) {
            'liquidation'    => $this->storeLiquidation($request),
            'payment'        => $this->storePayment($request),
            'business_trip'  => $this->storeBusinessTrip($request),
            'revolving_fund'  => $this->storeRevolvingFund($request),
        };
    }

    private function storeLiquidation(Request $request)
    {
        $validated = $request->validate([
            'department' => 'nullable|string|max:255',
            'name'       => 'nullable|string|max:255',
            'week_no'    => 'nullable|string|max:50',
            'form_no'    => 'nullable|string|max:50',
            'date_from'  => 'nullable|date',
            'date_to'    => 'nullable|date',

            'cash_advance_amount' => 'nullable|numeric|min:0',
            'cash_advance_date'   => 'nullable|date',
            'previous_balance'    => 'nullable|numeric',
            'starting_balance'    => 'nullable|numeric',

            'purposes'            => 'nullable|array',
            'purposes.*'          => 'string|max:100',
            'purpose_other_text'  => 'nullable|string|max:255',

            'items'               => 'required|array|min:1',
            'items.*.date'        => 'nullable|date',
            'items.*.particulars' => 'required|string|max:255',
            'items.*.amount'      => 'required|numeric|min:0',
        ]);

        $user = auth()->user();

        $doc = DB::transaction(function () use ($validated, $user) {

            $doc = RequestDocument::create([
                'request_no'  => $this->generateRequestNo(),
                'type'        => 'liquidation',
                'user_id'     => $user->id,
                'department'  => $validated['department'] ?? null,
                'name'        => $validated['name'] ?? null,

                'week_no'     => $validated['week_no'] ?? null,
                'form_no'     => $validated['form_no'] ?? null,
                'date_from'   => $validated['date_from'] ?? null,
                'date_to'     => $validated['date_to'] ?? null,

                'status'      => 'draft',
            ]);

            $detail = LiquidationDetail::create([
                'request_document_id' => $doc->id,

                'week_no'             => $validated['week_no'] ?? null,
                'form_no'             => $validated['form_no'] ?? null,
                'date_from'           => $validated['date_from'] ?? null,
                'date_to'             => $validated['date_to'] ?? null,

                'cash_advance_amount' => $validated['cash_advance_amount'] ?? null,
                'cash_advance_date'   => $validated['cash_advance_date'] ?? null,
                'previous_balance'    => $validated['previous_balance'] ?? null,
                'starting_balance'    => $validated['starting_balance'] ?? null,
                'reimbursement_amount'=> null,
                'ending_balance'      => null,
            ]);

            // Purposes
            $purposes = $validated['purposes'] ?? [];
            foreach ($purposes as $p) {
                LiquidationPurpose::create([
                    'request_document_id' => $doc->id,
                    'purpose'             => $p,
                    'other_text'          => $p === 'others' ? ($validated['purpose_other_text'] ?? null) : null,
                ]);
            }

            // Items
            foreach ($validated['items'] as $row) {
                RequestItem::create([
                    'request_document_id' => $doc->id,
                    'item_date'           => $row['date'] ?? null,
                    'particulars'         => $row['particulars'],
                    'amount'              => $row['amount'],
                ]);
            }

            // Totals
            $itemsTotal = (float) $doc->items()->sum('amount');
            $startBal = (float)($detail->starting_balance ?? 0);

            $detail->update([
                'reimbursement_amount' => $itemsTotal,
                'ending_balance'       => $startBal - $itemsTotal,
            ]);

            return $doc;
        });

        return redirect()
            ->route('requests.show', $doc->id)
            ->with('success', 'Liquidation saved as draft.');
    }

    private function storeBusinessTrip(Request $request)
    {
        $validated = $request->validate([
            'driver_name'       => 'required|string|max:255',
            'trip_date'         => 'required|date',
            'vehicle_plate_no'  => 'required|string|max:255',
            'total_mileage_km'  => 'nullable|numeric|min:0',
            'speedometer_begin' => 'nullable|string|max:255',
            'speedometer_end'   => 'nullable|string|max:255',
            'time_out'          => 'nullable|date_format:H:i',
            'time_in'           => 'nullable|date_format:H:i',
            'purpose'           => 'nullable|string',
            'checked_by'        => 'nullable|string|max:255',
            'noted_by'          => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        $doc = DB::transaction(function () use ($validated, $user) {

            $doc = RequestDocument::create([
                'request_no' => $this->generateRequestNo(),
                'type'       => 'business_trip',
                'user_id'    => $user->id,
                'status'     => 'draft',
            ]);

            BusinessTripDetail::create([
                'request_document_id' => $doc->id,
                'driver_name'         => $validated['driver_name'],
                'trip_date'           => $validated['trip_date'],
                'vehicle_plate_no'    => $validated['vehicle_plate_no'],
                'total_mileage_km'    => $validated['total_mileage_km'] ?? null,
                'speedometer_begin'   => $validated['speedometer_begin'] ?? null,
                'speedometer_end'     => $validated['speedometer_end'] ?? null,
                'time_out'            => $validated['time_out'] ?? null,
                'time_in'             => $validated['time_in'] ?? null,
                'purpose'             => $validated['purpose'] ?? null,
                'checked_by'          => $validated['checked_by'] ?? null,
                'noted_by'            => $validated['noted_by'] ?? null,
            ]);

            return $doc;
        });

        return redirect()
            ->route('requests.show', $doc->id)
            ->with('success', 'Business trip saved as draft.');
    }

    private function storePayment(Request $request)
    {
        $validated = $request->validate([
            'department' => 'nullable|string|max:255',
            'name'       => 'nullable|string|max:255',

            'payable_to' => 'required|string|max:255',
            'address'    => 'nullable|string|max:255',
            'date'       => 'nullable|date',

            'items'               => 'required|array|min:1',
            'items.*.particulars' => 'required|string|max:255',
            'items.*.amount'      => 'required|numeric|min:0',
        ]);

        $user = auth()->user();

        $doc = DB::transaction(function () use ($validated, $user) {

            $doc = RequestDocument::create([
                'request_no' => $this->generateRequestNo(),
                'type'       => 'payment',
                'user_id'    => $user->id,
                'department' => $validated['department'] ?? null,
                'name'       => $validated['name'] ?? null,
                'status'     => 'draft',
            ]);

            $paymentDetail = PaymentDetail::create([
                'request_document_id' => $doc->id,
                'payable_to'          => $validated['payable_to'],
                'address'             => $validated['address'] ?? null,
                'date'                => $validated['date'] ?? null,
                'total_amount'        => 0,
            ]);

            foreach ($validated['items'] as $row) {
                RequestItem::create([
                    'request_document_id' => $doc->id,
                    'item_date'           => null,
                    'particulars'         => $row['particulars'],
                    'amount'              => $row['amount'],
                ]);
            }

            $total = (float) $doc->items()->sum('amount');
            $paymentDetail->update(['total_amount' => $total]);

            return $doc;
        });

        return redirect()
            ->route('requests.show', $doc->id)
            ->with('success', 'Payment request saved as draft.');
    }

    public function show(RequestDocument $requestDocument)
    {
        $this->authorizeOwnerOrApprover($requestDocument);

        $requestDocument->load([
            'items',
            'liquidationDetail',
            'paymentDetail',
            'revolvingFundDetail',
            'purposes',
            'user',
            'notedByUser',
            'approvedByUser',
            'businessTripDetail',
        ]);

        return view('requests.show', ['doc' => $requestDocument]);
    }

    public function submit(RequestDocument $requestDocument)
    {
        $this->authorizeOwner($requestDocument);

        if ($requestDocument->status !== 'draft') {
            return back()->withErrors(['status' => 'Only draft requests can be submitted.']);
        }

        $requestDocument->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'Request submitted successfully.');
    }
private function storeRevolvingFund(Request $request)
{
    $validated = $request->validate([
        'department' => 'nullable|string|max:255',
        'name'       => 'nullable|string|max:255',

        'week_no'    => 'nullable|string|max:50',
        'date_from'  => 'nullable|date',
        'date_to'    => 'nullable|date',

        'cash_advance_amount' => 'nullable|numeric|min:0',
        'cash_advance_date'   => 'nullable|date',
        'previous_balance'    => 'nullable|numeric',
        'starting_balance'    => 'nullable|numeric',

        'items'               => 'required|array|min:1',
        'items.*.date'        => 'nullable|date',
        'items.*.particulars' => 'required|string|max:255',
        'items.*.amount'      => 'required|numeric|min:0',
    ]);

    $user = auth()->user();

    $doc = DB::transaction(function () use ($validated, $user) {

        $doc = RequestDocument::create([
            'request_no' => $this->generateRequestNo(),
            'type'       => 'revolving_fund',
            'user_id'    => $user->id,
            'department' => $validated['department'] ?? null,
            'name'       => $validated['name'] ?? null,

            // optional if you store these in request_documents too:
            'week_no'    => $validated['week_no'] ?? null,
            'date_from'  => $validated['date_from'] ?? null,
            'date_to'    => $validated['date_to'] ?? null,

            'status'     => 'draft',
        ]);

        $detail = RevolvingFundDetail::create([
            'request_document_id' => $doc->id,
            'week_no'             => $validated['week_no'] ?? null,
            'date_from'           => $validated['date_from'] ?? null,
            'date_to'             => $validated['date_to'] ?? null,
            'cash_advance_amount' => $validated['cash_advance_amount'] ?? null,
            'cash_advance_date'   => $validated['cash_advance_date'] ?? null,
            'previous_balance'    => $validated['previous_balance'] ?? null,
            'starting_balance'    => $validated['starting_balance'] ?? null,
            'reimbursement_amount'=> null,
            'ending_balance'      => null,
        ]);

        foreach ($validated['items'] as $row) {
            RequestItem::create([
                'request_document_id' => $doc->id,
                'item_date'           => $row['date'] ?? null,
                'particulars'         => $row['particulars'],
                'amount'              => $row['amount'],
                'rejection_remark'    => null,
            ]);
        }

        $itemsTotal = (float) $doc->items()->sum('amount');
        $startBal = (float)($detail->starting_balance ?? 0);

        $detail->update([
            'reimbursement_amount' => $itemsTotal,
            'ending_balance'       => $startBal - $itemsTotal,
        ]);

        return $doc;
    });

    return redirect()
        ->route('requests.show', $doc->id)
        ->with('success', 'Revolving fund saved as draft.');
}

private function updateRevolvingFund(Request $request, RequestDocument $doc)
{
    $validated = $request->validate([
        'department' => 'nullable|string|max:255',
        'name'       => 'nullable|string|max:255',

        'week_no'    => 'nullable|string|max:50',
        'date_from'  => 'nullable|date',
        'date_to'    => 'nullable|date',

        'cash_advance_amount' => 'nullable|numeric|min:0',
        'cash_advance_date'   => 'nullable|date',
        'previous_balance'    => 'nullable|numeric',
        'starting_balance'    => 'nullable|numeric',

        'items'               => 'required|array|min:1',
        'items.*.date'        => 'nullable|date',
        'items.*.particulars' => 'required|string|max:255',
        'items.*.amount'      => 'required|numeric|min:0',
    ]);

    DB::transaction(function () use ($validated, $doc) {

        $doc->update([
            'department' => $validated['department'] ?? null,
            'name'       => $validated['name'] ?? null,
            'week_no'    => $validated['week_no'] ?? null,
            'date_from'  => $validated['date_from'] ?? null,
            'date_to'    => $validated['date_to'] ?? null,
        ]);

        $detail = $doc->revolvingFundDetail;

        if ($detail) {
            $detail->update([
                'week_no'             => $validated['week_no'] ?? null,
                'date_from'           => $validated['date_from'] ?? null,
                'date_to'             => $validated['date_to'] ?? null,
                'cash_advance_amount' => $validated['cash_advance_amount'] ?? null,
                'cash_advance_date'   => $validated['cash_advance_date'] ?? null,
                'previous_balance'    => $validated['previous_balance'] ?? null,
                'starting_balance'    => $validated['starting_balance'] ?? null,
            ]);
        }

        $doc->items()->delete();

        foreach ($validated['items'] as $row) {
            RequestItem::create([
                'request_document_id' => $doc->id,
                'item_date'           => $row['date'] ?? null,
                'particulars'         => $row['particulars'],
                'amount'              => $row['amount'],
                'rejection_remark'    => null,
            ]);
        }

        $itemsTotal = (float) $doc->items()->sum('amount');
        $startBal = (float)($detail->starting_balance ?? 0);

        if ($detail) {
            $detail->update([
                'reimbursement_amount' => $itemsTotal,
                'ending_balance'       => $startBal - $itemsTotal,
            ]);
        }
    });

    // ? important: applies to ALL forms (including revolving_fund)
    $this->restoreStatusAfterRejectedUpdate($doc->fresh());

    return redirect()
        ->route('requests.show', $doc->id)
        ->with('success', 'Revolving fund updated successfully.');
}
    /**
     * ? EDIT RULE (All forms):
     * Owner can edit as long as status is NOT noted and NOT approved.
     */
    public function edit(RequestDocument $requestDocument)
    {
        $this->authorizeOwner($requestDocument);

        if (in_array($requestDocument->status, ['noted', 'approved'], true)) {
            return redirect()
                ->route('requests.show', $requestDocument->id)
                ->withErrors(['status' => 'This request can no longer be edited because it is already ' . $requestDocument->status . '.']);
        }

        $requestDocument->load([
            'items',
            'liquidationDetail',
            'paymentDetail',
            'purposes',
            'businessTripDetail',
        ]);

        return view('requests.edit', [
            'doc' => $requestDocument
        ]);
    }

    /**
     * ? UPDATE RULE (All forms):
     * Owner can update as long as status is NOT noted and NOT approved.
     * If it was REJECTED, after update it returns to NOTED (if it was previously noted) else SUBMITTED.
     */
    public function update(Request $request, RequestDocument $requestDocument)
    {
        $this->authorizeOwner($requestDocument);

        if (in_array($requestDocument->status, ['noted', 'approved'], true)) {
            return redirect()
                ->route('requests.show', $requestDocument->id)
                ->withErrors(['status' => 'This request can no longer be updated because it is already ' . $requestDocument->status . '.']);
        }

        return match ($requestDocument->type) {
            'liquidation'   => $this->updateLiquidation($request, $requestDocument),
            'payment'       => $this->updatePayment($request, $requestDocument),
            'business_trip' => $this->updateBusinessTrip($request, $requestDocument),
            'revolving_fund' => $this->updateRevolvingFund($request, $requestDocument),
            default         => back()->withErrors(['type' => 'Invalid request type.']),
        };
    }

    private function updateLiquidation(Request $request, RequestDocument $doc)
    {
        $validated = $request->validate([
            'department' => 'nullable|string|max:255',
            'name'       => 'nullable|string|max:255',
            'week_no'    => 'nullable|string|max:50',
            'date_from'  => 'nullable|date',
            'date_to'    => 'nullable|date',

            'cash_advance_amount' => 'nullable|numeric|min:0',
            'cash_advance_date'   => 'nullable|date',
            'previous_balance'    => 'nullable|numeric',
            'starting_balance'    => 'nullable|numeric',

            'items'               => 'required|array|min:1',
            'items.*.date'        => 'nullable|date',
            'items.*.particulars' => 'required|string|max:255',
            'items.*.amount'      => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $doc) {

            $doc->update([
                'department' => $validated['department'] ?? null,
                'name'       => $validated['name'] ?? null,
                'week_no'    => $validated['week_no'] ?? null,
                'date_from'  => $validated['date_from'] ?? null,
                'date_to'    => $validated['date_to'] ?? null,
            ]);

            $detail = $doc->liquidationDetail;
            if ($detail) {
                $detail->update([
                    'cash_advance_amount' => $validated['cash_advance_amount'] ?? null,
                    'cash_advance_date'   => $validated['cash_advance_date'] ?? null,
                    'previous_balance'    => $validated['previous_balance'] ?? null,
                    'starting_balance'    => $validated['starting_balance'] ?? null,
                ]);
            }

            // Keep old remarks? On update we rebuild items, so remarks should be cleared.
            $doc->items()->delete();

            foreach ($validated['items'] as $row) {
                RequestItem::create([
                    'request_document_id' => $doc->id,
                    'item_date'           => $row['date'] ?? null,
                    'particulars'         => $row['particulars'],
                    'amount'              => $row['amount'],
                    'rejection_remark'    => null,
                ]);
            }

            $total = (float) $doc->items()->sum('amount');
            $startBal = (float)($detail->starting_balance ?? 0);

            if ($detail) {
                $detail->update([
                    'reimbursement_amount' => $total,
                    'ending_balance'       => $startBal - $total,
                ]);
            }
        });

        // ? Must be outside transaction
        $this->restoreStatusAfterRejectedUpdate($doc->fresh());

        return redirect()
            ->route('requests.show', $doc->id)
            ->with('success', 'Request updated successfully.');
    }

    private function updatePayment(Request $request, RequestDocument $doc)
    {
        $validated = $request->validate([
            'department' => 'nullable|string|max:255',
            'name'       => 'nullable|string|max:255',

            'payable_to' => 'required|string|max:255',
            'address'    => 'nullable|string|max:255',
            'date'       => 'nullable|date',

            'items'               => 'required|array|min:1',
            'items.*.particulars' => 'required|string|max:255',
            'items.*.amount'      => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $doc) {

            $doc->update([
                'department' => $validated['department'] ?? null,
                'name'       => $validated['name'] ?? null,
            ]);

            $paymentDetail = $doc->paymentDetail;

            if ($paymentDetail) {
                $paymentDetail->update([
                    'payable_to' => $validated['payable_to'],
                    'address'    => $validated['address'] ?? null,
                    'date'       => $validated['date'] ?? null,
                ]);
            } else {
                $paymentDetail = PaymentDetail::create([
                    'request_document_id' => $doc->id,
                    'payable_to'          => $validated['payable_to'],
                    'address'             => $validated['address'] ?? null,
                    'date'                => $validated['date'] ?? null,
                    'total_amount'        => 0,
                ]);
            }

            $doc->items()->delete();

            foreach ($validated['items'] as $row) {
                RequestItem::create([
                    'request_document_id' => $doc->id,
                    'item_date'           => null,
                    'particulars'         => $row['particulars'],
                    'amount'              => $row['amount'],
                    'rejection_remark'    => null,
                ]);
            }

            $total = (float) $doc->items()->sum('amount');
            $paymentDetail->update(['total_amount' => $total]);
        });

        // ? Must be outside transaction
        $this->restoreStatusAfterRejectedUpdate($doc->fresh());

        return redirect()
            ->route('requests.show', $doc->id)
            ->with('success', 'Request updated successfully.');
    }

    private function updateBusinessTrip(Request $request, RequestDocument $doc)
    {
        $validated = $request->validate([
            'driver_name'       => 'required|string|max:255',
            'trip_date'         => 'required|date',
            'vehicle_plate_no'  => 'required|string|max:255',
            'total_mileage_km'  => 'nullable|numeric|min:0',
            'speedometer_begin' => 'nullable|string|max:255',
            'speedometer_end'   => 'nullable|string|max:255',
            'time_out'          => 'nullable|date_format:H:i',
            'time_in'           => 'nullable|date_format:H:i',
            'purpose'           => 'nullable|string',
            'checked_by'        => 'nullable|string|max:255',
            'noted_by'          => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $doc) {

            $detail = $doc->businessTripDetail;

            if ($detail) {
                $detail->update([
                    'driver_name'       => $validated['driver_name'],
                    'trip_date'         => $validated['trip_date'],
                    'vehicle_plate_no'  => $validated['vehicle_plate_no'],
                    'total_mileage_km'  => $validated['total_mileage_km'] ?? null,
                    'speedometer_begin' => $validated['speedometer_begin'] ?? null,
                    'speedometer_end'   => $validated['speedometer_end'] ?? null,
                    'time_out'          => $validated['time_out'] ?? null,
                    'time_in'           => $validated['time_in'] ?? null,
                    'purpose'           => $validated['purpose'] ?? null,
                    'checked_by'        => $validated['checked_by'] ?? null,
                    'noted_by'          => $validated['noted_by'] ?? null,
                ]);
            } else {
                BusinessTripDetail::create([
                    'request_document_id' => $doc->id,
                    'driver_name'         => $validated['driver_name'],
                    'trip_date'           => $validated['trip_date'],
                    'vehicle_plate_no'    => $validated['vehicle_plate_no'],
                    'total_mileage_km'    => $validated['total_mileage_km'] ?? null,
                    'speedometer_begin'   => $validated['speedometer_begin'] ?? null,
                    'speedometer_end'     => $validated['speedometer_end'] ?? null,
                    'time_out'            => $validated['time_out'] ?? null,
                    'time_in'             => $validated['time_in'] ?? null,
                    'purpose'             => $validated['purpose'] ?? null,
                    'checked_by'          => $validated['checked_by'] ?? null,
                    'noted_by'            => $validated['noted_by'] ?? null,
                ]);
            }
        });

        // ? Must be outside transaction
        $this->restoreStatusAfterRejectedUpdate($doc->fresh());

        return redirect()
            ->route('requests.show', $doc->id)
            ->with('success', 'Request updated successfully.');
    }

    public function note(RequestDocument $requestDocument)
    {
        $this->authorizeRole('unitadmin');

        if ($requestDocument->status !== 'submitted') {
            return back()->withErrors(['status' => 'Only submitted requests can be noted.']);
        }

        $requestDocument->update([
            'status'   => 'noted',
            'noted_at' => now(),
            'noted_by' => auth()->id(),
        ]);

        return back()->with('success', 'Request noted.');
    }

    public function approve(RequestDocument $requestDocument)
    {
        $this->authorizeRole('administrator');

        if (!in_array($requestDocument->status, ['submitted', 'noted'], true)) {
            return back()->withErrors(['status' => 'Only SUBMITTED or NOTED requests can be approved.']);
        }

        $requestDocument->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Request approved.');
    }

    /**
     * ? Reject with per-line remarks (payment/liquidation),
     * and keep noted_at/noted_by so after revision it can return to NOTED automatically.
     */
    public function reject(Request $request, RequestDocument $requestDocument)
    {
        $this->authorizeRole('administrator');

        if (!in_array($requestDocument->status, ['submitted', 'noted'], true)) {
            return back()->withErrors(['status' => 'Only SUBMITTED or NOTED requests can be rejected.']);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:2000',
            'line_remarks' => 'nullable|array',
            'line_remarks.*' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($requestDocument, $validated) {

            $requestDocument->update([
                'status'           => 'rejected',
                'rejected_at'      => now(),
                'rejected_by'      => auth()->id(),
                'rejection_reason' => $validated['reason'] ?? 'Rejected by administrator',
            ]);

            $lineRemarks = $validated['line_remarks'] ?? [];
            foreach ($lineRemarks as $itemId => $remark) {
                $requestDocument->items()
                    ->where('id', $itemId)
                    ->update(['rejection_remark' => $remark]);
            }
        });

        return back()->with('success', 'Request rejected with remarks.');
    }

    /**
     * ? After owner updates a rejected request:
     * - If it was previously NOTED, return to NOTED automatically.
     * - If it was never noted, return to SUBMITTED.
     * Clears rejected/approved fields for a clean re-review.
     *
     * Applies to ALL forms.
     */
    private function restoreStatusAfterRejectedUpdate(RequestDocument $doc): void
    {
        if ($doc->status !== 'rejected') return;

        $backToNoted = !is_null($doc->noted_at) && !is_null($doc->noted_by);

        $doc->update([
            'status'       => $backToNoted ? 'noted' : 'submitted',
            'submitted_at' => $doc->submitted_at ?? now(),

            // clear approval + rejection
            'approved_at'  => null,
            'approved_by'  => null,

            'rejected_at'      => null,
            'rejected_by'      => null,
            'rejection_reason' => null,
        ]);

        // Clear item remarks after revision (payment/liquidation only)
        $doc->items()->update(['rejection_remark' => null]);
    }

   public function print(RequestDocument $requestDocument)
{
    $this->authorizeOwnerOrApprover($requestDocument);

    // ? Business trip can print when NOTED (no approval anymore)
    if ($requestDocument->type === 'business_trip') {
        if (!in_array($requestDocument->status, ['noted', 'approved'], true)) {
            abort(403, 'You can only print a NOTED business trip.');
        }
    } else {
        // ? Other forms still need APPROVED
        if ($requestDocument->status !== 'approved') {
            abort(403, 'You can only print an APPROVED request.');
        }
    }

    $requestDocument->load([
        'items',
        'liquidationDetail',
        'paymentDetail',
        'purposes',
        'user',
        'businessTripDetail',
        'revolvingFundDetail',
    ]);

    $view = match ($requestDocument->type) {
        'liquidation'    => 'requests.print_liquidation',
        'payment'        => 'requests.print_payment',
        'business_trip'  => 'requests.print_business_trip',
        'revolving_fund' => 'requests.print_revolving_fund',
        default          => abort(404),
    };

    return view($view, ['doc' => $requestDocument]);
}

    public function pdf(RequestDocument $requestDocument)
{
    $this->authorizeOwnerOrApprover($requestDocument);

    // ? Business trip can PDF when NOTED (no approval anymore)
    if ($requestDocument->type === 'business_trip') {
        if (!in_array($requestDocument->status, ['noted', 'approved'], true)) {
            abort(403, 'You can only download PDF for a NOTED business trip.');
        }
    } else {
        // ? Other forms still need APPROVED
        if ($requestDocument->status !== 'approved') {
            abort(403, 'You can only download PDF for an APPROVED request.');
        }
    }

    $requestDocument->load([
        'items',
        'liquidationDetail',
        'paymentDetail',
        'purposes',
        'user',
        'businessTripDetail',
        'revolvingFundDetail',
    ]);

    $view = match ($requestDocument->type) {
        'liquidation'    => 'requests.print_liquidation',
        'payment'        => 'requests.print_payment',
        'business_trip'  => 'requests.print_business_trip',
        'revolving_fund' => 'requests.print_revolving_fund',
        default          => abort(404),
    };

    return Pdf::loadView($view, ['doc' => $requestDocument])
        ->setPaper('a4', 'portrait')
        ->download($requestDocument->request_no . '.pdf');
}

    private function generateRequestNo(): string
    {
        $year = now()->format('Y');
        $last = RequestDocument::whereYear('created_at', $year)->max('id');
        $next = ($last ?? 0) + 1;

        return "REQ-{$year}-" . str_pad((string)$next, 6, '0', STR_PAD_LEFT);
    }

    private function authorizeOwner(RequestDocument $doc): void
    {
        if ($doc->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }

    private function authorizeOwnerOrApprover(RequestDocument $doc): void
    {
        if ($doc->user_id === auth()->id()) return;

        $role = auth()->user()->role ?? null;

        if (!in_array($role, ['administrator', 'unitadmin'], true)) {
            abort(403, 'Unauthorized');
        }
    }

    private function authorizeRole(string $role): void
    {
        $userRole = auth()->user()->role ?? null;

        if ($userRole !== $role) {
            abort(403, 'Unauthorized');
        }
    }

    private function isApprover($user): bool
    {
        return in_array(($user->role ?? null), ['administrator', 'unitadmin'], true);
    }
}
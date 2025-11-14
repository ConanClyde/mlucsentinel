<?php

namespace App\Http\Controllers\Admin;

use App\Events\PaymentUpdated;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\StickerRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AuditLogService;
use App\Services\CacheInvalidationService;
use App\Services\PaymentBatchService;
use App\Services\PaymentReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use ZipArchive;

class StickersController extends Controller
{
    /**
     * Show the stickers page.
     */
    public function index(Request $request): View
    {
        if (! auth()->user()->hasPrivilege('view_stickers')) {
            abort(403, 'You do not have permission to view stickers.');
        }
        $statusFilter = $request->get('status', 'paid');
        $pendingPaymentsCount = Cache::remember('payments.pending.count', 30, function () {
            return Payment::batchRepresentative()
                ->where('status', 'pending')
                ->count();
        });

        // Load first page of payments for instant display (default 10 items)
        $payments = Payment::batchRepresentative()
            ->with(['user', 'vehicle.type'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        // Load first page of transactions for instant display (default 10 items)
        $transactions = Payment::batchRepresentative()
            ->with(['user', 'vehicle.type'])
            ->whereIn('status', ['paid', 'cancelled'])
            ->latest()
            ->paginate(10);

        // Load recent sticker requests from users
        $stickerRequests = StickerRequest::with(['user', 'vehicle.vehicleType'])
            ->latest()
            ->paginate(10);

        return view('admin.stickers', [
            'pageTitle' => 'Stickers Management',
            'payments' => $payments,
            'transactions' => $transactions,
            'stickerRequests' => $stickerRequests,
            'statusFilter' => $statusFilter,
            'pendingPaymentsCount' => $pendingPaymentsCount,
        ]);
    }

    /**
     * Get sticker requests data for AJAX requests with search and filter
     */
    public function getRequestsData(Request $request)
    {
        if (! auth()->user()->hasPrivilege('view_stickers')) {
            abort(403, 'You do not have permission to view stickers.');
        }

        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $page = $request->get('page', 1);

        $query = StickerRequest::with(['user', 'vehicle.vehicleType']);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('plate_no', 'like', "%{$search}%")
                            ->orWhereHas('vehicleType', function ($typeQuery) use ($search) {
                                $typeQuery->where('name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $requests->items(),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
                'links' => $requests->links()->render(),
            ],
        ]);
    }

    /**
     * Approve a sticker request
     */
    public function approveRequest(Request $request, $id)
    {
        if (! auth()->user()->hasPrivilege('edit_stickers')) {
            abort(403, 'You do not have permission to approve requests.');
        }

        $stickerRequest = StickerRequest::with(['user', 'vehicle.vehicleType'])->findOrFail($id);
        $oldStatus = $stickerRequest->status;

        $stickerRequest->update([
            'status' => 'approved',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
            'admin_notes' => $request->get('notes', 'Approved by admin'),
        ]);

        // Refresh relationships after update
        $stickerRequest->load(['user', 'vehicle.vehicleType']);

        // Send notification to user
        $stickerRequest->user->notify(new \App\Notifications\StickerRequestStatusNotification(
            $stickerRequest,
            $oldStatus,
            'approved'
        ));

        // Broadcast real-time update
        broadcast(new \App\Events\StickerRequestUpdated($stickerRequest))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Request approved successfully',
            'request' => $stickerRequest->load(['user', 'vehicle.vehicleType']),
        ]);
    }

    /**
     * Reject a sticker request
     */
    public function rejectRequest(Request $request, $id)
    {
        if (! auth()->user()->hasPrivilege('edit_stickers')) {
            abort(403, 'You do not have permission to reject requests.');
        }

        $stickerRequest = StickerRequest::with(['user', 'vehicle.vehicleType'])->findOrFail($id);
        $oldStatus = $stickerRequest->status;

        $stickerRequest->update([
            'status' => 'rejected',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
            'admin_notes' => $request->get('notes', 'Rejected by admin'),
        ]);

        // Refresh relationships after update
        $stickerRequest->load(['user', 'vehicle.vehicleType']);

        // Send notification to user
        $stickerRequest->user->notify(new \App\Notifications\StickerRequestStatusNotification(
            $stickerRequest,
            $oldStatus,
            'rejected'
        ));

        // Broadcast real-time update
        broadcast(new \App\Events\StickerRequestUpdated($stickerRequest))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Request rejected successfully',
            'request' => $stickerRequest->load(['user', 'vehicle.vehicleType']),
        ]);
    }

    /**
     * Show a specific sticker request
     */
    public function showRequest($id)
    {
        if (! auth()->user()->hasPrivilege('view_stickers')) {
            abort(403, 'You do not have permission to view requests.');
        }

        $stickerRequest = StickerRequest::with(['user', 'vehicle.vehicleType', 'processedBy'])
            ->findOrFail($id);

        return response()->json($stickerRequest);
    }

    /**
     * Get payments data for AJAX requests
     */
    public function data(Request $request)
    {
        if (! auth()->user()->hasPrivilege('view_stickers')) {
            abort(403, 'You do not have permission to view stickers.');
        }
        $tab = $request->get('tab', 'payment');
        $search = $request->get('search', '');
        $statusFilter = $request->get('status', 'paid');
        $perPage = max(1, (int) $request->get('per_page', 10));
        $page = max(1, (int) $request->get('page', 1));

        $query = Payment::with(['user', 'vehicle.type'])
            ->batchRepresentative();

        if ($tab === 'payment') {
            $query->where('status', 'pending');
        } elseif ($tab === 'transactions') {
            if ($statusFilter === 'all') {
                $query->whereIn('status', ['paid', 'failed', 'cancelled']);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('plate_no', 'like', "%{$search}%")
                            ->orWhere('color', 'like', "%{$search}%")
                            ->orWhere('number', 'like', "%{$search}%");
                    });
            });
        }

        // Advanced filters
        if ($request->filled('from_date')) {
            $column = $tab === 'transactions' ? 'paid_at' : 'created_at';
            $query->whereDate($column, '>=', $request->get('from_date'));
        }

        if ($request->filled('to_date')) {
            $column = $tab === 'transactions' ? 'paid_at' : 'created_at';
            $query->whereDate($column, '<=', $request->get('to_date'));
        }

        if ($request->filled('vehicle_type_id')) {
            $vt = (int) $request->get('vehicle_type_id');
            $query->whereHas('vehicle', function ($q) use ($vt) {
                $q->where('type_id', $vt);
            });
        }

        if ($request->filled('user_type') && $request->get('user_type') !== 'all') {
            $ut = $request->get('user_type');
            $query->whereHas('user', function ($q) use ($ut) {
                $q->where('user_type', $ut);
            });
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->get('batch_id'));
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', (float) $request->get('min_amount'));
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', (float) $request->get('max_amount'));
        }

        $paginator = $query->orderByDesc($tab === 'transactions' ? 'paid_at' : 'created_at')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items());

        // Preload batch vehicles for all batch_ids to avoid N+1
        $batchVehicles = collect();
        $batchIds = $items->pluck('batch_id')->filter()->unique();
        if ($batchIds->isNotEmpty()) {
            $batchVehicles = Payment::whereIn('batch_id', $batchIds)
                ->whereHas('vehicle')
                ->with('vehicle.type')
                ->get()
                ->groupBy('batch_id');
        }

        $fee = \App\Models\Fee::getAmount('sticker_fee', 15.00);
        $data = $items->map(function ($payment) use ($batchVehicles, $fee) {
            $batchList = [];
            if ($payment->batch_id && $batchVehicles->has($payment->batch_id)) {
                $batchList = $batchVehicles[$payment->batch_id]->filter(fn ($p) => $p->vehicle !== null)
                    ->map(function ($p) {
                        return [
                            'id' => $p->vehicle->id,
                            'plate_no' => $p->vehicle->plate_no,
                            'color' => $p->vehicle->color,
                            'number' => $p->vehicle->number,
                            'sticker' => $p->vehicle->sticker,
                            'type_name' => $p->vehicle->type ? $p->vehicle->type->name : null,
                            'type' => $p->vehicle->type ? [
                                'id' => $p->vehicle->type->id,
                                'name' => $p->vehicle->type->name,
                            ] : null,
                        ];
                    })->values();
            }

            $computedAmount = ($payment->status === 'pending' && $payment->type === 'sticker_fee')
                ? number_format(($payment->vehicle_count ?: 1) * $fee, 2, '.', '')
                : $payment->amount;

            return [
                'id' => $payment->id,
                'user_id' => $payment->user_id,
                'vehicle_id' => $payment->vehicle_id,
                'type' => $payment->type,
                'status' => $payment->status,
                'amount' => $computedAmount,
                'reference' => $payment->reference,
                'batch_id' => $payment->batch_id,
                'vehicle_count' => $payment->vehicle_count,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
                'user' => $payment->user ? [
                    'id' => $payment->user->id,
                    'first_name' => $payment->user->first_name,
                    'last_name' => $payment->user->last_name,
                    'email' => $payment->user->email,
                    'user_type' => $payment->user->user_type,
                ] : null,
                'vehicle' => $payment->vehicle ? [
                    'id' => $payment->vehicle->id,
                    'plate_no' => $payment->vehicle->plate_no,
                    'color' => $payment->vehicle->color,
                    'number' => $payment->vehicle->number,
                    'sticker' => $payment->vehicle->sticker,
                    'type' => $payment->vehicle->type ? [
                        'id' => $payment->vehicle->type->id,
                        'name' => $payment->vehicle->type->name,
                    ] : null,
                ] : null,
                'batch_vehicles' => $batchList,
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Search users for sticker request
     */
    public function searchUsers(Request $request)
    {
        $search = $request->get('search', '');

        $users = User::whereHas('vehicles')
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->with(['vehicles' => function ($query) {
                $query->with('type');
            }])
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'vehicles' => $user->vehicles->map(function ($vehicle) {
                        return [
                            'id' => $vehicle->id,
                            'plate_no' => $vehicle->plate_no,
                            'color' => $vehicle->color,
                            'number' => $vehicle->number,
                            'type_name' => $vehicle->type ? $vehicle->type->name : null,
                            'sticker_image' => $vehicle->sticker,
                            'type' => $vehicle->type ? [
                                'id' => $vehicle->type->id,
                                'name' => $vehicle->type->name,
                            ] : null,
                        ];
                    }),
                ];
            });

        return response()->json($users);
    }

    /**
     * Create a sticker request
     */
    public function createRequest(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'vehicle_ids' => 'required|array|min:1',
            'vehicle_ids.*' => 'required|exists:vehicles,id',
        ]);

        // Idempotency: if key already used, treat as duplicate and return success
        $key = $request->header('Idempotency-Key');
        if ($key) {
            $routeName = $request->route() ? $request->route()->getName() : 'admin.stickers.request';
            $ok = app(\App\Services\IdempotencyService::class)->ensure(
                $key,
                auth()->id(),
                $routeName,
                ['user_id' => $validated['user_id'], 'vehicle_ids' => $validated['vehicle_ids']]
            );
            if (! $ok) {
                return response()->json([
                    'success' => true,
                    'message' => 'Duplicate request ignored.',
                ], 200);
            }
        }

        $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
        $payment = app(PaymentBatchService::class)
            ->addVehiclesToBatch((int) $validated['user_id'], array_map('intval', $validated['vehicle_ids']), $editorName);

        return response()->json([
            'success' => true,
            'message' => $payment->vehicle_count > 1
                ? $payment->vehicle_count.' sticker requests created successfully!'
                : 'Sticker request created successfully!',
            'payment' => $payment,
        ]);
    }

    /**
     * Mark payment as paid
     */
    public function markAsPaid(Request $request, Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            // Update main payment
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Update all payments in the same batch
            if ($payment->batch_id) {
                Payment::where('batch_id', $payment->batch_id)
                    ->where('id', '!=', $payment->id)
                    ->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
            }

            // Generate receipt
            $receiptService = new PaymentReceiptService;
            $receiptPath = $payment->batch_id
                ? $receiptService->generateBatchReceipt($payment)
                : $receiptService->generateReceipt($payment);

            // Log payment confirmation
            \Log::channel('payments')->info('Payment confirmed', [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'vehicle_count' => $payment->vehicle_count,
                'batch_id' => $payment->batch_id,
                'confirmed_by' => auth()->id(),
                'confirmed_by_name' => auth()->user()->first_name.' '.auth()->user()->last_name,
            ]);

            // Audit log payment confirmation
            AuditLogService::log('payment_confirmed', $payment, ['status' => 'pending'], ['status' => 'paid', 'paid_at' => now()]);

            // Clear analytics cache as revenue calculations depend on payments
            CacheInvalidationService::clearAllAnalyticsCache();

            // Dispatch email job to send receipt to user
            \App\Jobs\SendPaymentReceiptEmail::dispatch($payment, $receiptPath);

            $payment->load(['user', 'vehicle.type', 'batchVehicles']);
            broadcast(new PaymentUpdated($payment, 'updated', auth()->user()->first_name.' '.auth()->user()->last_name));
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as paid!',
        ]);
    }

    /**
     * Cancel payment request
     */
    public function cancel(Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            // Log payment cancellation
            \Log::channel('payments')->warning('Payment cancelled', [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'vehicle_count' => $payment->vehicle_count,
                'batch_id' => $payment->batch_id,
                'cancelled_by' => auth()->id(),
                'cancelled_by_name' => auth()->user()->first_name.' '.auth()->user()->last_name,
            ]);

            // Update main payment to cancelled status
            $payment->update([
                'status' => 'cancelled',
                'paid_at' => now(), // Set timestamp for when it was cancelled
            ]);

            // Update all payments in the same batch
            if ($payment->batch_id) {
                Payment::where('batch_id', $payment->batch_id)
                    ->where('id', '!=', $payment->id)
                    ->update([
                        'status' => 'cancelled',
                        'paid_at' => now(),
                    ]);
            }

            // Broadcast payment update
            $payment->load(['user', 'vehicle.type', 'batchVehicles']);
            broadcast(new PaymentUpdated($payment, 'updated', auth()->user()->first_name.' '.auth()->user()->last_name));
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment request cancelled successfully!',
        ]);
    }

    /**
     * Delete payment request (physical deletion)
     */
    public function destroy(Payment $payment)
    {
        // Broadcast payment deletion BEFORE deleting
        $payment->load(['user', 'vehicle.type', 'batchVehicles']);
        broadcast(new PaymentUpdated($payment, 'deleted', auth()->user()->first_name.' '.auth()->user()->last_name));

        DB::transaction(function () use ($payment) {
            // Force delete (permanent) all payments in the same batch if exists
            if ($payment->batch_id) {
                Payment::where('batch_id', $payment->batch_id)->forceDelete();
            } else {
                $payment->forceDelete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment request deleted successfully!',
        ]);
    }

    /**
     * Download payment receipt
     */
    public function downloadReceipt(Payment $payment)
    {
        $receiptService = new PaymentReceiptService;

        // Check if receipt exists, generate if not
        if (! $receiptService->receiptExists($payment)) {
            if ($payment->batch_id) {
                $receiptService->generateBatchReceipt($payment);
            } else {
                $receiptService->generateReceipt($payment);
            }
        }

        $receiptUrl = $receiptService->getReceiptUrl($payment);

        return redirect($receiptUrl);
    }

    /**
     * Get issued stickers with filters
     */
    public function getIssuedStickers(Request $request)
    {
        if (! auth()->user()->hasPrivilege('view_stickers')) {
            abort(403, 'You do not have permission to view stickers.');
        }
        $perPage = max(1, (int) $request->get('per_page', 24));
        $page = max(1, (int) $request->get('page', 1));

        $query = Vehicle::with(['user', 'type'])
            ->whereNotNull('sticker');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plate_no', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $paginator = $query->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = collect($paginator->items())->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'plate_no' => $vehicle->plate_no,
                'color' => $vehicle->color,
                'number' => $vehicle->number,
                'sticker' => $vehicle->sticker,
                'created_at' => $vehicle->created_at,
                'owner_name' => $vehicle->user ? $vehicle->user->first_name.' '.$vehicle->user->last_name : 'N/A',
                'vehicle_type' => $vehicle->type ? $vehicle->type->name : 'N/A',
                'user' => $vehicle->user ? [
                    'id' => $vehicle->user->id,
                    'first_name' => $vehicle->user->first_name,
                    'last_name' => $vehicle->user->last_name,
                ] : null,
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Download single sticker
     */
    public function downloadSticker(Vehicle $vehicle)
    {
        if (! auth()->user()->hasPrivilege('download_stickers')) {
            abort(403, 'You do not have permission to download stickers.');
        }
        if (! $vehicle->sticker) {
            return response()->json(['error' => 'No sticker available for this vehicle'], 404);
        }

        $stickerPath = public_path($vehicle->sticker);

        if (! file_exists($stickerPath)) {
            return response()->json(['error' => 'Sticker file not found'], 404);
        }

        $ownerName = $vehicle->user ? $vehicle->user->first_name.'_'.$vehicle->user->last_name : 'Unknown';
        $plateNo = $vehicle->plate_no ? str_replace('-', '_', $vehicle->plate_no) : $vehicle->color.'_'.$vehicle->number;
        $filename = "sticker_{$ownerName}_{$plateNo}.svg";

        return response()->download($stickerPath, $filename);
    }

    /**
     * Download all filtered stickers as ZIP
     */
    public function downloadFilteredStickers(Request $request)
    {
        if (! auth()->user()->hasPrivilege('download_stickers')) {
            abort(403, 'You do not have permission to download stickers.');
        }
        $query = Vehicle::with(['user', 'type'])
            ->whereNotNull('sticker');

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plate_no', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $vehicles = $query->orderBy('created_at', 'desc')->get();

        if ($vehicles->isEmpty()) {
            return response()->json(['error' => 'No stickers found with the current filters'], 404);
        }

        // Create temporary ZIP file
        $zipFileName = 'stickers_'.date('Y-m-d_His').'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        // Ensure temp directory exists
        if (! file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($vehicles as $vehicle) {
                $stickerPath = public_path($vehicle->sticker);

                if (file_exists($stickerPath)) {
                    $ownerName = $vehicle->user ? $vehicle->user->first_name.'_'.$vehicle->user->last_name : 'Unknown';
                    $plateNo = $vehicle->plate_no ? str_replace('-', '_', $vehicle->plate_no) : $vehicle->color.'_'.$vehicle->number;
                    $filename = "sticker_{$ownerName}_{$plateNo}.svg";

                    $zip->addFile($stickerPath, $filename);
                }
            }

            $zip->close();
        }

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}

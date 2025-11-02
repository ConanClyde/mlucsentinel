<?php

namespace App\Http\Controllers\Admin;

use App\Events\PaymentUpdated;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\PaymentReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class StickersController extends Controller
{
    /**
     * Show the stickers page.
     */
    public function index(Request $request)
    {
        // Get pending payments
        $payments = Payment::with(['user', 'vehicle.type', 'batchVehicles'])
            ->batchRepresentative()
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get transactions with default filter (paid)
        $statusFilter = $request->get('status', 'paid');
        $transactionsQuery = Payment::with(['user', 'vehicle.type', 'batchVehicles'])
            ->batchRepresentative();

        if ($statusFilter === 'all') {
            $transactionsQuery->whereIn('status', ['paid', 'failed', 'cancelled']);
        } else {
            $transactionsQuery->where('status', $statusFilter);
        }

        $transactions = $transactionsQuery->orderBy('paid_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.stickers', [
            'pageTitle' => 'Stickers Management',
            'payments' => $payments,
            'transactions' => $transactions,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Get payments data for AJAX requests
     */
    public function data(Request $request)
    {
        $tab = $request->get('tab', 'payment');
        $search = $request->get('search', '');
        $statusFilter = $request->get('status', 'paid'); // Default to 'paid'

        $query = Payment::with(['user', 'vehicle.type', 'batchVehicles'])
            ->batchRepresentative(); // Only get one payment per batch

        // Filter by tab
        if ($tab === 'payment') {
            $query->where('status', 'pending');
        } elseif ($tab === 'transactions') {
            // Apply status filter for transactions
            if ($statusFilter === 'all') {
                $query->whereIn('status', ['paid', 'failed', 'cancelled']);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        // Apply search filter
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

        $payments = $query->latest()->get()->map(function ($payment) {
            $batchVehicles = [];
            if ($payment->batch_id) {
                $batchVehicles = Payment::where('batch_id', $payment->batch_id)
                    ->with('vehicle.type')
                    ->get()
                    ->map(function ($p) {
                        return [
                            'id' => $p->vehicle->id,
                            'plate_no' => $p->vehicle->plate_no,
                            'color' => $p->vehicle->color,
                            'number' => $p->vehicle->number,
                            'sticker' => $p->vehicle->sticker,
                            'type_name' => $p->vehicle->type ? $p->vehicle->type->name : null,
                        ];
                    });
            }

            return [
                'id' => $payment->id,
                'user_id' => $payment->user_id,
                'vehicle_id' => $payment->vehicle_id,
                'type' => $payment->type,
                'status' => $payment->status,
                'amount' => $payment->amount,
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
                'batch_vehicles' => $batchVehicles,
            ];
        });

        return response()->json($payments);
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

        $payment = DB::transaction(function () use ($validated) {
            $batchId = 'BATCH-'.strtoupper(uniqid());
            $vehicleCount = count($validated['vehicle_ids']);
            $totalAmount = $vehicleCount * 15.00;

            // Create main payment record
            $payment = Payment::create([
                'user_id' => $validated['user_id'],
                'vehicle_id' => $validated['vehicle_ids'][0], // First vehicle as representative
                'type' => 'sticker_fee',
                'status' => 'pending',
                'amount' => $totalAmount,
                'reference' => 'STK-'.strtoupper(uniqid()),
                'batch_id' => $vehicleCount > 1 ? $batchId : null,
                'vehicle_count' => $vehicleCount,
            ]);

            // Create child payment records for other vehicles (if multiple)
            if ($vehicleCount > 1) {
                for ($i = 1; $i < $vehicleCount; $i++) {
                    Payment::create([
                        'user_id' => $validated['user_id'],
                        'vehicle_id' => $validated['vehicle_ids'][$i],
                        'type' => 'sticker_fee',
                        'status' => 'pending',
                        'amount' => 15.00,
                        'reference' => 'STK-'.strtoupper(uniqid()),
                        'batch_id' => $batchId,
                        'vehicle_count' => 1,
                    ]);
                }
            }

            $payment->load(['user', 'vehicle.type', 'batchVehicles']);
            broadcast(new PaymentUpdated($payment, 'created', auth()->user()->first_name.' '.auth()->user()->last_name));

            return $payment;
        });

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
            if ($payment->batch_id) {
                $receiptService->generateBatchReceipt($payment);
            } else {
                $receiptService->generateReceipt($payment);
            }

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

        $stickers = $query->orderBy('created_at', 'desc')->get()->map(function ($vehicle) {
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
        });

        return response()->json($stickers);
    }

    /**
     * Download single sticker
     */
    public function downloadSticker(Vehicle $vehicle)
    {
        if (! $vehicle->sticker) {
            return response()->json(['error' => 'No sticker available for this vehicle'], 404);
        }

        $stickerPath = public_path($vehicle->sticker);

        if (! file_exists($stickerPath)) {
            return response()->json(['error' => 'Sticker file not found'], 404);
        }

        $ownerName = $vehicle->user ? $vehicle->user->first_name.'_'.$vehicle->user->last_name : 'Unknown';
        $plateNo = $vehicle->plate_no ? str_replace('-', '_', $vehicle->plate_no) : $vehicle->color.'_'.$vehicle->number;
        $filename = "sticker_{$ownerName}_{$plateNo}.png";

        return response()->download($stickerPath, $filename);
    }

    /**
     * Download all filtered stickers as ZIP
     */
    public function downloadFilteredStickers(Request $request)
    {
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
                    $filename = "sticker_{$ownerName}_{$plateNo}.png";

                    $zip->addFile($stickerPath, $filename);
                }
            }

            $zip->close();
        }

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}

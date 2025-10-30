<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard Export - {{ $exportDate }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        h1 {
            color: #1b1b18;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 10px;
        }
        h2 {
            color: #1b1b18;
            margin-top: 30px;
            border-bottom: 2px solid #e3e3e0;
            padding-bottom: 5px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            border: 1px solid #e3e3e0;
            padding: 15px;
            border-radius: 8px;
            background: #f9fafb;
        }
        .stat-label {
            font-size: 14px;
            color: #706f6c;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #1b1b18;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #3b82f6;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e3e3e0;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e3e3e0;
            text-align: center;
            color: #706f6c;
            font-size: 12px;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <h1>MLUC Sentinel - Dashboard Report</h1>
    <p style="color: #706f6c; margin-bottom: 30px;">Generated on: {{ $exportDate }}</p>

    <h2>Dashboard Statistics</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Vehicles</div>
            <div class="stat-value">{{ number_format($stats['total_vehicles']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Reports</div>
            <div class="stat-value">{{ number_format($stats['pending_reports']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Reports</div>
            <div class="stat-value">{{ number_format($stats['total_reports']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₱{{ number_format($stats['total_revenue'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Payments</div>
            <div class="stat-value">{{ number_format($stats['total_payments']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Paid Payments</div>
            <div class="stat-value">{{ number_format($stats['paid_payments']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Payments</div>
            <div class="stat-value">{{ number_format($stats['pending_payments']) }}</div>
        </div>
    </div>

    <h2>Violation Reports</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Violation Type</th>
                <th>Violator</th>
                <th>Reporter</th>
                <th>Vehicle</th>
                <th>Location</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $report)
                @php
                    $violatorName = $report->violatorVehicle?->user 
                        ? "{$report->violatorVehicle->user->first_name} {$report->violatorVehicle->user->last_name}"
                        : 'Unknown';
                    
                    $reporterName = $report->reportedBy
                        ? "{$report->reportedBy->first_name} {$report->reportedBy->last_name}"
                        : 'Unknown';

                    $vehicle = $report->violatorVehicle?->plate_no ?? $report->violator_sticker_number ?? 'N/A';
                    
                    $statusClass = 'status-' . $report->status;
                @endphp
                <tr>
                    <td>{{ $report->id }}</td>
                    <td>{{ $report->violationType->name ?? 'N/A' }}</td>
                    <td>{{ $violatorName }}</td>
                    <td>{{ $reporterName }}</td>
                    <td>{{ $vehicle }}</td>
                    <td>{{ $report->location }}</td>
                    <td><span class="status-badge {{ $statusClass }}">{{ ucfirst($report->status) }}</span></td>
                    <td>{{ $report->reported_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>MLUC Sentinel - Parking Violation Management System</p>
        <p>This is a system-generated report. For inquiries, contact the administrator.</p>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\Shift;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QRController extends Controller
{
    /**
     * Show QR codes list
     */
    public function index(Request $request)
    {
        $query = QrCode::with('shift');

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->input('search')}%");
        }

        if ($request->has('shift_id')) {
            $query->where('shift_id', $request->input('shift_id'));
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $qrCodes = $query->paginate(15);
        $shifts = Shift::all();

        return view('admin.qr.index', [
            'qrCodes' => $qrCodes,
            'shifts' => $shifts,
        ]);
    }

    /**
     * Show create QR form
     */
    public function create()
    {
        $shifts = Shift::all();

        return view('admin.qr.create', [
            'shifts' => $shifts,
        ]);
    }

    /**
     * Store new QR code
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'sometimes|integer|min:10|max:1000',
        ]);

        // Get shift to auto-set active times
        $shift = Shift::findOrFail($validated['shift_id']);

        // Generate unique QR code
        $qrCode = Str::random(32);
        while (QrCode::where('code', $qrCode)->exists()) {
            $qrCode = Str::random(32);
        }

        $validated['code'] = $qrCode;
        $validated['radius_meters'] = $validated['radius_meters'] ?? 100;
        
        // Auto-set active times from shift
        $validated['active_from'] = $shift->start_time;
        $validated['active_until'] = $shift->end_time;
        $validated['is_active'] = true;

        $qr = QrCode::create($validated);

        return redirect()->route('admin.qr.show', $qr->id)
            ->with('success', 'QR Code berhasil dibuat')
            ->with('qrCode', $qrCode);
    }

    /**
     * Show QR detail
     */
    public function show(int $id)
    {
        $qr = QrCode::with('shift')->findOrFail($id);

        // Get statistics
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $stats = AttendanceRecord::where('qr_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as total_scans')
            ->selectRaw('SUM(CASE WHEN is_late THEN 1 ELSE 0 END) as late_count')
            ->first();

        $recentScans = AttendanceRecord::where('qr_id', $id)
            ->with(['employee', 'shift'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.qr.show', [
            'qr' => $qr,
            'stats' => $stats,
            'recentScans' => $recentScans,
        ]);
    }

    /**
     * Show edit QR form
     */
    public function edit(int $id)
    {
        $qr = QrCode::findOrFail($id);
        $shifts = Shift::all();

        return view('admin.qr.edit', [
            'qr' => $qr,
            'shifts' => $shifts,
        ]);
    }

    /**
     * Update QR code
     */
    public function update(Request $request, int $id)
    {
        $qr = QrCode::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'radius_meters' => 'sometimes|integer|min:10|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        $qr->update($validated);

        return redirect()->route('admin.qr.show', $qr->id)
            ->with('success', 'QR Code berhasil diupdate');
    }

    /**
     * Delete QR code
     */
    public function destroy(int $id)
    {
        $qr = QrCode::findOrFail($id);
        $qr->delete();

        return redirect()->route('admin.qr.index')
            ->with('success', 'QR Code berhasil dihapus');
    }

    /**
     * Show QR statistics
     */
    public function statistics(Request $request, int $id)
    {
        $qr = QrCode::findOrFail($id);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $records = AttendanceRecord::where('qr_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['employee', 'shift'])
            ->paginate(20);

        $totalScans = AttendanceRecord::where('qr_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        $onTimeCount = AttendanceRecord::where('qr_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_late', false)
            ->count();

        $lateCount = AttendanceRecord::where('qr_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_late', true)
            ->count();

        return view('admin.qr.statistics', [
            'qr' => $qr,
            'records' => $records,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalScans' => $totalScans,
            'onTimeCount' => $onTimeCount,
            'lateCount' => $lateCount,
        ]);
    }

    /**
     * Download QR code as PDF
     */
    public function download(int $id)
    {
        $qr = QrCode::findOrFail($id);

        // Generate QR code as SVG and convert to base64 data URL
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(400)->generate($qr->code);
        $qrDataUrl = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        
        // Prepare data
        $printDate = now()->format('d M Y H:i');
        $qrName = $qr->name;
        $qrCode = $qr->code;
        $latitude = $qr->latitude;
        $longitude = $qr->longitude;
        $radius = $qr->radius_meters;
        $shiftName = $qr->shift->name;
        $startTime = $qr->shift->start_time;
        $endTime = $qr->shift->end_time;

        // Create HTML content for PDF
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>QR Code - {$qrName}</title>
    <style>
        body {
            margin: 20px;
            padding: 0;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .qr-container {
            border: 2px solid #333;
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
        }
        .qr-image {
            margin: 30px 0;
            background: white;
            padding: 20px;
        }
        .qr-image img {
            max-width: 300px;
            height: auto;
        }
        h2 {
            margin-top: 0;
            color: #333;
            font-size: 24px;
        }
        .info {
            font-size: 13px;
            color: #666;
            margin-top: 20px;
            text-align: left;
        }
        .info p {
            margin: 8px 0;
        }
        .info strong {
            color: #333;
        }
        .footer {
            margin-top: 30px;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <h2>{$qrName}</h2>
        <div class="qr-image">
            <img src="{$qrDataUrl}" alt="QR Code" />
        </div>
        <div class="info">
            <p><strong>Kode QR:</strong> {$qrCode}</p>
            <p><strong>Lokasi:</strong> {$latitude}, {$longitude}</p>
            <p><strong>Radius GPS:</strong> {$radius} meter</p>
            <p><strong>Shift:</strong> {$shiftName}</p>
            <p><strong>Jam Kerja:</strong> {$startTime} - {$endTime}</p>
        </div>
        <div class="footer">
            <p>Dicetak pada: {$printDate}</p>
            <p>Sistem Absen Kafe - SIAREA</p>
        </div>
    </div>
</body>
</html>
HTML;

        // Generate PDF from HTML
        $pdf = \PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        // Return as downloadable PDF
        return $pdf->download('QR_' . str_replace(' ', '_', $qr->name) . '_' . now()->format('Y-m-d') . '.pdf');
    }
}

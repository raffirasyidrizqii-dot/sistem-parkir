<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\TiketParkir;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    public function index()
    {
        try {
            $data = Transaksi::orderBy('id', 'desc')->get();
            return response()->json([
                'status' => true,
                'data'   => $data
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $totalBayar = (float) ($request->total_bayar ?? 0);
            $uangBayar  = (float) ($request->bayar ?? 0);
            $tipe       = $request->tipe ?? 'non-member';

            $tiket = null;
            if ($request->filled('tiket_id')) {
                $tiket = TiketParkir::find($request->tiket_id);
            }

            if (!$tiket && $request->filled('kode')) {
                $rawKode = trim($request->kode);
                $tiket = TiketParkir::where('kode_tiket', $rawKode)
                    ->orWhere('kode_tiket', 'LIKE', '%' . $rawKode . '%')
                    ->where('status', 'masuk')
                    ->latest('id')
                    ->first();
            }

            $kodeFinal = $tiket ? $tiket->kode_tiket : ($request->kode ?: ('TRX-' . time()));

            if ($tiket) {
                $tiket->status = 'keluar';
                $tiket->waktu_keluar = Carbon::now();
                $tiket->save();
            }

            $kembalian = max(0, $uangBayar - $totalBayar);
            $kategori  = strtolower($request->kategori ?: ($tiket->kategori ?? 'motor'));
            $nopol     = $request->nopol ?: ($tiket->plat_nomor ?? '-');

            $transaksi = Transaksi::create([
                'kode_tiket'    => (string) $kodeFinal,
                'kategori'      => $kategori ?: 'motor',
                'no_plat'       => (string) ($nopol ?: '-'),
                'total_bayar'   => $totalBayar,
                'uang_bayar'    => $uangBayar,
                'kembalian'     => $kembalian,
                'status'        => 'lunas',
                'tanggal_bayar' => Carbon::now()
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Pembayaran berhasil, palang keluar dibuka!',
                'data'    => $transaksi
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal transaksi keluar: ' . $e->getMessage()
            ], 500);
        }
    }
}
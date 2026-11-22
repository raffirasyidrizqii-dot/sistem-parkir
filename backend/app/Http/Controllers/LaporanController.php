<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Member;

class LaporanController extends Controller
{
    public function member(Request $request)
    {
        try {
            // Mengambil semua transaksi keluar yang berasal dari kartu member
            $data = Transaksi::where('kode_tiket', 'LIKE', 'MBR-%')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'data'   => $data
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil laporan member: ' . $e->getMessage()
            ], 500);
        }
    }

    public function nonMember(Request $request)
    {
        try {
            // Mengambil semua transaksi keluar selain member (tiket umum)
            $data = Transaksi::where('kode_tiket', 'NOT LIKE', 'MBR-%')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'data'   => $data
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil laporan non-member: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rekapGlobal(Request $request)
    {
        try {
            $totalPendapatan = Transaksi::sum('total_bayar');
            $data = Transaksi::orderBy('id', 'desc')->get();

            return response()->json([
                'status' => true,
                'data'   => [
                    'total_pendapatan' => (float) $totalPendapatan,
                    'riwayat'          => $data
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil rekap: ' . $e->getMessage()
            ], 500);
        }
    }
}
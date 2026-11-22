<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TiketParkir;
use Carbon\Carbon;

class TiketController extends Controller
{
    public function kendaraanAktif()
    {
        try {
            $data = TiketParkir::where('status', 'masuk')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($item) {
                    $kat = strtolower($item->kategori ?: 'motor');
                    $tipe = str_starts_with($item->kode_tiket, 'MBR-') ? 'Member' : 'Non-Member';

                    return [
                        'id'          => $item->id,
                        'kode_tiket'  => $item->kode_tiket,
                        'tipe'        => $tipe,
                        'kategori'    => $kat,
                        'no_plat'     => $item->plat_nomor ?: '-',
                        'plat_nomor'  => $item->plat_nomor ?: '-',
                        'waktu_masuk' => $item->waktu_masuk ?: $item->created_at,
                        'status'      => $item->status,
                    ];
                });

            return response()->json([
                'status' => true,
                'data'   => $data
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
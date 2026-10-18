<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PeminjamanController extends Controller
{
    public function index()
    {
        $peminjamans = Peminjaman::with('buku')->latest()->paginate(10);
        return view('peminjaman.index', compact('peminjamans'));
    }

    public function create(Request $request)
    {
        $bukus = Buku::orderBy('judul')->get();
        $bukuTerpilih = $request->query('buku_id');
        return view('peminjaman.create', compact('bukus', 'bukuTerpilih'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'buku_id' => 'required|exists:bukus,id',
            'nim_peminjam' => 'required|string|min:6|max:15',
            'nama_peminjam' => 'required|string|min:3|max:150',
            'lama_pinjam' => 'required|integer|between:1,30',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Kunci baris buku agar tidak diubah transaksi lain
                $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->firstOrFail();

                if ($buku->stok < 1) {
                    throw new \RuntimeException("Stok buku \"{$buku->judul}\" habis, peminjaman ditolak.");
                }

                $nim = strtoupper(trim($validated['nim_peminjam']));

                $masihDipinjam = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', $nim)
                    ->where('status', 'dipinjam')
                    ->exists();

                if ($masihDipinjam) {
                    throw new \RuntimeException('NIM ini masih meminjam buku yang sama dan belum mengembalikannya.');
                }

                $buku->decrement('stok');

                Peminjaman::create([
                    'kode_pinjam' => 'PJM-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                    'buku_id' => $buku->id,
                    'nim_peminjam' => $nim,
                    'nama_peminjam' => $validated['nama_peminjam'],
                    'tanggal_pinjam' => now()->toDateString(),
                    'tanggal_jatuh_tempo' => now()->addDays((int) $validated['lama_pinjam'])->toDateString(),
                    'status' => 'dipinjam',
                ]);
            }, 3);

            return redirect()->route('peminjaman.index')
                ->with('success', 'Peminjaman berhasil dicatat, stok buku diperbarui.');
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION] Peminjaman gagal: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan database, peminjaman dibatalkan.');
        }
    }

    public function kembali(Peminjaman $peminjaman)
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                $pinjam = Peminjaman::where('id', $peminjaman->id)->lockForUpdate()->firstOrFail();

                if ($pinjam->status === 'dikembalikan') {
                    throw new \RuntimeException('Buku ini sudah dikembalikan sebelumnya.');
                }

                $buku = Buku::where('id', $pinjam->buku_id)->lockForUpdate()->firstOrFail();

                $pinjam->update([
                    'status' => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                $buku->increment('stok');
            }, 3);

            return redirect()->route('peminjaman.index')
                ->with('success', 'Buku berhasil dikembalikan, stok bertambah.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION] Pengembalian gagal: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan database, pengembalian dibatalkan.');
        }
    }
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.deepseek.key') ?: env('DEEPSEEK_API_KEY', '');
        $this->baseUrl = config('services.deepseek.url') ?: env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1');
    }

    public function chat(string $userMessage, $categoriesList = []): array
    {
        $categoriesString = '';
        foreach ($categoriesList as $cat) {
            $categoriesString .= "- {$cat->name} (tipe: {$cat->type})\n";
        }

        $today = now()->format('Y-m-d');

        $systemPrompt = <<<PROMPT
Anda adalah asisten keuangan pribadi bernama Fintrac.AI. Tugas Anda: mencatat transaksi keuangan pengguna dan memberikan saran finansial yang bijak.

=== KATEGORI YANG TERSEDIA ===
{$categoriesString}
=== ATURAN PEMILIHAN KATEGORI ===
Pilih kategori yang paling logis untuk setiap item:
- Makanan       → makanan & minuman: nasi, bakso, kopi, teh, snack, boba, mie, dll.
- Transportasi  → perjalanan: bensin, ojek, taksi, parkir, tol, bus, kereta, dll.
- Belanja       → barang: pakaian, elektronik, peralatan, kosmetik, dll.
- Hiburan       → rekreasi: nonton, games, streaming, konser, wisata, dll.
- Utilitas      → tagihan: listrik, air, internet, pulsa, gas, sewa, dll.
- Kesehatan     → medis: obat, dokter, vitamin, gym, klinik, dll.
- Pendidikan    → belajar: buku, kursus, sekolah, les, seminar, dll.
- Gaji          → penghasilan tetap dari pekerjaan.
- Freelance     → penghasilan lepas/proyek.
- Investasi     → hasil investasi, dividen, bunga.
- Orang Tua / Keluarga → uang jajan, kiriman orang tua/saudara (jika belum ada kategorinya, buat baru).
- Hutang        → bayar hutang, cicilan non-bank (jika belum ada kategorinya, buat baru).
- Lainnya (Pemasukan)  → pemasukan yang tidak cocok kategori manapun di atas.
- Lainnya (Pengeluaran) → pengeluaran yang tidak cocok kategori manapun di atas.

PENTING: Jika ada item yang tidak cocok dengan kategori yang tersedia, BUAT kategori baru yang sesuai dengan set "is_new_category": true.

=== FORMAT RESPONS (HANYA JSON, TANPA TEKS LAIN) ===

OPSI A — Ada transaksi untuk dicatat (satu atau lebih):
{
  "action": "log_transactions",
  "transactions": [
    {
      "amount": 15000,
      "type": "expense",
      "category": "Transportasi",
      "is_new_category": false,
      "description": "Bensin motor",
      "date": "{$today}"
    },
    {
      "amount": 500000,
      "type": "income",
      "category": "Orang Tua",
      "is_new_category": true,
      "description": "Uang jajan dari mama",
      "date": "{$today}"
    }
  ],
  "response": "Konfirmasi ringkas menyebutkan setiap item yang dicatat beserta kategorinya, termasuk kategori baru yang dibuat."
}

OPSI B — Pengguna meminta HAPUS transaksi yang sudah dicatat sebelumnya:
{
  "action": "delete_transaction",
  "transactions": [],
  "delete_target": {
    "description_hint": "ayam goreng",
    "amount": 17000,
    "type": "expense",
    "date": "{$today}"
  },
  "response": "Pesan konfirmasi bahwa transaksi sedang dihapus."
}

OPSI C — Pertanyaan, saran, sapaan, atau tidak ada transaksi:
{
  "action": "chat",
  "transactions": [],
  "response": "Saran atau jawaban informatif dan bersahabat."
}

=== ATURAN TAMBAHAN ===
- Jika pengguna menyebut BEBERAPA item, PISAHKAN menjadi objek berbeda di array "transactions".
- Nilai "category" harus konsisten: jika is_new_category=false gunakan nama PERSIS dari daftar. Jika true, buat nama kategori singkat dan deskriptif (maks 30 karakter).
- Jika pengguna berkata "hapus", "batalkan", "cancel", "jadi nggak jadi", "gajadi beli", "salah catat" dll → gunakan action "delete_transaction" dan isi delete_target dengan info transaksi yang perlu dihapus.
- Hari ini: {$today}. Gunakan untuk "hari ini", "kemarin", "tadi", dsb.
- Jangan menghasilkan teks di luar blok JSON.
PROMPT;

        try {
            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->timeout(45)
                ->retry(2, 2000, fn($e) => $e instanceof \Illuminate\Http\Client\ConnectionException)
                ->post($this->baseUrl . '/chat/completions', [
                    'model'           => 'deepseek-chat',
                    'messages'        => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $userMessage],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature'     => 0.2,
                ]);

            if ($response->failed()) {
                Log::error('DeepSeek API Error: ' . $response->body());
                return $this->errorResponse('Maaf, terjadi kesalahan saat menghubungi asisten AI. Coba lagi.');
            }

            $result  = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '{}';
            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('DeepSeek JSON decode error. Raw: ' . $content);
                return $this->errorResponse('AI memberikan respons yang tidak valid. Coba lagi.');
            }

            // Normalise legacy single-transaction schema
            if (($decoded['action'] ?? '') === 'log_transaction' && isset($decoded['data'])) {
                $decoded['action']       = 'log_transactions';
                $decoded['transactions'] = [$decoded['data']];
                unset($decoded['data']);
            }

            return $decoded;

        } catch (\Exception $e) {
            Log::error('DeepSeek Service Exception: ' . $e->getMessage());
            return $this->errorResponse('Koneksi ke asisten AI gagal. Periksa jaringan internet Anda dan coba lagi.');
        }
    }

    private function errorResponse(string $message): array
    {
        return [
            'action'       => 'chat',
            'transactions' => [],
            'response'     => $message,
        ];
    }
}

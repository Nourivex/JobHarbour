<?php

namespace App\Services;

/**
 * Class MatchmakingService
 *
 * Layanan Mesin Pemadanan Keahlian Otomatis (Automated Skill-Matchmaking Engine).
 *
 * Penjelasan Formula untuk Pembelajaran Mahasiswa:
 * Sistem menggunakan algoritma "Weighted Jaccard Similarity" dengan normalisasi cerdas
 * (Fuzzy & Token-Based Matching) untuk menghitung persentase kesesuaian antara
 * daftar keahlian pelamar kerja dan syarat kompetensi lowongan.
 *
 * Rumus Matematis:
 *                          Σ (bobot keahlian pelamar yang cocok)
 *       Skor Kecocokan = ---------------------------------------- x 100%
 *                          Σ (total bobot seluruh syarat lowongan)
 *
 * Tingkatan Bobot Kualifikasi:
 * - required     : Bobot 3 (Sangat mutlak/wajib dipenuhi)
 * - important    : Bobot 2 (Penting dan menjadi nilai utama)
 * - nice_to_have : Bobot 1 (Nilai tambah / pendukung)
 *
 * Kategori Hasil Penilaian:
 * - 80% - 100% : excellent       (Sangat cocok, lolos otomatis ke tahap berikutnya)
 * - 50% - 79%  : good            (Cukup cocok, masuk peninjauan recruiter manual)
 * - 0% - 49%   : under_qualified (Belum memenuhi kualifikasi minimal)
 */
class MatchmakingService
{
    /**
     * Kamus sinonim dan alias keahlian teknis umum untuk normalisasi cerdas.
     */
    protected const ALIASES = [
        'js' => 'javascript',
        'ts' => 'typescript',
        'golang' => 'go',
        'postgres' => 'postgresql',
        'reactjs' => 'react',
        'react.js' => 'react',
        'vuejs' => 'vue',
        'vue.js' => 'vue',
        'nodejs' => 'node',
        'node.js' => 'node',
        'nextjs' => 'next',
        'next.js' => 'next',
        'nuxtjs' => 'nuxt',
        'nuxt.js' => 'nuxt',
        'k8s' => 'kubernetes',
        'aws' => 'amazon web services',
        'gcp' => 'google cloud platform',
        'restful api' => 'rest api',
        'restful' => 'rest api',
        'rest' => 'rest api',
        'ui/ux' => 'ui ux',
        'ui ux' => 'ui ux',
    ];

    /**
     * Kata-kata pelengkap umum yang diabaikan saat pemecahan token agar variasi penulisan tetap cocok.
     */
    protected const STOP_WORDS = [
        'framework',
        'library',
        'development',
        'developer',
        'programming',
        'language',
        'database',
        'db',
        'technologies',
        'technology',
        'service',
        'services',
    ];

    /**
     * Menghitung skor kecocokan antara keahlian kandidat pelamar dan syarat lowongan kerja.
     *
     * @param  array  $candidateSkills  Array daftar nama keahlian pelamar (cth: ['PHP', 'Laravel', 'MySQL'])
     * @param  array  $jobRequirements  Array kualifikasi lowongan (cth: [['skill' => 'PHP', 'weight' => 3, 'level' => 'required']])
     * @return array                    Hasil kalkulasi lengkap beserta rincian matematisnya
     */
    public function calculate(array $candidateSkills, array $jobRequirements): array
    {
        // Normalisasi dan tokenisasi keahlian kandidat
        $normalizedCandidate = [];
        foreach ($candidateSkills as $s) {
            $raw = is_array($s) ? ($s['name'] ?? $s['skill'] ?? '') : (string) $s;
            $clean = $this->normalizeSkill($raw);
            if ($clean !== '') {
                $normalizedCandidate[] = [
                    'original' => $raw,
                    'clean' => $clean,
                    'tokens' => $this->tokenize($clean),
                ];
            }
        }

        // Jika lowongan tidak menentukan syarat keahlian khusus, beri skor maksimal 100%
        if (empty($jobRequirements)) {
            return [
                'score' => 100.0,
                'category' => 'excellent',
                'matched_skills' => $candidateSkills,
                'missing_skills' => [],
                'calculation_details' => [
                    'matched_weight' => 0,
                    'total_weight' => 0,
                    'formula' => 'Tanpa syarat spesifik (Default 100%)',
                ],
            ];
        }

        $totalRequiredWeight = 0; // Total akumulasi bobot semua syarat
        $matchedWeight = 0;       // Akumulasi bobot keahlian yang berhasil dicocokkan
        $matchedSkills = [];      // Daftar keahlian yang cocok
        $missingSkills = [];      // Daftar keahlian yang belum dimiliki pelamar

        // Iterasi setiap syarat keahlian yang diminta oleh lowongan
        foreach ($jobRequirements as $req) {
            $skillName = is_array($req) ? ($req['skill'] ?? $req['name'] ?? '') : (string) $req;
            $level = strtolower(is_array($req) ? ($req['level'] ?? 'required') : 'required');

            // Tentukan bobot standar berdasarkan level kualifikasi
            $weight = match ($level) {
                'required' => 3,
                'important' => 2,
                'nice_to_have', 'nice-to-have' => 1,
                default => (int) ($req['weight'] ?? 2),
            };

            // Jika dalam data terdapat bobot kustom positif, utamakan bobot tersebut
            if (is_array($req) && isset($req['weight']) && is_numeric($req['weight']) && (int) $req['weight'] > 0) {
                $weight = (int) $req['weight'];
            }

            $totalRequiredWeight += $weight;

            // Periksa apakah keahlian cocok dengan salah satu keahlian kandidat
            $matchedCandidate = $this->findMatch($skillName, $normalizedCandidate);

            if ($matchedCandidate !== null) {
                $matchedWeight += $weight;
                $matchedSkills[] = $skillName;
            } else {
                $missingSkills[] = $skillName;
            }
        }

        // Hitung persentase skor akhir (dibulatkan 2 angka di belakang koma)
        $score = $totalRequiredWeight > 0 ? round(($matchedWeight / $totalRequiredWeight) * 100, 2) : 0.0;

        // Tentukan kategori kelayakan berdasarkan skor
        $category = match (true) {
            $score >= 80.0 => 'excellent',
            $score >= 50.0 => 'good',
            default => 'under_qualified',
        };

        return [
            'score' => $score,
            'category' => $category,
            'matched_skills' => $matchedSkills,
            'missing_skills' => $missingSkills,
            'calculation_details' => [
                'matched_weight' => $matchedWeight,
                'total_weight' => $totalRequiredWeight,
                'formula' => 'Skor = (Σ Bobot Cocok / Σ Total Bobot Syarat) x 100%',
            ],
        ];
    }

    /**
     * Normalisasi string keahlian: huruf kecil, bersihkan ekstensi, dan petakan alias.
     */
    public function normalizeSkill(string $skill): string
    {
        $s = mb_strtolower(trim($skill));
        // Ganti titik pada ekstensi umum (.js -> js)
        $s = preg_replace('/\.([a-z]+)/i', '$1', $s);
        // Ganti karakter non-alfanumerik dengan spasi
        $s = preg_replace('/[^a-z0-9\s]/i', ' ', $s);
        // Hapus spasi berlebih
        $s = trim(preg_replace('/\s+/', ' ', $s));

        return self::ALIASES[$s] ?? $s;
    }

    /**
     * Pecah menjadi kumpulan token kata bersih tanpa stop words.
     */
    public function tokenize(string $cleanSkill): array
    {
        $words = array_values(array_filter(explode(' ', $cleanSkill), fn ($w) => $w !== ''));
        $filtered = array_values(array_filter($words, fn ($w) => ! in_array($w, self::STOP_WORDS, true)));

        return ! empty($filtered) ? $filtered : $words;
    }

    /**
     * Menemukan keahlian kandidat yang cocok dengan syarat lowongan.
     */
    public function findMatch(string $reqSkill, array $normalizedCandidates): ?string
    {
        $reqClean = $this->normalizeSkill($reqSkill);
        if ($reqClean === '') {
            return null;
        }

        $reqTokens = $this->tokenize($reqClean);

        foreach ($normalizedCandidates as $cand) {
            $candClean = $cand['clean'];
            $candTokens = $cand['tokens'];

            // 1. Kecocokan eksak setelah pembersihan alias
            if ($reqClean === $candClean) {
                return $cand['original'];
            }

            // 2. Token overlap: subset kata (misal: "PHP" dan "PHP 8.2", "Laravel" dan "Laravel 11")
            $isSubset1 = count(array_diff($reqTokens, $candTokens)) === 0;
            $isSubset2 = count(array_diff($candTokens, $reqTokens)) === 0;

            if ($isSubset1 || $isSubset2) {
                return $cand['original'];
            }

            // 3. Batas kata (word boundary match)
            if (preg_match('/\b'.preg_quote($reqClean, '/').'\b/i', $candClean) ||
                preg_match('/\b'.preg_quote($candClean, '/').'\b/i', $reqClean)) {
                return $cand['original'];
            }
        }

        return null;
    }
}


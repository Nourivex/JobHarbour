<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\JobVacancy;
use App\Models\PipelineTimeline;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic dummy data for JobHarbour.
     */
    public function run(): void
    {
        // =====================================================================
        // 1. Akun Recruiter & Admin
        // =====================================================================
        $recruiter = User::updateOrCreate(
            ['email' => 'recruiter@jobharbour.id'],
            [
                'name' => 'Fahren Andrean (Harbour Master)',
                'password' => Hash::make('password123'),
                'role' => 'recruiter',
                'phone' => '081234567891',
                'bio' => 'Lead Technical Recruiter PT Teknologi Maju & Nusantara Maritime Tech.',
                'location' => 'Tegal',
                'skills' => ['Technical Recruiting', 'Talent Acquisition', 'HRIS'],
            ]
        );

        User::updateOrCreate(
            ['email' => 'recruiter@padukerja.id'],
            [
                'name' => 'Fahren Andrean (Recruiter Alias)',
                'password' => Hash::make('password123'),
                'role' => 'recruiter',
                'phone' => '081234567891',
                'bio' => 'Lead Technical Recruiter (Alias PaduKerja)',
                'location' => 'Tegal',
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@jobharbour.id'],
            [
                'name' => 'Admin JobHarbour UHN',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '081100223344',
                'bio' => 'Administrator Sistem Platform Rekrutmen JobHarbour.',
                'location' => 'Tegal',
                'skills' => ['System Administration', 'DevOps', 'Security'],
            ]
        );

        // =====================================================================
        // 2. Akun Pelamar (Applicants)
        // =====================================================================
        $budi = User::updateOrCreate(
            ['email' => 'budi@jobharbour.id'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => 'applicant',
                'phone' => '081234567890',
                'bio' => 'Backend Developer dengan pengalaman 3 tahun membangun microservices di ekosistem PHP, Laravel, dan MariaDB.',
                'location' => 'Tegal',
                'skills' => ['PHP', 'Laravel', 'MySQL', 'REST API', 'Git', 'Docker'],
            ]
        );

        User::updateOrCreate(
            ['email' => 'budi@padukerja.id'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => 'applicant',
                'phone' => '081234567890',
                'bio' => 'Backend Developer dengan pengalaman 3 tahun di ekosistem PHP & Laravel.',
                'location' => 'Tegal',
                'skills' => ['PHP', 'Laravel', 'MySQL', 'Git'],
            ]
        );

        $siti = User::updateOrCreate(
            ['email' => 'siti@jobharbour.id'],
            [
                'name' => 'Siti Nurhaliza',
                'password' => Hash::make('password123'),
                'role' => 'applicant',
                'phone' => '081298765432',
                'bio' => 'Software Engineer spesialis Python, Data Engineering, dan Cloud Infrastructure.',
                'location' => 'Semarang',
                'skills' => ['Python', 'Django', 'PostgreSQL', 'Docker', 'Kubernetes', 'SQL'],
            ]
        );

        User::updateOrCreate(
            ['email' => 'siti@padukerja.id'],
            [
                'name' => 'Siti Nurhaliza',
                'password' => Hash::make('password123'),
                'role' => 'applicant',
                'phone' => '081298765432',
                'bio' => 'Software Engineer spesialis Python & Data Engineering.',
                'location' => 'Semarang',
                'skills' => ['Python', 'Django', 'PostgreSQL', 'Docker'],
            ]
        );

        $andi = User::updateOrCreate(
            ['email' => 'andi@jobharbour.id'],
            [
                'name' => 'Andi Pratama',
                'password' => Hash::make('password123'),
                'role' => 'applicant',
                'phone' => '081377889900',
                'bio' => 'Frontend & UI Engineer yang fokus pada arsitektur desain modern, React, TypeScript, dan Tailwind CSS.',
                'location' => 'Bandung',
                'skills' => ['React', 'JavaScript', 'TypeScript', 'Tailwind CSS', 'Next.js', 'Figma'],
            ]
        );

        $dewi = User::updateOrCreate(
            ['email' => 'dewi@jobharbour.id'],
            [
                'name' => 'Dewi Lestari',
                'password' => Hash::make('password123'),
                'role' => 'applicant',
                'phone' => '085711223344',
                'bio' => 'Fullstack Web Developer lulusan Informatika dengan antusiasme tinggi di bidang Cloud & DevOps.',
                'location' => 'Tegal',
                'skills' => ['PHP', 'Laravel', 'JavaScript', 'React', 'MySQL', 'Git'],
            ]
        );

        // =====================================================================
        // 3. Katalog Lowongan Pekerjaan (Job Vacancies)
        // =====================================================================
        $job1 = JobVacancy::updateOrCreate(
            ['title' => 'Senior Backend Engineer (Microservices)', 'company' => 'PT Teknologi Maju Bahari'],
            [
                'description' => 'Merancang, membangun, dan mengoptimalkan layanan microservices REST API berkinerja tinggi menggunakan Laravel 13 dan MariaDB untuk ekosistem logistik pelabuhan nasional.',
                'location' => 'Tegal',
                'employment_type' => 'full-time',
                'salary_min' => 12000000,
                'salary_max' => 18000000,
                'quota' => 3,
                'filled' => 1,
                'requirements' => [
                    ['skill' => 'PHP', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'Laravel', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'MySQL', 'level' => 'important', 'weight' => 2],
                    ['skill' => 'Redis', 'level' => 'nice_to_have', 'weight' => 1],
                    ['skill' => 'Docker', 'level' => 'nice_to_have', 'weight' => 1],
                ],
                'status' => 'open',
                'posted_by' => $recruiter->id,
                'expires_at' => now()->addDays(30),
            ]
        );

        $job2 = JobVacancy::updateOrCreate(
            ['title' => 'Frontend React & Design System Specialist', 'company' => 'PT Digital Solusindo Nusantara'],
            [
                'description' => 'Mengembangkan antarmuka pengguna web modern bertema Modern Harbour Tech yang responsif, aksesibel, dan terintegrasi penuh dengan REST API.',
                'location' => 'Bandung',
                'employment_type' => 'remote',
                'salary_min' => 10000000,
                'salary_max' => 15000000,
                'quota' => 2,
                'filled' => 0,
                'requirements' => [
                    ['skill' => 'React', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'JavaScript', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'Tailwind CSS', 'level' => 'important', 'weight' => 2],
                    ['skill' => 'TypeScript', 'level' => 'nice_to_have', 'weight' => 1],
                ],
                'status' => 'open',
                'posted_by' => $recruiter->id,
                'expires_at' => now()->addDays(45),
            ]
        );

        $job3 = JobVacancy::updateOrCreate(
            ['title' => 'Cloud & DevOps Reliability Engineer', 'company' => 'PT Samudera Cloud Indonesia'],
            [
                'description' => 'Mengelola pipeline CI/CD, orkestrasi kontainer Docker & Kubernetes, serta pemantauan infrastruktur terdistribusi 24/7.',
                'location' => 'Semarang',
                'employment_type' => 'full-time',
                'salary_min' => 14000000,
                'salary_max' => 21000000,
                'quota' => 2,
                'filled' => 0,
                'requirements' => [
                    ['skill' => 'Docker', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'Kubernetes', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'Linux', 'level' => 'important', 'weight' => 2],
                    ['skill' => 'Python', 'level' => 'nice_to_have', 'weight' => 1],
                    ['skill' => 'PostgreSQL', 'level' => 'nice_to_have', 'weight' => 1],
                ],
                'status' => 'open',
                'posted_by' => $recruiter->id,
                'expires_at' => now()->addDays(25),
            ]
        );

        $job4 = JobVacancy::updateOrCreate(
            ['title' => 'Fullstack Laravel & React Developer', 'company' => 'PT Pelabuhan Digital Tegal'],
            [
                'description' => 'Membangun aplikasi portal talenta dan manajemen armada dari sisi backend API hingga antarmuka pengguna interaktif.',
                'location' => 'Tegal',
                'employment_type' => 'contract',
                'salary_min' => 8500000,
                'salary_max' => 12500000,
                'quota' => 4,
                'filled' => 0,
                'requirements' => [
                    ['skill' => 'PHP', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'Laravel', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'React', 'level' => 'important', 'weight' => 2],
                    ['skill' => 'MySQL', 'level' => 'important', 'weight' => 2],
                    ['skill' => 'Git', 'level' => 'nice_to_have', 'weight' => 1],
                ],
                'status' => 'open',
                'posted_by' => $recruiter->id,
                'expires_at' => now()->addDays(40),
            ]
        );

        $job5 = JobVacancy::updateOrCreate(
            ['title' => 'Data & Analytics Engineer (Internship)', 'company' => 'PT Nusantara Logistik Maritim'],
            [
                'description' => 'Program magang terstruktur untuk mengolah data telemetri kapal, menyusun pipeline ETL Python, dan visualisasi metrik operasional.',
                'location' => 'Semarang',
                'employment_type' => 'internship',
                'salary_min' => 4500000,
                'salary_max' => 6500000,
                'quota' => 5,
                'filled' => 0,
                'requirements' => [
                    ['skill' => 'Python', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'SQL', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'PostgreSQL', 'level' => 'important', 'weight' => 2],
                    ['skill' => 'Docker', 'level' => 'nice_to_have', 'weight' => 1],
                ],
                'status' => 'open',
                'posted_by' => $recruiter->id,
                'expires_at' => now()->addDays(60),
            ]
        );

        // =====================================================================
        // 4. Berkas Lamaran & Riwayat Pipeline (Berbagai Tahapan Seleksi)
        // =====================================================================

        // Lamaran 1: Budi -> Job 1 (Tahap: Interview, Skor: 90%)
        $app1 = Application::updateOrCreate(
            ['user_id' => $budi->id, 'job_vacancy_id' => $job1->id],
            [
                'cover_letter' => 'Saya berpengalaman 3 tahun membangun arsitektur microservices dengan Laravel, MySQL, dan Docker. Siap berkontribusi penuh di Tegal.',
                'match_score' => 90.00,
                'match_category' => 'excellent',
                'matched_skills' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
                'missing_skills' => ['Redis'],
                'status' => 'interview',
            ]
        );

        PipelineTimeline::updateOrCreate(
            ['application_id' => $app1->id],
            [
                'current_stage' => 'interview',
                'stages' => [
                    [
                        'stage' => 'screening',
                        'status' => 'passed',
                        'entered_at' => now()->subDays(6)->toIso8601String(),
                        'completed_at' => now()->subDays(5)->toIso8601String(),
                        'notes' => 'Hasil Mesin Pemadanan Otomatis: Skor 90%. Kategori: excellent. Lolos otomatis.',
                    ],
                    [
                        'stage' => 'assessment',
                        'status' => 'passed',
                        'entered_at' => now()->subDays(5)->toIso8601String(),
                        'completed_at' => now()->subDays(2)->toIso8601String(),
                        'notes' => 'Live coding REST API & optimasi query diselesaikan dengan skor 94/100.',
                    ],
                    [
                        'stage' => 'interview',
                        'status' => 'in_progress',
                        'entered_at' => now()->subDays(2)->toIso8601String(),
                        'completed_at' => null,
                        'notes' => 'Wawancara teknis akhir bersama Lead Architect & HR Manager.',
                        'interview_schedule' => [
                            'datetime' => now()->addDays(2)->setTime(10, 0)->toIso8601String(),
                            'type' => 'online',
                            'meeting_url' => 'https://meet.google.com/jobharbour-interview-budi',
                            'interviewer' => 'Fahren Andrean & Tech Lead',
                        ],
                    ],
                ],
            ]
        );

        // Lamaran 2: Budi -> Job 4 (Tahap: Screening, Skor: 81.82%)
        $app2 = Application::updateOrCreate(
            ['user_id' => $budi->id, 'job_vacancy_id' => $job4->id],
            [
                'cover_letter' => 'Sangat tertarik bergabung di proyek portal talenta PT Pelabuhan Digital Tegal.',
                'match_score' => 81.82,
                'match_category' => 'excellent',
                'matched_skills' => ['PHP', 'Laravel', 'MySQL', 'Git'],
                'missing_skills' => ['React'],
                'status' => 'screening',
            ]
        );

        PipelineTimeline::updateOrCreate(
            ['application_id' => $app2->id],
            [
                'current_stage' => 'screening',
                'stages' => [
                    [
                        'stage' => 'screening',
                        'status' => 'passed',
                        'entered_at' => now()->subDays(1)->toIso8601String(),
                        'completed_at' => now()->subDays(1)->toIso8601String(),
                        'notes' => 'Hasil Mesin Pemadanan Otomatis: Skor 81.82%. Kategori: excellent.',
                    ],
                ],
            ]
        );

        // Lamaran 3: Dewi -> Job 4 (Tahap: Offering, Skor: 100%)
        $app3 = Application::updateOrCreate(
            ['user_id' => $dewi->id, 'job_vacancy_id' => $job4->id],
            [
                'cover_letter' => 'Seluruh kualifikasi PHP, Laravel, React, MySQL, dan Git sesuai dengan pengalaman proyek saya.',
                'match_score' => 100.00,
                'match_category' => 'excellent',
                'matched_skills' => ['PHP', 'Laravel', 'React', 'MySQL', 'Git'],
                'missing_skills' => [],
                'status' => 'offering',
            ]
        );

        PipelineTimeline::updateOrCreate(
            ['application_id' => $app3->id],
            [
                'current_stage' => 'offering',
                'stages' => [
                    [
                        'stage' => 'screening',
                        'status' => 'passed',
                        'entered_at' => now()->subDays(10)->toIso8601String(),
                        'completed_at' => now()->subDays(9)->toIso8601String(),
                        'notes' => 'Skor kecocokan sempurna 100% (Excellent).',
                    ],
                    [
                        'stage' => 'assessment',
                        'status' => 'passed',
                        'entered_at' => now()->subDays(9)->toIso8601String(),
                        'completed_at' => now()->subDays(6)->toIso8601String(),
                        'notes' => 'Take-home test fullstack selesai tepat waktu dengan hasil sangat memuaskan.',
                    ],
                    [
                        'stage' => 'interview',
                        'status' => 'passed',
                        'entered_at' => now()->subDays(6)->toIso8601String(),
                        'completed_at' => now()->subDays(2)->toIso8601String(),
                        'notes' => 'Komunikasi dan penguasaan teknis sangat baik.',
                    ],
                    [
                        'stage' => 'offering',
                        'status' => 'in_progress',
                        'entered_at' => now()->subDays(1)->toIso8601String(),
                        'completed_at' => null,
                        'notes' => 'Offering letter telah dikirimkan ke kandidat, menunggu tanda tangan persetujuan.',
                    ],
                ],
            ]
        );

        // Lamaran 4: Andi -> Job 2 (Tahap: Assessment, Skor: 100%)
        $app4 = Application::updateOrCreate(
            ['user_id' => $andi->id, 'job_vacancy_id' => $job2->id],
            [
                'cover_letter' => 'Saya spesialis React, TypeScript, dan Tailwind CSS dengan portofolio design system skala produksi.',
                'match_score' => 100.00,
                'match_category' => 'excellent',
                'matched_skills' => ['React', 'JavaScript', 'Tailwind CSS', 'TypeScript'],
                'missing_skills' => [],
                'status' => 'assessment',
            ]
        );

        PipelineTimeline::updateOrCreate(
            ['application_id' => $app4->id],
            [
                'current_stage' => 'assessment',
                'stages' => [
                    [
                        'stage' => 'screening',
                        'status' => 'passed',
                        'entered_at' => now()->subDays(4)->toIso8601String(),
                        'completed_at' => now()->subDays(3)->toIso8601String(),
                        'notes' => 'Skor pemadanan otomatis 100% (Excellent).',
                    ],
                    [
                        'stage' => 'assessment',
                        'status' => 'in_progress',
                        'entered_at' => now()->subDays(3)->toIso8601String(),
                        'completed_at' => null,
                        'notes' => 'Sedang mengerjakan tantangan slicing UI komponen Harbour Design System.',
                    ],
                ],
            ]
        );

        // Lamaran 5: Siti -> Job 3 (Tahap: Interview, Skor: 80%)
        $app5 = Application::updateOrCreate(
            ['user_id' => $siti->id, 'job_vacancy_id' => $job3->id],
            [
                'cover_letter' => 'Berpengalaman mengelola cluster Docker, Kubernetes, dan otomasi Python untuk workload data.',
                'match_score' => 80.00,
                'match_category' => 'excellent',
                'matched_skills' => ['Docker', 'Kubernetes', 'Python', 'PostgreSQL'],
                'missing_skills' => ['Linux'],
                'status' => 'interview',
            ]
        );

        PipelineTimeline::updateOrCreate(
            ['application_id' => $app5->id],
            [
                'current_stage' => 'interview',
                'stages' => [
                    [
                        'stage' => 'screening',
                        'status' => 'passed',
                        'entered_at' => now()->subDays(5)->toIso8601String(),
                        'completed_at' => now()->subDays(4)->toIso8601String(),
                        'notes' => 'Skor pemadanan 80% (Excellent).',
                    ],
                    [
                        'stage' => 'assessment',
                        'status' => 'passed',
                        'entered_at' => now()->subDays(4)->toIso8601String(),
                        'completed_at' => now()->subDays(2)->toIso8601String(),
                        'notes' => 'Simulasi deployment Kubernetes lulus dengan baik.',
                    ],
                    [
                        'stage' => 'interview',
                        'status' => 'in_progress',
                        'entered_at' => now()->subDays(1)->toIso8601String(),
                        'completed_at' => null,
                        'notes' => 'Dijadwalkan wawancara teknis infrastruktur.',
                        'interview_schedule' => [
                            'datetime' => now()->addDays(3)->setTime(14, 0)->toIso8601String(),
                            'type' => 'online',
                            'meeting_url' => 'https://meet.google.com/jobharbour-interview-siti',
                            'interviewer' => 'VP of Engineering',
                        ],
                    ],
                ],
            ]
        );

        // Lamaran 6: Siti -> Job 5 (Tahap: Screening, Skor: 100%)
        $app6 = Application::updateOrCreate(
            ['user_id' => $siti->id, 'job_vacancy_id' => $job5->id],
            [
                'cover_letter' => 'Siap berkontribusi pada proyek pipeline data telemetri kapal menggunakan Python dan PostgreSQL.',
                'match_score' => 100.00,
                'match_category' => 'excellent',
                'matched_skills' => ['Python', 'SQL', 'PostgreSQL', 'Docker'],
                'missing_skills' => [],
                'status' => 'screening',
            ]
        );

        PipelineTimeline::updateOrCreate(
            ['application_id' => $app6->id],
            [
                'current_stage' => 'screening',
                'stages' => [
                    [
                        'stage' => 'screening',
                        'status' => 'passed',
                        'entered_at' => now()->subHours(12)->toIso8601String(),
                        'completed_at' => now()->subHours(12)->toIso8601String(),
                        'notes' => 'Skor pemadanan otomatis 100% (Excellent).',
                    ],
                ],
            ]
        );

        // Lamaran 7: Andi -> Job 1 (Tahap: Rejected, Skor: 0% - contoh kandidat beda bidang)
        $app7 = Application::updateOrCreate(
            ['user_id' => $andi->id, 'job_vacancy_id' => $job1->id],
            [
                'cover_letter' => 'Mencoba melamar posisi Backend Engineer meskipun fokus utama saya di Frontend.',
                'match_score' => 0.00,
                'match_category' => 'under_qualified',
                'matched_skills' => [],
                'missing_skills' => ['PHP', 'Laravel', 'MySQL', 'Redis', 'Docker'],
                'status' => 'rejected',
            ]
        );

        PipelineTimeline::updateOrCreate(
            ['application_id' => $app7->id],
            [
                'current_stage' => 'rejected',
                'stages' => [
                    [
                        'stage' => 'screening',
                        'status' => 'rejected',
                        'entered_at' => now()->subDays(3)->toIso8601String(),
                        'completed_at' => now()->subDays(2)->toIso8601String(),
                        'notes' => 'Hasil Mesin Pemadanan: 0% (under_qualified). Disarankan melamar posisi Frontend React Specialist.',
                    ],
                    [
                        'stage' => 'rejected',
                        'status' => 'rejected',
                        'entered_at' => now()->subDays(2)->toIso8601String(),
                        'completed_at' => now()->subDays(2)->toIso8601String(),
                        'notes' => 'Belum memenuhi kualifikasi wajib PHP & Laravel untuk posisi Senior Backend.',
                    ],
                ],
            ]
        );
    }
}

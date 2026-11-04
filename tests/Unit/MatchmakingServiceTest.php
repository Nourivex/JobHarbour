<?php

namespace Tests\Unit;

use App\Services\MatchmakingService;
use PHPUnit\Framework\TestCase;

class MatchmakingServiceTest extends TestCase
{
    protected MatchmakingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MatchmakingService();
    }

    /**
     * Memastikan keahlian dengan nomor versi atau kata pelengkap tetap cocok (fuzzy matching).
     */
    public function test_fuzzy_and_version_skill_matching(): void
    {
        $candidateSkills = [
            'PHP 8.2',
            'Laravel Framework',
            'MySQL Database',
            'Docker Container',
            'RESTful API',
        ];

        $jobRequirements = [
            ['skill' => 'PHP', 'level' => 'required', 'weight' => 3],
            ['skill' => 'Laravel', 'level' => 'required', 'weight' => 3],
            ['skill' => 'MySQL', 'level' => 'important', 'weight' => 2],
            ['skill' => 'Docker', 'level' => 'important', 'weight' => 2],
            ['skill' => 'REST API', 'level' => 'nice_to_have', 'weight' => 1],
        ];

        $result = $this->service->calculate($candidateSkills, $jobRequirements);

        $this->assertEquals(100.0, $result['score']);
        $this->assertEquals('excellent', $result['category']);
        $this->assertCount(5, $result['matched_skills']);
        $this->assertEmpty($result['missing_skills']);
    }

    /**
     * Memastikan alias umum (JS/TypeScript/Golang/Postgres) dikenali secara otomatis.
     */
    public function test_alias_dictionary_matching(): void
    {
        $candidateSkills = ['Golang', 'JS', 'Postgres', 'K8s'];
        $jobRequirements = [
            ['skill' => 'Go', 'level' => 'required', 'weight' => 3],
            ['skill' => 'JavaScript', 'level' => 'required', 'weight' => 3],
            ['skill' => 'PostgreSQL', 'level' => 'important', 'weight' => 2],
            ['skill' => 'Kubernetes', 'level' => 'nice_to_have', 'weight' => 1],
        ];

        $result = $this->service->calculate($candidateSkills, $jobRequirements);

        $this->assertEquals(100.0, $result['score']);
        $this->assertEquals('excellent', $result['category']);
        $this->assertCount(4, $result['matched_skills']);
    }

    /**
     * Memastikan keahlian yang berbeda tidak salah dicocokkan (misal: Java tidak boleh cocok dengan JavaScript).
     */
    public function test_unrelated_skills_do_not_match(): void
    {
        $candidateSkills = ['Java', 'C++', 'Python'];
        $jobRequirements = [
            ['skill' => 'JavaScript', 'level' => 'required', 'weight' => 3],
            ['skill' => 'PHP', 'level' => 'required', 'weight' => 3],
        ];

        $result = $this->service->calculate($candidateSkills, $jobRequirements);

        $this->assertEquals(0.0, $result['score']);
        $this->assertEquals('under_qualified', $result['category']);
        $this->assertEmpty($result['matched_skills']);
        $this->assertCount(2, $result['missing_skills']);
    }

    /**
     * Memastikan bobot kualifikasi menghasilkan persentase skor yang akurat.
     */
    public function test_weighted_score_calculation(): void
    {
        // Total bobot: required(3) + important(2) + nice_to_have(1) = 6
        // Kandidat hanya punya 'PHP' (bobot 3) -> 3 / 6 * 100% = 50%
        $candidateSkills = ['PHP'];
        $jobRequirements = [
            ['skill' => 'PHP', 'level' => 'required', 'weight' => 3],
            ['skill' => 'Redis', 'level' => 'important', 'weight' => 2],
            ['skill' => 'GraphQL', 'level' => 'nice_to_have', 'weight' => 1],
        ];

        $result = $this->service->calculate($candidateSkills, $jobRequirements);

        $this->assertEquals(50.0, $result['score']);
        $this->assertEquals('good', $result['category']);
        $this->assertEquals(['PHP'], $result['matched_skills']);
        $this->assertEquals(['Redis', 'GraphQL'], $result['missing_skills']);
    }
}


<?php

namespace App\Jobs;

use App\Models\Material;
use App\Services\OpenRouterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $material;
    protected $prompt;
    protected $systemPrompt;

    /**
     * Create a new job instance.
     */
    public function __construct(Material $material, string $prompt, string $systemPrompt)
    {
        $this->material = $material;
        $this->prompt = $prompt;
        $this->systemPrompt = $systemPrompt;
    }

    /**
     * Execute the job.
     */
    public function handle(OpenRouterService $aiService): void
    {
        $jobStart = microtime(true);
        $materialId = $this->material->id;

        Log::info("JOB_START | MATERIAL_ID: {$materialId}");

        $openRouterStart = microtime(true);
        $openRouterDuration = 0.0;
        
        try {
            $summary = $aiService->generate($this->prompt, 'summary', $this->systemPrompt);
            $openRouterDuration = microtime(true) - $openRouterStart;

            // Update material with generated summary (even if it's the mock response generated inside OpenRouterService)
            $this->material->update([
                'summary' => $summary
            ]);

            $jobDuration = microtime(true) - $jobStart;
            Log::info("JOB_END | MATERIAL_ID: {$materialId} | JOB_DURATION: {$jobDuration}s | OPENROUTER_DURATION: {$openRouterDuration}s");
        } catch (\Exception $e) {
            $openRouterDuration = microtime(true) - $openRouterStart;
            Log::error("GenerateSummaryJob Exception for MATERIAL_ID: {$materialId}. Error: " . $e->getMessage());

            // Ensure summary is updated to an informative fallback text if it was empty or default processing text
            $currentSummary = trim($this->material->summary ?? '');
            if (empty($currentSummary) || $currentSummary === 'Ringkasan sedang diproses, silakan buka kembali beberapa saat lagi.') {
                $fallbackText = "### ⚠️ Gagal Memproses Ringkasan\n\nMaaf, sistem mengalami kendala (timeout atau error koneksi) saat menghubungi AI untuk meringkas dokumen **" . $this->material->judul . "**.\n\nSilakan coba mengunggah dokumen kembali atau hubungi administrator jika masalah ini terus berlanjut.";
                $this->material->update([
                    'summary' => $fallbackText
                ]);
            }

            $jobDuration = microtime(true) - $jobStart;
            Log::info("JOB_END | MATERIAL_ID: {$materialId} | JOB_DURATION: {$jobDuration}s | OPENROUTER_DURATION: {$openRouterDuration}s");
        }
    }
}

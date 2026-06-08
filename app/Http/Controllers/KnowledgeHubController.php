<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\User;
use App\Models\PromptSetting;
use App\Services\OpenRouterService;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

class KnowledgeHubController extends Controller
{
    protected $aiService;
    protected $storageService;

    public function __construct(OpenRouterService $aiService, SupabaseStorageService $storageService)
    {
        $this->aiService = $aiService;
        $this->storageService = $storageService;
    }

    public function index()
    {
        $materials = Material::where('user_id', Auth::id())->latest()->get();
        return view('knowledge-hub.index', compact('materials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'tipe_file' => 'required|string|in:pdf,docx,pptx,youtube',
            'file_upload' => 'required_if:tipe_file,pdf,docx,pptx|file|mimes:pdf,docx,pptx|max:10240', // Max 10MB
            'youtube_url' => 'required_if:tipe_file,youtube|nullable|url',
        ]);

        $fileUrl = null;
        $extractedText = '';

        if (in_array($request->tipe_file, ['pdf', 'docx', 'pptx']) && $request->hasFile('file_upload')) {
            $file = $request->file('file_upload');

            // 1. Ekstrak Teks Terlebih Dahulu saat File Masih di Temp
            $tempPath = $file->getRealPath();
            
            try {
                if ($request->tipe_file === 'pdf') {
                    if (class_exists('Smalot\PdfParser\Parser')) {
                        $parser = new PdfParser();
                        $pdf = $parser->parseFile($tempPath);
                        $extractedText = $pdf->getText();
                    } else {
                        $extractedText = "Teks PDF: " . $request->judul;
                    }
                } elseif ($request->tipe_file === 'docx') {
                    $extractedText = $this->readDocx($tempPath);
                } elseif ($request->tipe_file === 'pptx') {
                    $extractedText = $this->readPptx($tempPath);
                }
            } catch (\Exception $e) {
                \Log::warning("Text extraction failed for {$request->tipe_file}: " . $e->getMessage());
                $extractedText = "Teks dokumen: " . $request->judul;
            }

            $extractedText = Str::limit($extractedText, 15000);

            // 2. Upload File ke Supabase Storage (atau lokal fallback)
            $fileUrl = $this->storageService->upload($file, 'materials');
        } else {
            // YouTube Link
            $fileUrl = $request->youtube_url;
            $extractedText = "Video YouTube dengan judul: " . $request->judul . ". Tautan: " . $fileUrl;
        }

        // Ambil System Prompt Summary dari database
        $systemPrompt = PromptSetting::where('key', 'prompt_summary')->value('value') ?? 
            "Buat ringkasan komprehensif, terstruktur, dan menarik dari materi kuliah berikut.";

        // Buat prompt untuk AI
        $prompt = "Materi: " . $request->judul . "\n\nIsi Konten/Teks:\n" . $extractedText;

        // Panggil OpenRouter Service untuk generate summary
        $summary = $this->aiService->generate($prompt, 'summary', $systemPrompt);

        // Buat material baru
        $material = Material::create([
            'user_id' => Auth::id(),
            'judul' => $request->judul,
            'tipe_file' => $request->tipe_file,
            'file_url' => $fileUrl,
            'summary' => $summary,
            'mindmap_data' => null,
        ]);

        // Award XP ke user (+100 XP)
        $user = Auth::user();
        $xpGained = 100;
        $newXp = $user->xp + $xpGained;
        $newLevel = $user->level;
        $xpThreshold = $user->level * 500;
        if ($newXp >= $xpThreshold) {
            $newXp -= $xpThreshold;
            $newLevel++;
        }
        User::where('id', $user->id)->update([
            'xp' => $newXp,
            'level' => $newLevel
        ]);

        return redirect()->route('knowledge-hub.show', $material->id)
            ->with('success', "Materi berhasil diunggah dan dianalisis AI! Anda mendapatkan +{$xpGained} XP! 🎉");
    }

    public function show(Material $material)
    {
        if ($material->user_id !== Auth::id()) {
            abort(403);
        }

        return view('knowledge-hub.show', compact('material'));
    }

    public function destroy(Material $material)
    {
        if ($material->user_id !== Auth::id()) {
            abort(403);
        }

        // Hapus file lokal jika file_url mengarah ke public path lokal
        if ($material->file_url && !str_starts_with($material->file_url, 'http') && file_exists(public_path($material->file_url))) {
            @unlink(public_path($material->file_url));
        }

        $material->delete();

        return redirect()->route('knowledge-hub.index')->with('success', 'Materi berhasil dihapus dari workspace.');
    }

    // AI Chat Materi
    public function chat(Request $request, Material $material)
    {
        if ($material->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        // Ambil System Prompt Chat Materi
        $systemPromptBase = PromptSetting::where('key', 'prompt_chat_materi')->value('value') ?? 
            "Jawablah pertanyaan mengenai materi kuliah berikut secara ramah dan informatif.";

        // Tempelkan ringkasan materi ke System Prompt sebagai konteks (RAG Sederhana)
        $systemPrompt = $systemPromptBase . "\n\nRINGKASAN DOKUMEN MATERI:\n" . $material->summary;

        // Panggil AI
        $response = $this->aiService->generate($request->message, 'chat_materi', $systemPrompt);

        return response()->json([
            'success' => true,
            'response' => $response,
        ]);
    }

    /**
     * Native parser DOCX extracting text from word/document.xml.
     */
    protected function readDocx($filepath)
    {
        $content = '';
        $zip = new \ZipArchive();
        if ($zip->open($filepath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();
                
                // Parse XML tags cleanly
                $xml = new \SimpleXMLElement($data);
                $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                $paragraphs = $xml->xpath('//w:t');
                foreach ($paragraphs as $p) {
                    $content .= $p . ' ';
                }
                return $content;
            }
            $zip->close();
        }
        return '';
    }

    /**
     * Native parser PPTX extracting text from ppt/slides/slide*.xml.
     */
    protected function readPptx($filepath)
    {
        $content = '';
        $zip = new \ZipArchive();
        if ($zip->open($filepath) === true) {
            for ($i = 1; ; $i++) {
                $slidePath = "ppt/slides/slide{$i}.xml";
                if ($zip->locateName($slidePath) === false) {
                    break;
                }
                
                $data = $zip->getFromName($slidePath);
                $xml = new \SimpleXMLElement($data);
                $xml->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
                $xml->registerXPathNamespace('p', 'http://schemas.openxmlformats.org/presentationml/2006/main');
                
                $texts = $xml->xpath('//a:t');
                foreach ($texts as $t) {
                    $content .= $t . ' ';
                }
            }
            $zip->close();
            return $content;
        }
        return '';
    }
}

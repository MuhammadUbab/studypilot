@extends('layouts.app')

@section('title', 'AI Settings')

@section('content')
<div class="container-fluid" style="max-width: 800px; margin: 0 auto;">
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="fw-bold">AI Settings</h1>
            <p class="text-secondary">Konfigurasikan model AI default, kelola penyedia API, dan atur parameter respon kecerdasan buatan.</p>
        </div>
    </div>

    <!-- AI settings form -->
    <div class="glass-card p-4">
        <form action="{{ route('admin.ai-settings.update') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="form-label fw-semibold">Penyedia API Utama</label>
                <select class="form-select bg-dark border-secondary text-heading" disabled>
                    <option value="openrouter" selected>OpenRouter API (Direkomendasikan)</option>
                </select>
                <span class="text-secondary small mt-1 d-block">OpenRouter dikonfigurasi melalui kunci lingkungan API Anda.</span>
            </div>

            <div class="mb-4">
                <label for="default_model" class="form-label fw-semibold">Model AI Default (Utama)</label>
                <select class="form-select bg-dark border-secondary text-heading" id="default_model" name="default_model" required>
                    <option value="deepseek/deepseek-chat" {{ $defaultModel === 'deepseek/deepseek-chat' ? 'selected' : '' }}>DeepSeek V3 (Default)</option>
                    <option value="google/gemini-2.5-flash" {{ $defaultModel === 'google/gemini-2.5-flash' ? 'selected' : '' }}>Gemini 2.5 Flash</option>
                    <option value="anthropic/claude-3-haiku" {{ $defaultModel === 'anthropic/claude-3-haiku' ? 'selected' : '' }}>Claude 3 Haiku</option>
                    <option value="openai/gpt-4o-mini" {{ $defaultModel === 'openai/gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini</option>
                    <option value="meta-llama/llama-3.3-70b-instruct" {{ $defaultModel === 'meta-llama/llama-3.3-70b-instruct' ? 'selected' : '' }}>Llama 3.3 70b</option>
                    <option value="qwen/qwen-2.5-72b-instruct" {{ $defaultModel === 'qwen/qwen-2.5-72b-instruct' ? 'selected' : '' }}>Qwen 2.5 72b</option>
                </select>
                <span class="text-secondary small mt-1 d-block">Model ini akan digunakan untuk semua proses generasi teks, ringkasan, kuis, dan planner secara default.</span>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Pengaturan AI
            </button>
        </form>
    </div>
</div>
@endsection

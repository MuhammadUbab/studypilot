@extends('layouts.app')

@section('title', $material->judul)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb & Top Bar -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('knowledge-hub.index') }}" class="text-secondary text-decoration-none">Knowledge Hub</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">{{ $material->judul }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-white mb-0">{{ $material->judul }}</h2>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
            <!-- Generate Quiz Form -->
            <form action="{{ route('quizzes.generate', $material->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="showQuizLoading(this)">
                    <i class="fa-solid fa-graduation-cap me-2"></i> Generate Kuis AI
                </button>
            </form>
            <a href="{{ asset($material->file_url) }}" target="_blank" class="btn btn-outline-light">
                <i class="fa-solid fa-up-right-from-square me-2"></i> {{ $material->tipe_file === 'pdf' ? 'Lihat PDF' : 'Buka YouTube' }}
            </a>
        </div>
    </div>

    <!-- Workspace Layout Grid -->
    <div class="row g-4">
        <!-- AI Summary Panel -->
        <div class="col-lg-7">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary-subtle pb-3">
                    <h4 class="fw-bold mb-0">
                        <i class="fa-solid fa-notes-medical text-indigo me-2" style="color:var(--color-primary);"></i> AI Notes & Summary
                    </h4>
                    <a href="{{ route('materials.pdf', $material->id) }}" class="btn btn-sm btn-outline-light">
                        <i class="fa-solid fa-file-pdf me-1 text-danger"></i> Download PDF
                    </a>
                </div>
                
                <!-- Markdown Rendered Summary -->
                <div class="text-light markdown-body" id="summary-content" style="line-height: 1.7; font-size: 0.95rem;">
                    <!-- Raw text fallback -->
                    {!! nl2br(e($material->summary)) !!}
                </div>
            </div>
        </div>

        <!-- AI Chat Materi Panel -->
        <div class="col-lg-5">
            <div class="glass-card p-4 d-flex flex-column" style="height: 600px;">
                <h4 class="fw-bold mb-3 border-bottom border-secondary-subtle pb-3">
                    <i class="fa-solid fa-comments text-indigo me-2" style="color:var(--color-primary);"></i> Chat Materi
                </h4>
                
                <!-- Messages Box -->
                <div class="flex-grow-1 overflow-y-auto mb-3 pe-2" id="chat-messages" style="max-height: 400px; display:flex; flex-direction:column; gap:16px;">
                    <!-- Greeting Message -->
                    <div class="d-flex align-items-start gap-2.5 max-w-80">
                        <div class="bg-indigo text-white p-3 rounded-4 rounded-tl-0 shadow-sm" style="background: rgba(99, 102, 241, 0.12); border:1px solid rgba(99, 102, 241, 0.2);">
                            <span class="fw-semibold text-primary d-block small mb-1">StudyPilot AI</span>
                            Halo! Saya sudah membaca materi **{{ $material->judul }}**. Silakan tanyakan apa saja terkait dokumen ini, dan saya akan menjawabnya secara ringkas berdasarkan isi dokumen.
                        </div>
                    </div>
                </div>

                <!-- Input Chat -->
                <div class="mt-auto border-top border-secondary-subtle pt-3">
                    <form id="chat-form" onsubmit="sendChatMessage(event)">
                        <div class="input-group">
                            <input type="text" class="form-control bg-dark border-secondary text-white" id="chat-input" placeholder="Tanyakan tentang materi..." autocomplete="off" required>
                            <button class="btn btn-primary px-3" type="submit" id="chat-send-btn">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Marked.js for parsing markdown summary -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    // Parse Markdown Summary
    try {
        const rawSummary = {!! json_encode($material->summary) !!};
        document.getElementById('summary-content').innerHTML = marked.parse(rawSummary);
    } catch (e) {
        console.error('Failed to parse summary markdown:', e);
    }

    // Chat Materi Logic
    function sendChatMessage(event) {
        event.preventDefault();
        const input = document.getElementById('chat-input');
        const query = input.value.trim();
        if (!query) return;

        // Clear input & disable submit
        input.value = '';
        const sendBtn = document.getElementById('chat-send-btn');
        sendBtn.setAttribute('disabled', 'disabled');

        // Append User Message to Chat Log
        const chatLogs = document.getElementById('chat-messages');
        const userMsgDiv = document.createElement('div');
        userMsgDiv.style.alignSelf = 'flex-end';
        userMsgDiv.innerHTML = `
            <div class="bg-secondary text-white p-3 rounded-4 rounded-tr-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); border:1px solid rgba(255, 255, 255, 0.08);">
                <span class="fw-semibold text-secondary d-block small mb-1">Anda</span>
                ${query}
            </div>
        `;
        chatLogs.appendChild(userMsgDiv);
        chatLogs.scrollTop = chatLogs.scrollHeight;

        // Append Typing Indicator
        const typingDiv = document.createElement('div');
        typingDiv.id = 'ai-typing-indicator';
        typingDiv.innerHTML = `
            <div class="bg-indigo text-white p-3 rounded-4 rounded-tl-0 shadow-sm" style="background: rgba(99, 102, 241, 0.08); border:1px solid rgba(99, 102, 241, 0.1);">
                <span class="spinner-border spinner-border-sm text-primary me-2" role="status"></span>AI sedang mengetik...
            </div>
        `;
        chatLogs.appendChild(typingDiv);
        chatLogs.scrollTop = chatLogs.scrollHeight;

        // Call backend via AJAX
        fetch(`/knowledge-hub/{{ $material->id }}/chat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSR-Token': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: query })
        })
        .then(response => response.json())
        .then(data => {
            // Remove typing indicator
            document.getElementById('ai-typing-indicator').remove();
            sendBtn.removeAttribute('disabled');

            if (data.success) {
                // Append AI Response
                const aiMsgDiv = document.createElement('div');
                aiMsgDiv.innerHTML = `
                    <div class="bg-indigo text-white p-3 rounded-4 rounded-tl-0 shadow-sm" style="background: rgba(99, 102, 241, 0.12); border:1px solid rgba(99, 102, 241, 0.2);">
                        <span class="fw-semibold text-primary d-block small mb-1">StudyPilot AI</span>
                        ${marked.parse(data.response)}
                    </div>
                `;
                chatLogs.appendChild(aiMsgDiv);
            } else {
                // Append Error Response
                const errDiv = document.createElement('div');
                errDiv.innerHTML = `
                    <div class="bg-danger-subtle text-danger p-3 rounded-4 rounded-tl-0 shadow-sm">
                        Maaf, sistem AI mengalami gangguan dalam menjawab. Coba lagi nanti.
                    </div>
                `;
                chatLogs.appendChild(errDiv);
            }
            chatLogs.scrollTop = chatLogs.scrollHeight;
        })
        .catch(err => {
            if (document.getElementById('ai-typing-indicator')) {
                document.getElementById('ai-typing-indicator').remove();
            }
            sendBtn.removeAttribute('disabled');
            Swal.fire({
                title: 'Koneksi Terputus',
                text: 'Koneksi terputus saat menghubungi AI. Silakan coba beberapa saat lagi.',
                icon: 'error',
                confirmButtonColor: '#6366f1'
            });
        });
    }

    function showQuizLoading(btn) {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Membangun Soal Kuis AI...';
        btn.setAttribute('disabled', 'disabled');
        btn.form.submit();
    }
</script>
@endsection

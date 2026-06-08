<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            line-height: 1.6;
            font-size: 10.5pt;
        }
        .header {
            border-bottom: 2px solid #6366f1;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            color: #6366f1;
        }
        .meta-table {
            width: 100%;
            margin-top: 10px;
            font-size: 9pt;
            color: #555555;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .title {
            font-size: 16pt;
            font-weight: bold;
            color: #111111;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .content {
            margin-bottom: 40px;
        }
        .footer {
            position: fixed;
            bottom: -1cm;
            left: 0px;
            right: 0px;
            height: 1cm;
            text-align: center;
            font-size: 8pt;
            color: #888888;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
        }
        /* Markdown formatting styles */
        h1, h2, h3, h4 {
            color: #111111;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 8px;
        }
        h1 { font-size: 15pt; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        h2 { font-size: 13pt; }
        h3 { font-size: 11.5pt; }
        p {
            margin-top: 0;
            margin-bottom: 12px;
        }
        ul, ol {
            padding-left: 20px;
            margin-top: 0;
            margin-bottom: 12px;
        }
        li {
            margin-bottom: 4px;
        }
        code {
            background-color: #f3f4f6;
            padding: 2px 4px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 8.5pt;
            color: #1f2937;
        }
        pre {
            background-color: #f3f4f6;
            padding: 10px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 8.5pt;
            overflow-x: auto;
            margin-top: 0;
            margin-bottom: 12px;
            white-space: pre-wrap;
        }
        blockquote {
            border-left: 3px solid #6366f1;
            margin: 0 0 12px 0;
            background-color: #f9fafb;
            padding: 8px 12px;
            color: #4b5563;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
            font-size: 9.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 10px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #111111;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <span class="logo">StudyPilot</span>
                </td>
                <td style="text-align: right; vertical-align: middle;">
                    <span style="font-size: 9pt; color: #666666;">Academic Operating System</span>
                </td>
            </tr>
        </table>
        
        <table class="meta-table">
            <tr>
                <td style="width: 15%;"><strong>Pengguna:</strong></td>
                <td style="width: 45%;">{{ $user->name }} ({{ ucfirst($user->role === 'admin' ? 'Administrator' : ($user->education_level ?? 'mahasiswa')) }})</td>
                <td style="text-align: right; width: 40%;"><strong>Tanggal Generate:</strong> {{ $date }}</td>
            </tr>
            @if($user->jurusan)
            <tr>
                <td><strong>Jurusan:</strong></td>
                <td>{{ $user->jurusan }} @if($user->semester)(Semester {{ $user->semester }})@endif</td>
                <td></td>
            </tr>
            @endif
        </table>
    </div>

    <div class="title">{{ $title }}</div>

    <div class="content">
        {!! $htmlContent !!}
    </div>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh StudyPilot - Belajar Lebih Pintar, Tugas Lebih Teratur, Nilai Lebih Maksimal.
    </div>
</body>
</html>

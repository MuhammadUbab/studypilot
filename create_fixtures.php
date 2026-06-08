<?php
// Script to create dummy test documents: PDF, DOCX, PPTX

$fixturesDir = __DIR__ . '/tests/fixtures';
if (!file_exists($fixturesDir)) {
    mkdir($fixturesDir, 0777, true);
}

// 1. Create small.pdf (valid minimal PDF with selectable text)
$smallPdf = "%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >> /Contents 4 0 R >>
endobj
4 0 obj
<< /Length 50 >>
stream
BT
/F1 12 Tf
72 712 Td
(Ini adalah teks dalam PDF kecil untuk pengujian.) Tj
ET
endstream
endobj
xref
0 5
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
  0000000248 00000 n 
trailer
<< /Size 5 /Root 1 0 R >>
startxref
348
%%EOF\n";
file_put_contents($fixturesDir . '/small.pdf', $smallPdf);

// 2. Create scanned.pdf (valid minimal PDF with NO selectable text)
$scannedPdf = "%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << >> /Contents 4 0 R >>
endobj
4 0 obj
<< /Length 15 >>
stream
% No text stream
endstream
endobj
xref
0 5
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000199 00000 n 
trailer
<< /Size 5 /Root 1 0 R >>
startxref
264
%%EOF\n";
file_put_contents($fixturesDir . '/scanned.pdf', $scannedPdf);

// 3. Create large.pdf (larger PDF with repeated text)
$largePdfText = str_repeat("Ini adalah baris teks berulang untuk mensimulasikan file PDF berukuran besar pada modul Knowledge Hub StudyPilot. ", 1500);
$streamLength = strlen($largePdfText) + 30; // approx
$largePdf = "%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >> /Contents 4 0 R >>
endobj
4 0 obj
<< /Length " . strlen($largePdfText) . " >>
stream
BT
/F1 12 Tf
72 712 Td
(" . $largePdfText . ") Tj
ET
endstream
endobj
xref
0 5
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000248 00000 n 
trailer
<< /Size 5 /Root 1 0 R >>
startxref
" . (248 + strlen($largePdfText) + 40) . "
%%EOF\n";
file_put_contents($fixturesDir . '/large.pdf', $largePdf);

// 4. Create test.docx (valid zip file containing word/document.xml)
$docxZip = new ZipArchive();
if ($docxZip->open($fixturesDir . '/test.docx', ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
        <w:body>
            <w:p>
                <w:r>
                    <w:t>Ini adalah isi dokumen DOCX untuk pengujian integrasi StudyPilot.</w:t>
                </w:r>
            </w:p>
        </w:body>
    </w:document>';
    $docxZip->addFromString('word/document.xml', $xml);
    $docxZip->close();
}

// 5. Create test.pptx (valid zip file containing ppt/slides/slide1.xml)
$pptxZip = new ZipArchive();
if ($pptxZip->open($fixturesDir . '/test.pptx', ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">
        <p:cSld>
            <p:spTree>
                <p:sp>
                    <p:txBody>
                        <a:p>
                            <a:r>
                                <a:t>Ini adalah isi slide PPTX untuk pengujian integrasi StudyPilot.</a:t>
                            </a:r>
                        </a:p>
                    </p:txBody>
                </p:sp>
            </p:spTree>
        </p:cSld>
    </p:sld>';
    $pptxZip->addFromString('ppt/slides/slide1.xml', $xml);
    $pptxZip->close();
}

echo "Fixtures created successfully in tests/fixtures/:\n";
echo "- small.pdf: " . filesize($fixturesDir . '/small.pdf') . " bytes\n";
echo "- scanned.pdf: " . filesize($fixturesDir . '/scanned.pdf') . " bytes\n";
echo "- large.pdf: " . filesize($fixturesDir . '/large.pdf') . " bytes\n";
echo "- test.docx: " . filesize($fixturesDir . '/test.docx') . " bytes\n";
echo "- test.pptx: " . filesize($fixturesDir . '/test.pptx') . " bytes\n";

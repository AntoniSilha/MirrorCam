<?php
require __DIR__ . '/vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

try {
    // Lokasi Tesseract dan tessdata
    $ocr = new TesseractOCR(__DIR__ . '/foto_ktp_test.jpeg');
    $ocr->executable('C:/Program Files/Tesseract-OCR/tesseract.exe');
    $ocr->tessdataDir('C:/Program Files/Tesseract-OCR/tessdata');

    // Jalankan OCR
    $text = $ocr->run();

    echo "=== HASIL BACAAN OCR ===\n";
    var_dump($text);
} catch (\Exception $e) {
    echo "=== ERROR DITEMUKAN ===\n";
    echo $e->getMessage();
}

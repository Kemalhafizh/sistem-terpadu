<?php

namespace App\Jobs;

use App\Models\OcrResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOcrDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ocrResultId;

    public function __construct($ocrResultId)
    {
        $this->ocrResultId = $ocrResultId;
    }

    public function handle(): void
    {
        $ocrResult = OcrResult::find($this->ocrResultId);
        
        if (!$ocrResult) {
            return;
        }

        $ocrResult->update(['status' => 'processing']);

        try {
            // Simulate OCR Processing time and text extraction
            // In reality, this might call Tesseract or Google Cloud Vision API
            sleep(1);
            
            $simulatedExtractedText = "DOCUMENT TYPE: " . strtoupper($ocrResult->document_type) . "\n"
                                    . "This is a simulated extracted text from OCR for file: " . $ocrResult->file_path . "\n"
                                    . "STATUS: VERIFIED\n";

            $ocrResult->update([
                'extracted_text' => $simulatedExtractedText,
                'status' => 'completed',
            ]);
        } catch (\Exception $e) {
            Log::error('OCR Processing failed: ' . $e->getMessage());
            $ocrResult->update(['status' => 'failed']);
        }
    }
}

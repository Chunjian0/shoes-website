<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\CompanyProfile;
use App\Models\Purchase;
use App\Models\PurchaseSupplierItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\SignablePDF;

class PurchaseOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    private Purchase $purchase;
    private PurchaseSupplierItem $supplierItem;
    private ?CompanyProfile $company;

    public function __construct(
        Purchase $purchase, 
        PurchaseSupplierItem $supplierItem,
        ?CompanyProfile $company = null
    ) {
        $this->purchase = $purchase;
        $this->supplierItem = $supplierItem;
        
        Log::info('PurchaseOrderMailConstructor', [
            'purchase_number' => $purchase->purchase_number,
            'supplier_id' => $supplierItem->supplier_id,
            'company_provided' => $company ? true : false
        ]);

        if ($company === null) {
            $company = CompanyProfile::first();
            if (!$company) {
                Log::error('Company profile not found');
                throw new \RuntimeException('Company profile not set. Please set up company information first.');
            }
        }

        if (empty($company->company_name)) {
            Log::error('Company name not set in profile');
            throw new \RuntimeException('Company name not set. Please set up company name in profile.');
        }

        $this->company = $company;
        
        Log::info('Company information setting is completed', [
            'company_name' => $this->company->name,
            'company_data' => $this->company->toArray()
        ]);
    }

    public function build(): self
    {
        try {
            // generatePDFdocument
            $pdfPath = $this->generateSignedPdf();
            
            Log::info('Start building emails', [
                'pdf_path' => $pdfPath,
                'purchase_number' => $this->purchase->purchase_number,
                'supplier_name' => $this->supplierItem->supplier->name
            ]);

            return $this->view('emails.purchase-order')
                ->subject("Purchase Order #{$this->purchase->purchase_number}")
                ->with([
                    'purchase' => $this->purchase,
                    'supplierItem' => $this->supplierItem,
                    'company' => $this->company
                ])
                ->attach($pdfPath, [
                    'as' => $this->purchase->purchase_number . '_' . $this->supplierItem->supplier->code . '.pdf',
                    'mime' => 'application/pdf'
                ]);
        } catch (\Exception $e) {
            Log::error('Failed to build email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function generateSignedPdf(): string
    {
        try {
            Log::info('Start generating purchase ordersPDF', [
                'purchase_number' => $this->purchase->purchase_number,
                'supplier_id' => $this->supplierItem->supplier_id,
                'company_info' => [
                    'name' => $this->company->company_name ?? 'null',
                    'exists' => isset($this->company) ? 'yes' : 'no'
                ]
            ]);

            // Check the necessary data
            if (!isset($this->company)) {
                throw new \RuntimeException('Company information not set');
            }

            if (empty($this->company->company_name)) {
                throw new \RuntimeException('Company name not set. Please set up company name in profile.');
            }

            // generatePDFcontent
            $pdf = new SignablePDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            Log::info('PDFThe instance was created successfully');
            
            // Setting up document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($this->company->company_name);
            $pdf->SetTitle("Purchase Order #{$this->purchase->purchase_number}");
            
            Log::info('PDFDocument information is set successfully');
            
            // Set default fonts
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            
            // Set page margins
            $pdf->SetMargins(15, 15, 15);
            
            // Set up automatic paging
            $pdf->SetAutoPageBreak(TRUE, 25);
            
            // Set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            
            Log::info('PDFBasic settings are completed');
            
            // Add a page
            $pdf->AddPage();

            // Set fonts
            $pdf->SetFont('times', '', 11);
            
            Log::info('Prepare for renderingPDFview');

            // RenderingPDFview
            $html = view('purchases.pdf', [
                'purchase' => $this->purchase,
                'supplierItem' => $this->supplierItem,
                'company' => $this->company
            ])->render();

            Log::info('PDFThe view rendering is successful');

            // WriteHTMLcontent
            $pdf->writeHTML($html, true, false, true, false, '');
            
            Log::info('HTMLContent written successfully');

            // Add a signature area
            if (auth()->check()) {
                $pdf->addSignatureArea(auth()->user()->name);
                Log::info('Signature area added successfully');
            } else {
                Log::warning('User is not logged in, skip the signature area');
            }

            // Make sure the directory exists
            Storage::makeDirectory('public/purchases');
            
            // Generate file path
            $pdfPath = storage_path('app/public/purchases/' . $this->purchase->purchase_number . '_' . $this->supplierItem->supplier->code . '.pdf');
            
            // Make sure the directory exists
            if (!file_exists(dirname($pdfPath))) {
                mkdir(dirname($pdfPath), 0755, true);
            }
            
            Log::info('PDFFile path is ready', ['path' => $pdfPath]);

            // keepPDFdocument
            $pdf->Output($pdfPath, 'F');
            
            if (!file_exists($pdfPath)) {
                throw new \RuntimeException('PDFFile saving failed');
            }
            
            $fileSize = filesize($pdfPath);
            Log::info('PDFFile saved successfully', [
                'path' => $pdfPath,
                'size' => $fileSize
            ]);

            return $pdfPath;
        } catch (\Exception $e) {
            Log::error('PDFGeneration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'purchase_number' => $this->purchase->purchase_number,
                'supplier_id' => $this->supplierItem->supplier_id,
                'company_info' => [
                    'exists' => isset($this->company),
                    'name' => $this->company->company_name ?? 'null'
                ]
            ]);
            throw $e;
        }
    }
}
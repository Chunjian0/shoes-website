<?php

declare(strict_types=1);

namespace App\Support;

use TCPDF;

class SignablePDF extends TCPDF
{
    public function __construct()
    {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Setting up document information
        $this->SetCreator(PDF_CREATOR);
        
        // Set page margins
        $this->SetHeaderMargin(5);
        $this->SetFooterMargin(10);

        // Set up automatic paging
        $this->SetAutoPageBreak(true, 25);

        // Set fonts
        $this->SetFont('helvetica', '', 10);
    }

    /**
     * Add a digital signature area
     */
    public function addSignatureArea(string $signerName): void
    {
        // Add a signature area
        $this->setSignatureAppearance(180, $this->getPageHeight() - 60, 60, 20);
        
        // Set small fonts
        $this->SetFont('helvetica', '', 8);
        
        // Add signature time
        $this->Text(180, $this->getPageHeight() - 35, 'Signing time:' . now()->format('Y-m-d H:i:s'));
        
        // Add a signature
        $this->Text(180, $this->getPageHeight() - 30, 'Signator:' . $signerName);
    }

    /**
     * Rewrite the header
     */
    public function Header(): void
    {
        // Set fonts
        $this->SetFont('helvetica', 'B', 15);
        
        // Add a title
        $this->Cell(0, 10, 'Purchase Order', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    /**
     * Rewrite the footer
     */
    public function Footer(): void
    {
        // Set fonts
        $this->SetFont('helvetica', 'I', 8);
        
        // Add page number
        $this->Cell(0, 10, '1. ' . $this->getAliasNumPage() . ' Pages, total ' . $this->getAliasNbPages() . ' Page', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
} 
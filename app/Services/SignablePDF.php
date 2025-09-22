<?php

declare(strict_types=1);

namespace App\Services;

use TCPDF;

class SignablePDF extends TCPDF
{
    public function Header()
    {
        // Empty implementation, no default page header is displayed
    }

    public function Footer()
    {
        // Set footer
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, '1. '.$this->getAliasNumPage().' Pages, total '.$this->getAliasNbPages().' Page', 0, false, 'C');
    }

    public function addSignatureArea(string $name): void
    {
        // Add a signature area
        $this->SetY(-40);
        $this->SetFont('helvetica', '', 10);
        
        // Signature of the approver
        $this->Cell(0, 10, 'Approver:' . $name, 0, 1, 'R');
        $this->Cell(0, 10, 'date:' . now()->format('Y-m-d'), 0, 1, 'R');
    }
} 
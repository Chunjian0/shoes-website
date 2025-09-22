<?php

namespace App\Services;

use App\Models\EInvoice;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use DOMDocument;
use Exception;

class EInvoiceService
{
    protected $apiUrl;
    protected $apiKey;
    protected $apiSecret;
    protected $companyProfile;

    public function __construct()
    {
        $this->apiUrl = config('services.myinvois.url');
        $this->apiKey = config('services.myinvois.key');
        $this->apiSecret = config('services.myinvois.secret');
        $this->companyProfile = CompanyProfile::first();
    }

    /**
     * 从销售订单创建电子发票
     */
    public function createFromOrder(SalesOrder $order): EInvoice
    {
        // 检查订单是否已有发票
        if ($order->eInvoice) {
            return $order->eInvoice;
        }

        // 创建新发票
        $invoice = new EInvoice([
            'invoice_number' => EInvoice::generateInvoiceNumber(),
            'sales_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'store_id' => $order->store_id,
            'user_id' => auth()->id(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => $order->total_amount,
            'tax_amount' => $order->tax_amount,
            'status' => 'draft',
        ]);

        $invoice->save();

        return $invoice;
    }

    /**
     * 生成发票XML数据
     */
    public function generateXml(EInvoice $invoice): string
    {
        $order = $invoice->order;
        $customer = $invoice->customer;
        $store = $invoice->store;
        $company = $this->companyProfile;

        // 创建XML文档
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // 创建根元素
        $root = $dom->createElement('Invoice');
        $root->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $root->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $root->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $dom->appendChild($root);

        // 添加发票基本信息
        $this->addElement($dom, $root, 'cbc:ID', $invoice->invoice_number);
        $this->addElement($dom, $root, 'cbc:IssueDate', $invoice->invoice_date->format('Y-m-d'));
        $this->addElement($dom, $root, 'cbc:InvoiceTypeCode', 380); // 商业发票代码
        $this->addElement($dom, $root, 'cbc:DocumentCurrencyCode', 'MYR'); // 马来西亚林吉特

        // 添加卖方信息
        $accountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
        $root->appendChild($accountingSupplierParty);
        
        $party = $dom->createElement('cac:Party');
        $accountingSupplierParty->appendChild($party);
        
        $partyName = $dom->createElement('cac:PartyName');
        $party->appendChild($partyName);
        $this->addElement($dom, $partyName, 'cbc:Name', $company->name);
        
        $postalAddress = $dom->createElement('cac:PostalAddress');
        $party->appendChild($postalAddress);
        $this->addElement($dom, $postalAddress, 'cbc:StreetName', $company->address);
        $this->addElement($dom, $postalAddress, 'cbc:CityName', $company->city);
        $this->addElement($dom, $postalAddress, 'cbc:PostalZone', $company->postal_code);
        $this->addElement($dom, $postalAddress, 'cbc:CountrySubentity', $company->state);
        
        $country = $dom->createElement('cac:Country');
        $postalAddress->appendChild($country);
        $this->addElement($dom, $country, 'cbc:IdentificationCode', 'MY');
        
        $partyTaxScheme = $dom->createElement('cac:PartyTaxScheme');
        $party->appendChild($partyTaxScheme);
        $this->addElement($dom, $partyTaxScheme, 'cbc:CompanyID', $company->tax_id);
        
        $taxScheme = $dom->createElement('cac:TaxScheme');
        $partyTaxScheme->appendChild($taxScheme);
        $this->addElement($dom, $taxScheme, 'cbc:ID', 'SST');

        // 添加买方信息
        $accountingCustomerParty = $dom->createElement('cac:AccountingCustomerParty');
        $root->appendChild($accountingCustomerParty);
        
        $party = $dom->createElement('cac:Party');
        $accountingCustomerParty->appendChild($party);
        
        $partyName = $dom->createElement('cac:PartyName');
        $party->appendChild($partyName);
        $this->addElement($dom, $partyName, 'cbc:Name', $customer->name);
        
        if ($customer->address) {
            $postalAddress = $dom->createElement('cac:PostalAddress');
            $party->appendChild($postalAddress);
            $this->addElement($dom, $postalAddress, 'cbc:StreetName', $customer->address);
            
            $country = $dom->createElement('cac:Country');
            $postalAddress->appendChild($country);
            $this->addElement($dom, $country, 'cbc:IdentificationCode', 'MY');
        }
        
        if ($customer->tax_id) {
            $partyTaxScheme = $dom->createElement('cac:PartyTaxScheme');
            $party->appendChild($partyTaxScheme);
            $this->addElement($dom, $partyTaxScheme, 'cbc:CompanyID', $customer->tax_id);
            
            $taxScheme = $dom->createElement('cac:TaxScheme');
            $partyTaxScheme->appendChild($taxScheme);
            $this->addElement($dom, $taxScheme, 'cbc:ID', 'SST');
        }

        // 添加付款条款
        $paymentTerms = $dom->createElement('cac:PaymentTerms');
        $root->appendChild($paymentTerms);
        $this->addElement($dom, $paymentTerms, 'cbc:Note', 'Payment due within 30 days');

        // 添加税务总计
        if ($invoice->tax_amount > 0) {
            $taxTotal = $dom->createElement('cac:TaxTotal');
            $root->appendChild($taxTotal);
            $this->addElement($dom, $taxTotal, 'cbc:TaxAmount', number_format($invoice->tax_amount, 2, '.', ''));
            
            $taxSubtotal = $dom->createElement('cac:TaxSubtotal');
            $taxTotal->appendChild($taxSubtotal);
            $this->addElement($dom, $taxSubtotal, 'cbc:TaxableAmount', number_format($invoice->total_amount - $invoice->tax_amount, 2, '.', ''));
            $this->addElement($dom, $taxSubtotal, 'cbc:TaxAmount', number_format($invoice->tax_amount, 2, '.', ''));
            
            $taxCategory = $dom->createElement('cac:TaxCategory');
            $taxSubtotal->appendChild($taxCategory);
            $this->addElement($dom, $taxCategory, 'cbc:Percent', '6');
            
            $taxScheme = $dom->createElement('cac:TaxScheme');
            $taxCategory->appendChild($taxScheme);
            $this->addElement($dom, $taxScheme, 'cbc:ID', 'SST');
        }

        // 添加发票总计
        $legalMonetaryTotal = $dom->createElement('cac:LegalMonetaryTotal');
        $root->appendChild($legalMonetaryTotal);
        $this->addElement($dom, $legalMonetaryTotal, 'cbc:LineExtensionAmount', number_format($invoice->total_amount - $invoice->tax_amount, 2, '.', ''));
        $this->addElement($dom, $legalMonetaryTotal, 'cbc:TaxExclusiveAmount', number_format($invoice->total_amount - $invoice->tax_amount, 2, '.', ''));
        $this->addElement($dom, $legalMonetaryTotal, 'cbc:TaxInclusiveAmount', number_format($invoice->total_amount, 2, '.', ''));
        $this->addElement($dom, $legalMonetaryTotal, 'cbc:PayableAmount', number_format($invoice->total_amount, 2, '.', ''));

        // 添加发票行项目
        $lineNumber = 1;
        foreach ($order->items as $orderItem) {
            $invoiceLine = $dom->createElement('cac:InvoiceLine');
            $root->appendChild($invoiceLine);
            
            $this->addElement($dom, $invoiceLine, 'cbc:ID', $lineNumber++);
            $this->addElement($dom, $invoiceLine, 'cbc:InvoicedQuantity', $orderItem->quantity);
            $this->addElement($dom, $invoiceLine, 'cbc:LineExtensionAmount', number_format($orderItem->subtotal, 2, '.', ''));
            
            $itemElement = $dom->createElement('cac:Item');
            $invoiceLine->appendChild($itemElement);
            $this->addElement($dom, $itemElement, 'cbc:Name', $orderItem->product_name);
            $this->addElement($dom, $itemElement, 'cbc:Description', $orderItem->product_name);
            
            if ($orderItem->product && $orderItem->product->sku) {
                $sellersItemIdentification = $dom->createElement('cac:SellersItemIdentification');
                $itemElement->appendChild($sellersItemIdentification);
                $this->addElement($dom, $sellersItemIdentification, 'cbc:ID', $orderItem->product->sku);
            }
            
            $price = $dom->createElement('cac:Price');
            $invoiceLine->appendChild($price);
            $this->addElement($dom, $price, 'cbc:PriceAmount', number_format($orderItem->unit_price, 2, '.', ''));
        }

        // 返回XML字符串
        return $dom->saveXML();
    }

    /**
     * 添加XML元素
     */
    private function addElement(DOMDocument $dom, $parent, $name, $value): void
    {
        $element = $dom->createElement($name, $value);
        $parent->appendChild($element);
    }

    /**
     * 生成发票PDF
     */
    public function generatePdf(EInvoice $invoice): string
    {
        // 这里使用您现有的PDF生成逻辑
        // 例如使用TCPDF或DomPDF库
        
        // 示例代码，实际实现需要根据您的PDF生成方式调整
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Optic System');
        $pdf->SetAuthor($this->companyProfile->name);
        $pdf->SetTitle('Invoice #' . $invoice->invoice_number);
        $pdf->SetSubject('Invoice');
        $pdf->SetKeywords('Invoice, E-Invoice, MyInvois');
        
        // 设置页眉和页脚
        $pdf->setHeaderFont(Array('helvetica', '', 10));
        $pdf->setFooterFont(Array('helvetica', '', 8));
        $pdf->SetHeaderData('', 0, $this->companyProfile->name, 'Invoice #' . $invoice->invoice_number);
        
        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');
        
        // 设置边距
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // 设置自动分页
        $pdf->SetAutoPageBreak(TRUE, 25);
        
        // 设置图像比例因子
        $pdf->setImageScale(1.25);
        
        // 添加页面
        $pdf->AddPage();
        
        // 添加发票内容
        $html = view('invoices.pdf', ['invoice' => $invoice])->render();
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // 如果有QR码，添加QR码
        if ($invoice->qr_code) {
            $pdf->write2DBarcode($invoice->qr_code, 'QRCODE,L', 160, 20, 30, 30);
        }
        
        // 保存PDF文件
        $filename = 'invoice_' . $invoice->invoice_number . '.pdf';
        $path = 'invoices/' . $filename;
        Storage::put('public/' . $path, $pdf->Output($filename, 'S'));
        
        // 更新发票记录
        $invoice->pdf_path = $path;
        $invoice->save();
        
        return $path;
    }

    /**
     * 提交发票到MyInvois API
     */
    public function submitToApi(EInvoice $invoice): array
    {
        try {
            // 生成XML数据
            $xmlData = $this->generateXml($invoice);
            
            // 保存XML数据到发票记录
            $invoice->xml_data = $xmlData;
            $invoice->submission_count += 1;
            $invoice->last_submitted_at = now();
            $invoice->save();
            
            // 准备API请求数据
            $requestData = [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
                'invoice_data' => base64_encode($xmlData),
                'company_id' => $this->companyProfile->tax_id,
                'invoice_number' => $invoice->invoice_number,
            ];
            
            // 发送API请求
            $response = Http::post($this->apiUrl . '/submit_invoice', $requestData);
            
            // 处理响应
            if ($response->successful()) {
                $responseData = $response->json();
                
                // 更新发票记录
                $invoice->status = 'submitted';
                $invoice->submission_id = $responseData['submission_id'] ?? null;
                $invoice->qr_code = $responseData['qr_code'] ?? null;
                $invoice->response_data = $responseData;
                $invoice->error_message = null;
                $invoice->save();
                
                // 生成PDF
                $this->generatePdf($invoice);
                
                return [
                    'success' => true,
                    'message' => 'Invoice submitted successfully',
                    'data' => $responseData
                ];
            } else {
                // 处理错误
                $errorData = $response->json();
                
                // 更新发票记录
                $invoice->status = 'error';
                $invoice->error_message = $errorData['message'] ?? 'API request failed';
                $invoice->response_data = $errorData;
                $invoice->save();
                
                return [
                    'success' => false,
                    'message' => $errorData['message'] ?? 'API request failed',
                    'data' => $errorData
                ];
            }
        } catch (Exception $e) {
            // 记录异常
            Log::error('E-Invoice API submission error: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'exception' => $e
            ]);
            
            // 更新发票记录
            $invoice->status = 'error';
            $invoice->error_message = $e->getMessage();
            $invoice->save();
            
            return [
                'success' => false,
                'message' => 'Exception occurred: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * 检查发票状态
     */
    public function checkStatus(EInvoice $invoice): array
    {
        try {
            // 准备API请求数据
            $requestData = [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
                'submission_id' => $invoice->submission_id,
                'company_id' => $this->companyProfile->tax_id,
            ];
            
            // 发送API请求
            $response = Http::post($this->apiUrl . '/check_status', $requestData);
            
            // 处理响应
            if ($response->successful()) {
                $responseData = $response->json();
                
                // 更新发票记录
                $status = $responseData['status'] ?? 'unknown';
                if ($status === 'approved') {
                    $invoice->status = 'approved';
                } elseif ($status === 'rejected') {
                    $invoice->status = 'rejected';
                    $invoice->error_message = $responseData['reason'] ?? 'Rejected by MyInvois';
                }
                
                $invoice->response_data = array_merge($invoice->response_data ?? [], $responseData);
                $invoice->save();
                
                return [
                    'success' => true,
                    'message' => 'Status checked successfully',
                    'data' => $responseData
                ];
            } else {
                // 处理错误
                $errorData = $response->json();
                
                return [
                    'success' => false,
                    'message' => $errorData['message'] ?? 'API request failed',
                    'data' => $errorData
                ];
            }
        } catch (Exception $e) {
            // 记录异常
            Log::error('E-Invoice status check error: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'exception' => $e
            ]);
            
            return [
                'success' => false,
                'message' => 'Exception occurred: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
} 
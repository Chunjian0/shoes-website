<?php

namespace App\Exceptions;

use Exception;

class StockUnavailableException extends Exception
{
    protected $productId;
    protected $requestedQuantity;

    public function __construct($message = "Stock is unavailable for the requested product or specification.", $productId = null, $requestedQuantity = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->productId = $productId;
        $this->requestedQuantity = $requestedQuantity;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function getRequestedQuantity()
    {
        return $this->requestedQuantity;
    }
} 
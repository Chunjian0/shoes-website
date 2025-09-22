<?php
// Simple API Test Script - Multi-Endpoint

// CORRECT Base URL confirmed by user
$baseUrl = 'http://localhost:2268/api'; 

$endpoints = [
    '/homepage/data',         // <<<--- KEY: Aggregated homepage data
];

// --- Optional: Add more specific template/product IDs if 1 doesn't exist ---
// '/product-templates/YOUR_VALID_TEMPLATE_ID', 
// '/products/YOUR_VALID_PRODUCT_ID',

header('Content-Type: text/plain');

echo "Starting API Tests (Base URL: " . $baseUrl . ")\n";
echo "==================================================\n\n";

foreach ($endpoints as $endpoint) {
    // Skip if endpoint is empty (e.g., commented out optional IDs)
    if (empty($endpoint) || !is_string($endpoint) || $endpoint === '/') continue; 

    $url = $baseUrl . $endpoint;
    echo "Testing endpoint: " . $endpoint . "\n";
    echo "URL: " . $url . "\n";

    // Use stream context to potentially get headers (like status code)
    $startTime = microtime(true); 
    $contextOptions = [
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\n",
            'ignore_errors' => true // Allows reading response body even on 4xx/5xx errors
        ],
        // Disable SSL verification for localhost testing if needed (use with caution)
        // 'ssl' => [
        //     'verify_peer' => false,
        //     'verify_peer_name' => false,
        // ],
    ];
    $context = stream_context_create($contextOptions);

    // Use error suppression (@) as file_get_contents can throw warnings on failure
    $response = @file_get_contents($url, false, $context);
    $endTime = microtime(true); 
    $duration = round(($endTime - $startTime) * 1000, 2); // Duration in milliseconds

    // $http_response_header is magically populated by file_get_contents
    $statusCode = 'N/A';
    if (isset($http_response_header) && is_array($http_response_header) && count($http_response_header) > 0) {
        // Extract status code (e.g., "HTTP/1.1 200 OK")
        if (preg_match('{HTTP/\d\.\d\s+(\d+)}', $http_response_header[0], $match)) {
            $statusCode = (int)$match[1];
        }
    }

    if ($response === false) {
        echo "Duration: " . $duration . " ms\n"; 
        $error = error_get_last();
        echo "Status: FAILED\n";
        // Check for specific connection errors
        if (strpos($error['message'] ?? '', 'Connection refused') !== false) {
             echo "Error: Connection Refused. Is the API server running at " . parse_url($url, PHP_URL_HOST) . ":" . parse_url($url, PHP_URL_PORT) . "?\n";
        } elseif (strpos($error['message'] ?? '', 'Failed to open stream') !== false) {
             echo "Error: Failed to open stream. Check Base URL and endpoint path.\n";
             echo "Details: " . ($error['message'] ?? 'Unknown stream error') . "\n";
        } else {
            echo "Error: Request failed. " . ($error['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "Duration: " . $duration . " ms\n"; 
        echo "Status Code: " . $statusCode . "\n";
        $decoded = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            echo "Response Type: JSON (Decoded Successfully)\n";
            if (isset($decoded['success'])) {
                echo "API Success Field: " . ($decoded['success'] ? 'true' : 'false') . "\n";
            }
             if (isset($decoded['message'])) {
                echo "API Message: " . $decoded['message'] . "\n";
            }
            // Optionally print small part of the response data for verification
            if (isset($decoded['data']) && is_array($decoded['data'])) {
                 echo "Data Snippet Keys: " . implode(', ', array_keys($decoded['data'])) . "\n";
                 // Print the full data content for detailed inspection
                 echo "Full Response Data:\n";
                 // Use json_encode with pretty print for better readability
                 echo json_encode($decoded['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                 echo "\n";
            }

        } else {
            echo "Response Type: Non-JSON or Decode Error (" . json_last_error_msg() . ")\n";
            echo "Raw Response Snippet: " . substr($response, 0, 200) . "...\n";
        }
    }
    echo "--------------------------------------------------\n\n";
    // Clear headers for next request
    unset($http_response_header);
}

echo "API Tests Finished.\n";

?> 
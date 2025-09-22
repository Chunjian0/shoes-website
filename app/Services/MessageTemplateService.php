<?php

namespace App\Services;

use App\Models\MessageTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class MessageTemplateService
{
    /**
     * Cache key prefix for templates
     */
    protected string $cacheKeyPrefix = 'message_template_';
    
    /**
     * Cache TTL in seconds (1 day)
     */
    protected int $cacheTtl = 86400;
    
    /**
     * Get a template by name and type
     *
     * @param string $name Template name
     * @param string $channel Channel (email, sms, etc)
     * @return MessageTemplate|null
     */
    public function getTemplate(string $name, string $channel = 'email'): ?MessageTemplate
    {
        $cacheKey = $this->getCacheKey($name, $channel);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($name, $channel) {
            return MessageTemplate::where('name', $name)
                ->where('channel', $channel)
                ->where('status', 'active')
                ->first();
        });
    }
    
    /**
     * Check if a template exists
     *
     * @param string $name Template name
     * @param string $channel Channel (email, sms, etc)
     * @return bool
     */
    public function templateExists(string $name, string $channel = 'email'): bool
    {
        return $this->getTemplate($name, $channel) !== null;
    }
    
    /**
     * Parse a template with variables
     *
     * @param MessageTemplate $template
     * @param array $variables
     * @return array Parsed content with subject and body
     */
    public function parseTemplate(MessageTemplate $template, array $variables): array
    {
        $subject = $this->parseContent($template->subject, $variables);
        $content = $this->parseContent($template->content, $variables);
        
        return [
            'subject' => $subject,
            'content' => $content
        ];
    }
    
    /**
     * Parse content with variables using template syntax
     *
     * @param string $content The template content
     * @param array $variables The variables to inject
     * @return string The parsed content
     */
    public function parseContent(string $content, array $variables): string
    {
        // Handle @foreach loops
        $content = preg_replace_callback(
            '/@foreach\s*\((\w+)\s+as\s+(\w+)\)(.*?)@endforeach/s',
            function ($matches) use ($variables) {
                $arrayName = trim($matches[1]);
                $itemName = trim($matches[2]);
                $loopContent = $matches[3];
                
                if (!isset($variables[$arrayName]) || !is_array($variables[$arrayName])) {
                    return '';
                }
                
                $result = '';
                foreach ($variables[$arrayName] as $item) {
                    $itemVariables = [$itemName => $item];
                    $parsedContent = $this->parseBasicVariables($loopContent, $itemVariables);
                    $result .= $parsedContent;
                }
                
                return $result;
            },
            $content
        );
        
        // Handle basic variables
        return $this->parseBasicVariables($content, $variables);
    }
    
    /**
     * Parse basic {{ variable }} syntax
     *
     * @param string $content
     * @param array $variables
     * @return string
     */
    private function parseBasicVariables(string $content, array $variables): string
    {
        return preg_replace_callback(
            '/\{\{\s*([^{}]+?)\s*\}\}/',
            function ($matches) use ($variables) {
                $variable = trim($matches[1]);
                
                // Handle nested object properties with dot notation
                if (strpos($variable, '.') !== false) {
                    $parts = explode('.', $variable);
                    $value = $variables;
                    
                    foreach ($parts as $part) {
                        if (is_array($value) && isset($value[$part])) {
                            $value = $value[$part];
                        } else {
                            return '';
                        }
                    }
                    
                    return $this->formatValue($value);
                }
                
                // Handle direct variable access
                return isset($variables[$variable]) ? $this->formatValue($variables[$variable]) : '';
            },
            $content
        );
    }
    
    /**
     * Format a value for template output
     *
     * @param mixed $value
     * @return string
     */
    private function formatValue($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        return (string) $value;
    }
    
    /**
     * Generate a cache key for a template
     *
     * @param string $name
     * @param string $channel
     * @return string
     */
    private function getCacheKey(string $name, string $channel): string
    {
        return $this->cacheKeyPrefix . $name . '_' . $channel;
    }
    
    /**
     * Clear the template cache
     *
     * @param string|null $name
     * @param string|null $channel
     * @return void
     */
    public function clearCache(?string $name = null, ?string $channel = null): void
    {
        if ($name !== null && $channel !== null) {
            $cacheKey = $this->getCacheKey($name, $channel);
            Cache::forget($cacheKey);
        } else {
            // Clear all template caches
            $cachePattern = $this->cacheKeyPrefix . '*';
            $cacheKeys = Cache::getMultiple([$cachePattern]);
            
            foreach (array_keys($cacheKeys) as $key) {
                if (strpos($key, $this->cacheKeyPrefix) === 0) {
                    Cache::forget($key);
                }
            }
        }
    }
    
    /**
     * Send an email using a template
     *
     * @param string $templateName Template name
     * @param string|array $to Recipient email(s)
     * @param array $variables Variables to use in the template
     * @param array $attachments Optional file attachments
     * @return bool Success status
     */
    public function sendEmail(string $templateName, $to, array $variables = [], array $attachments = []): bool
    {
        try {
            // Get the template
            $template = $this->getTemplate($templateName, 'email');
            
            if (!$template) {
                Log::error("Email template not found: {$templateName}");
                return false;
            }
            
            // Parse the template
            $parsed = $this->parseTemplate($template, $variables);
            
            // Convert single email to array
            $recipients = is_array($to) ? $to : [$to];
            
            // Send the email
            Mail::html($parsed['content'], function ($message) use ($recipients, $parsed, $attachments) {
                $message->to($recipients)
                    ->subject($parsed['subject']);
                
                // Add attachments if any
                foreach ($attachments as $attachment) {
                    if (isset($attachment['path']) && file_exists($attachment['path'])) {
                        $message->attach($attachment['path'], [
                            'as' => $attachment['as'] ?? basename($attachment['path']),
                            'mime' => $attachment['mime'] ?? null,
                        ]);
                    }
                }
            });
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email template: {$e->getMessage()}");
            return false;
        }
    }
} 
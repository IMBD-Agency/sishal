<?php

namespace App\Traits;

trait SanitizesHtml
{
    /**
     * Sanitize HTML content to prevent malicious redirects and scripts.
     * 
     * @param string|null $content
     * @return string|null
     */
    public function sanitizeSecureHtml($content)
    {
        if (empty($content)) {
            return $content;
        }

        // 1. Block Meta Refresh redirects (common in the reported attack)
        $content = preg_replace('/<meta\s+http-equiv=["\']refresh["\'][^>]*>/i', '<!-- blocked meta refresh -->', $content);
        
        // 2. Block Script tags entirely
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "<!-- blocked script -->", $content);
        
        // 3. Block inline event handlers (onmouseover, onclick, etc.)
        $content = preg_replace('/(\s)on[a-z]+\s*=\s*["\'][^"\']*["\']/i', '$1blocked_event_handler=""', $content);
        $content = preg_replace('/(\s)on[a-z]+\s*=\s*[^"\']\s+/i', '$1blocked_event_handler="" ', $content);

        // 4. Block javascript: pseudo-protocol in links
        $content = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', 'href="javascript:void(0)"', $content);

        return $content;
    }

    /**
     * Automatically sanitize specified fields on saving.
     */
    protected static function bootSanitizesHtml()
    {
        static::saving(function ($model) {
            $fieldsToSanitize = $model->getSanitizableFields();
            foreach ($fieldsToSanitize as $field) {
                if (isset($model->{$field})) {
                    $model->{$field} = $model->sanitizeSecureHtml($model->{$field});
                }
            }
        });
    }

    /**
     * Default list of fields to sanitize. 
     * Can be overridden in the model.
     */
    public function getSanitizableFields()
    {
        return property_exists($this, 'sanitizable') ? $this->sanitizable : [];
    }
}

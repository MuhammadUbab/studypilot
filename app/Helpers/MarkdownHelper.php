<?php

namespace App\Helpers;

class MarkdownHelper
{
    public static function toHtml(string $markdown): string
    {
        // Normalize line endings
        $markdown = str_replace("\r\n", "\n", $markdown);

        // Blockquotes (handle multi-line blockquotes)
        $markdown = preg_replace('/^> (.*?)$/m', '<blockquote>$1</blockquote>', $markdown);
        // Merge consecutive blockquotes
        $markdown = preg_replace('/<\/blockquote>\n<blockquote>/', '<br>', $markdown);
        
        // Remove GitHub callout syntax if any e.g. [!NOTE]
        $markdown = preg_replace('/\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]/', '', $markdown);

        // Headers
        $markdown = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $markdown);
        $markdown = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $markdown);
        $markdown = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $markdown);

        // Bold & Italic
        $markdown = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $markdown);
        $markdown = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $markdown);

        // Code blocks
        $markdown = preg_replace('/```(.*?)\n(.*?)```/s', '<pre><code>$2</code></pre>', $markdown);
        $markdown = preg_replace('/`(.*?)`/', '<code>$1</code>', $markdown);

        // Unordered lists
        $markdown = preg_replace('/^\* (.*?)$/m', '<li>$1</li>', $markdown);
        $markdown = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $markdown);
        // Wrap <li> elements that are not already inside a ul/ol list
        $markdown = preg_replace('/(<li>.*?<\/li>)+/s', '<ul>$0</ul>', $markdown);

        // Clean up empty lines or multiple line breaks
        $html = nl2br($markdown);
        
        // Remove duplicate newlines inside block elements to prevent huge spaces
        $html = preg_replace('/<(ul|li|blockquote|h1|h2|h3|pre|code)>(.*?)<br\s*\/?>/i', '<$1>$2', $html);
        $html = preg_replace('/<br\s*\/?>\s*<\/(ul|li|blockquote|h1|h2|h3|pre|code)>/i', '</$1>', $html);

        return $html;
    }
}

<?php

namespace App\Helpers;

class TitleFormatter
{
    /**
     * Format a title to Title Case
     * Converts the first letter of each word to uppercase
     * Handles special cases like apostrophes and multiple spaces
     * 
     * @param string $title
     * @return string
     */
    public static function formatTitle($title)
    {
        if (empty($title)) {
            return $title;
        }

        // Trim whitespace
        $title = trim($title);
        
        // Replace multiple spaces with single space
        $title = preg_replace('/\s+/', ' ', $title);
        
        // Split into words
        $words = explode(' ', $title);
        
        // Capitalize first letter of each word
        $formattedWords = array_map(function($word) {
            // Handle empty strings
            if (empty($word)) {
                return $word;
            }
            
            // Get first character and rest of the word
            $firstChar = mb_substr($word, 0, 1);
            $rest = mb_substr($word, 1);
            
            // Capitalize first character and keep rest lowercase
            return mb_strtoupper($firstChar) . mb_strtolower($rest);
        }, $words);
        
        // Join words back together
        return implode(' ', $formattedWords);
    }

    /**
     * Format a description to Title Case
     * Formats each sentence in the description with Title Case
     * Handles multiple sentences separated by periods, exclamation marks, or question marks
     * 
     * @param string $description
     * @return string
     */
    public static function formatDescription($description)
    {
        if (empty($description)) {
            return $description;
        }

        // Trim whitespace
        $description = trim($description);
        
        // Replace multiple spaces with single space
        $description = preg_replace('/\s+/', ' ', $description);
        
        // Split by sentence endings (. ! ?) while preserving the punctuation
        // This regex splits on sentence endings but keeps the punctuation
        $sentences = preg_split('/([.!?]+)/', $description, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        $formattedSentences = [];
        $i = 0;
        
        while ($i < count($sentences)) {
            $sentence = trim($sentences[$i]);
            
            // Skip if it's just punctuation
            if (preg_match('/^[.!?]+$/', $sentence)) {
                $formattedSentences[] = $sentence;
                $i++;
                continue;
            }
            
            // Format the sentence with Title Case
            $formattedSentence = self::formatTitle($sentence);
            
            // Add punctuation if the next element is punctuation
            if ($i + 1 < count($sentences) && preg_match('/^[.!?]+$/', $sentences[$i + 1])) {
                $formattedSentence .= $sentences[$i + 1];
                $i += 2;
            } else {
                $i++;
            }
            
            $formattedSentences[] = $formattedSentence;
        }
        
        // Join sentences back together
        $result = implode(' ', $formattedSentences);
        
        // Clean up any extra spaces
        $result = preg_replace('/\s+/', ' ', $result);
        
        return trim($result);
    }
}


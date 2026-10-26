<?php

namespace App\Support;

use App\Models\Message;
use Illuminate\Support\Str;

class ChatMessageModeration
{
    /** @return array{allowed: bool, words: array<int, string>} */
    public function inspect(string $content): array
    {
        $normalized = $this->normalize($content);
        $matched = [];

        foreach (Message::getBadWords() as $word) {
            $word = $this->normalize($word);
            if ($word === '') {
                continue;
            }

            $parts = preg_split('/\s+/', $word) ?: [];
            $pattern = implode('[^a-z0-9]+', array_map(
                static fn (string $part): string => implode('[^a-z0-9]*', str_split($part)),
                $parts
            ));

            if (preg_match('/(?<![a-z0-9])' . $pattern . '(?![a-z0-9])/i', $normalized)) {
                $matched[] = $word;
            }
        }

        $matched = array_values(array_unique($matched));
        if ($matched === [] || $this->isClearlyNonAbusiveContext($normalized, $matched)) {
            return ['allowed' => true, 'words' => $matched];
        }

        return ['allowed' => false, 'words' => $matched];
    }

    private function normalize(string $text): string
    {
        $text = strtolower(Str::ascii($text));
        $text = strtr($text, ['0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '@' => 'a', '$' => 's']);

        return preg_replace('/(.)\1{2,}/', '$1$1', $text) ?? $text;
    }

    /**
     * Permite citar un insulto únicamente al reportar un mensaje recibido y
     * permite el uso coloquial de “marica” como muletilla, no como ataque.
     */
    private function isClearlyNonAbusiveContext(string $text, array $matched): bool
    {
        if (preg_match('/\b(eres|sos|usted es|maldito|callate|te voy a|voy a acabar|ojala te)\b/', $text)) {
            return false;
        }

        if (count($matched) === 1 && $matched[0] === 'marica'
            && preg_match('/^\s*marica[,\s]+(ya voy|ya llego|mira|imaginate|no sabia|que pena|jaja|gracias|hola)\b/', $text)) {
            return true;
        }

        if (count($matched) !== 1) {
            return false;
        }

        $term = preg_quote($matched[0], '/');
        $reportedMessage = '/\b(?:me|nos|le|les)\s+(?:dijo|escribio|mando|envio|llamo|insulto|amenazo)\b[^.!?\n]{0,35}\b' . $term . '\b/';
        $formalReport = '/\b(?:reporto|denuncio|quiero reportar|quiero denunciar|recibi un mensaje)\b[^.!?\n]{0,80}\b' . $term . '\b/';

        return (bool) (preg_match($reportedMessage, $text) || preg_match($formalReport, $text));
    }
}

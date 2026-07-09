<?php

namespace App\Services;

use App\Models\Category;

class MessageParser
{
    public function parse(string $text): array
    {
        $text = trim(preg_replace('/^rp\s*/i', '', $text));
        $text = trim(preg_replace('/\s+/', ' ', $text));

        $amount = $this->extractAmount($text);
        $remaining = $amount ? trim(preg_replace('/^[\d.,\s]+/', '', $text)) : $text;
        $remaining = trim(preg_replace('/\s+/', ' ', $remaining));

        if (!$amount) {
            $categoryName = $this->matchCategory($text);
            return [
                'amount' => null,
                'category' => $categoryName ?: $text,
                'description' => null,
            ];
        }

        if (empty($remaining)) {
            return ['amount' => $amount, 'category' => null, 'description' => null];
        }

        $categoryName = $this->matchCategory($remaining);

        if ($categoryName) {
            $description = $this->stripCategory($remaining, $categoryName);
        } else {
            $description = $remaining;
        }

        return ['amount' => $amount, 'category' => $categoryName, 'description' => $description];
    }

    public function matchCategory(string $text): ?string
    {
        $text = trim($text);
        if ($text === '') return null;

        $categories = Category::all();
        $lower = mb_strtolower($text);
        $firstWord = mb_strtolower(explode(' ', $text)[0]);

        // 1 — Exact match
        foreach ($categories as $c) {
            if (mb_strtolower($c->name) === $lower) {
                return $c->name;
            }
        }

        // 2 — Text starts with category name
        foreach ($categories as $c) {
            $cLower = mb_strtolower($c->name);
            if (mb_strpos($lower, $cLower) === 0) {
                return $c->name;
            }
        }

        // 3 — First word matches category prefix (makan → Makanan)
        $best = null;
        $bestLen = 0;
        foreach ($categories as $c) {
            $cLower = mb_strtolower($c->name);
            if (mb_strpos($cLower, $firstWord) === 0 && mb_strlen($firstWord) >= 3) {
                if (mb_strlen($cLower) > $bestLen) {
                    $best = $c->name;
                    $bestLen = mb_strlen($cLower);
                }
            }
        }
        if ($best) return $best;

        // 4 — Category name is a substring of text (any position)
        foreach ($categories as $c) {
            if (mb_strlen($c->name) <= 3) continue;
            if (mb_stripos($text, $c->name) !== false) {
                return $c->name;
            }
        }

        // 5 — First word of text is a substring of category name
        foreach ($categories as $c) {
            $cLower = mb_strtolower($c->name);
            if (mb_strlen($firstWord) >= 3 && mb_stripos($cLower, $firstWord) !== false) {
                return $c->name;
            }
        }

        return null;
    }

    private function stripCategory(string $text, string $categoryName): ?string
    {
        // Try removing full category name (case-insensitive)
        $result = trim(str_ireplace($categoryName, '', $text));
        if ($result !== '' && $result !== $text) {
            return $result ?: null;
        }

        // Try removing first word (prefix match case: "makan" → Makanan)
        $words = explode(' ', trim($text));
        if (count($words) > 1) {
            array_shift($words);
            $result = trim(implode(' ', $words));
            return $result ?: null;
        }

        return null;
    }

    private function extractAmount(string $text): ?int
    {
        // Remove dots used as thousand separators, then extract digits
        $clean = str_replace('.', '', $text);
        $clean = str_replace(',', '', $clean);
        preg_match('/\d{1,12}/', $clean, $m);
        return isset($m[0]) ? (int) $m[0] : null;
    }
}

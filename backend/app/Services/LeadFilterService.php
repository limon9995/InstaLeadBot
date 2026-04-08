<?php

namespace App\Services;

/**
 * Analyzes raw scraped data and determines:
 *  - Whether the lead is crypto-related
 *  - The lead's probable country
 *  - The lead's probable gender
 *
 * Returns null if the lead doesn't pass filters.
 */
class LeadFilterService
{
    // ─── Crypto Keywords ─────────────────────────────────────────────────

    private const CRYPTO_KEYWORDS = [
        'crypto', 'bitcoin', 'btc', 'ethereum', 'eth', 'forex', 'trading',
        'trader', 'investor', 'defi', 'nft', 'blockchain', 'altcoin',
        'hodl', 'binance', 'coinbase', 'fx', 'signals', 'technical analysis',
        'ta', 'market', 'charts', 'portfolio', 'stonks', 'web3',
    ];

    // ─── Country Detection ────────────────────────────────────────────────

    private const COUNTRY_MAP = [
        // USA
        'USA'           => 'USA',
        'US'            => 'USA',
        'United States' => 'USA',
        'New York'      => 'USA',
        'Los Angeles'   => 'USA',
        'California'    => 'USA',
        'Texas'         => 'USA',
        '🇺🇸'          => 'USA',

        // UK
        'UK'            => 'UK',
        'United Kingdom'=> 'UK',
        'England'       => 'UK',
        'London'        => 'UK',
        'Britain'       => 'UK',
        '🇬🇧'          => 'UK',

        // Germany
        'Germany'       => 'Germany',
        'Deutschland'   => 'Germany',
        'Berlin'        => 'Germany',
        'DE'            => 'Germany',
        '🇩🇪'          => 'Germany',

        // Canada
        'Canada'        => 'Canada',
        'Toronto'       => 'Canada',
        'Vancouver'     => 'Canada',
        'CA'            => 'Canada',
        '🇨🇦'          => 'Canada',

        // Australia
        'Australia'     => 'Australia',
        'Sydney'        => 'Australia',
        'Melbourne'     => 'Australia',
        'AU'            => 'Australia',
        '🇦🇺'          => 'Australia',

        // Brazil
        'Brazil'        => 'Brazil',
        'Brasil'        => 'Brazil',
        'São Paulo'     => 'Brazil',
        'BR'            => 'Brazil',
        '🇧🇷'          => 'Brazil',

        // UAE
        'UAE'           => 'UAE',
        'Dubai'         => 'UAE',
        'Abu Dhabi'     => 'UAE',
        '🇦🇪'          => 'UAE',

        // Switzerland
        'Switzerland'   => 'Switzerland',
        'Zurich'        => 'Switzerland',
        'Geneva'        => 'Switzerland',
        'CH'            => 'Switzerland',
        '🇨🇭'          => 'Switzerland',
    ];

    // ─── Gender Detection ─────────────────────────────────────────────────

    /** Common male pronouns / indicators in bios */
    private const MALE_PRONOUNS = ['he/him', 'he / him', 'his', 'he|him'];

    /** Partial list of common male first names */
    private const MALE_NAMES = [
        'james', 'john', 'robert', 'michael', 'william', 'david', 'richard',
        'joseph', 'thomas', 'charles', 'daniel', 'matthew', 'andrew', 'kevin',
        'mark', 'paul', 'steven', 'edward', 'christopher', 'jason', 'ryan',
        'jacob', 'gary', 'nicholas', 'eric', 'jonathan', 'stephen', 'larry',
        'justin', 'scott', 'brandon', 'frank', 'benjamin', 'gregory', 'sam',
        'raymond', 'patrick', 'jack', 'dennis', 'jerry', 'alexander', 'tyler',
        'henry', 'adam', 'douglas', 'nathan', 'peter', 'zachary', 'kyle',
        'noah', 'liam', 'mason', 'ethan', 'oliver', 'elijah', 'aiden',
        'lucas', 'logan', 'carter', 'cam', 'ali', 'ahmed', 'omar', 'hassan',
        'mike', 'chris', 'alex', 'max', 'joe', 'tom', 'matt', 'dave',
        'bob', 'rick', 'jim', 'andy', 'rob', 'jay', 'ron', 'dan', 'ken',
        'brad', 'chad', 'bro', 'dude', 'guy',
    ];

    /** Female indicator names/pronouns — used to exclude */
    private const FEMALE_INDICATORS = [
        'she/her', 'she / her', 'her/hers', 'mom', 'mother', 'wife',
        'girl', 'woman', 'female', 'lady', 'queen',
    ];

    // ─── Public API ───────────────────────────────────────────────────────

    /**
     * Filter a raw lead array from the scraper.
     *
     * @param array $raw  ['username', 'bio', 'source_keyword', ...]
     * @return array|null Enriched lead data, or null if filtered out.
     */
    public function filter(array $raw): ?array
    {
        $bio     = $raw['bio'] ?? '';
        $username = strtolower($raw['username'] ?? '');

        // 1. Must be crypto-related
        if (! $this->isCryptoRelated($bio)) {
            return null;
        }

        // 2. Country detection
        $country = $this->detectCountry($bio);
        if (! $country) {
            return null; // Only target known countries
        }

        // 3. Gender detection
        $gender = $this->detectGender($bio, $username);
        if ($gender !== 'male') {
            return null; // Only target probable males
        }

        // 4. Score and tag
        $score = $this->computeScore($bio, $country);
        $tag   = $this->computeTag($score);

        return array_merge($raw, [
            'country' => $country,
            'gender'  => $gender,
            'score'   => $score,
            'tag'     => $tag,
        ]);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function isCryptoRelated(string $bio): bool
    {
        $bioLower = strtolower($bio);
        foreach (self::CRYPTO_KEYWORDS as $keyword) {
            if (str_contains($bioLower, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function detectCountry(string $bio): ?string
    {
        foreach (self::COUNTRY_MAP as $indicator => $country) {
            // Case-insensitive match for text, exact for emojis
            if (mb_strlen($indicator) <= 4) {
                // Short codes and emojis: exact match
                if (str_contains($bio, $indicator)) {
                    return $country;
                }
            } else {
                // Longer strings: case-insensitive
                if (stripos($bio, $indicator) !== false) {
                    return $country;
                }
            }
        }
        return null;
    }

    private function detectGender(string $bio, string $username): string
    {
        $haystack = strtolower($bio . ' ' . $username);

        // Explicit female indicators → not male
        foreach (self::FEMALE_INDICATORS as $indicator) {
            if (str_contains($haystack, $indicator)) {
                return 'female';
            }
        }

        // Explicit male pronouns
        foreach (self::MALE_PRONOUNS as $pronoun) {
            if (str_contains($haystack, $pronoun)) {
                return 'male';
            }
        }

        // Name-based inference: check first word of username
        $firstWord = explode('_', $username)[0];
        $firstWord = preg_replace('/[^a-z]/', '', $firstWord);
        if (in_array($firstWord, self::MALE_NAMES, true)) {
            return 'male';
        }

        // Check bio first word
        $bioWords = preg_split('/\s+/', strtolower(trim($bio)));
        if (! empty($bioWords[0])) {
            $firstName = preg_replace('/[^a-z]/', '', $bioWords[0]);
            if (in_array($firstName, self::MALE_NAMES, true)) {
                return 'male';
            }
        }

        return 'unknown';
    }

    private function computeScore(string $bio, string $country): int
    {
        $score   = 0;
        $bioLower = strtolower($bio);

        // Crypto keyword density
        foreach (self::CRYPTO_KEYWORDS as $keyword) {
            if (str_contains($bioLower, $keyword)) {
                $score += 10;
            }
        }

        // Premium countries get bonus points
        $premiumCountries = ['USA', 'UK', 'Switzerland', 'UAE'];
        if (in_array($country, $premiumCountries, true)) {
            $score += 20;
        }

        // Bio length bonus (more info = more legit)
        if (mb_strlen($bio) > 60) {
            $score += 10;
        }

        return min($score, 100);
    }

    private function computeTag(int $score): string
    {
        if ($score >= 60) return 'hot';
        if ($score >= 30) return 'warm';
        return 'cold';
    }
}

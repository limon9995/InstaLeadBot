<?php

namespace App\Services;

/**
 * LeadFilterService
 * -----------------
 * Filters raw scraped Instagram data.
 *
 * Rules:
 *  1. Must be crypto/forex/trading related
 *  2. Must be from BRAZIL only
 *  3. Must be probable MALE
 *
 * Extracts:
 *  - Country (Brazil detection)
 *  - Gender  (name + pronoun based)
 *  - Age     (from bio text or date-of-birth)
 *  - Job     (from bio emojis / keywords)
 *  - Score & Tag (hot / warm / cold)
 */
class LeadFilterService
{
    // ─── Crypto Keywords ──────────────────────────────────────────────────

    private const CRYPTO_KEYWORDS = [
        'crypto', 'bitcoin', 'btc', 'ethereum', 'eth', 'forex', 'trading',
        'trader', 'investor', 'defi', 'nft', 'blockchain', 'altcoin',
        'hodl', 'binance', 'coinbase', 'fx', 'signals', 'web3',
        'technical analysis', 'ta', 'market', 'portfolio',
        'criptomoeda', 'cripto', 'investidor', 'mercado', // Portuguese (Brazil)
    ];

    // ─── Brazil Indicators ────────────────────────────────────────────────

    private const BRAZIL_INDICATORS = [
        '🇧🇷', 'brazil', 'brasil', 'br',
        'são paulo', 'sao paulo', 'rio de janeiro', 'rio',
        'belo horizonte', 'brasília', 'brasilia', 'curitiba',
        'fortaleza', 'manaus', 'salvador', 'recife', 'porto alegre',
        'florianópolis', 'florianopolis', 'campinas', 'goiânia', 'goiania',
    ];

    // ─── Male Indicators ─────────────────────────────────────────────────

    private const MALE_PRONOUNS = ['he/him', 'he / him', 'his', 'ele/dele', 'ele'];

    private const MALE_NAMES = [
        // International
        'james','john','robert','michael','william','david','richard','joseph',
        'thomas','charles','daniel','matthew','andrew','kevin','mark','paul',
        'steven','edward','christopher','jason','ryan','jacob','gary','nicholas',
        'eric','jonathan','stephen','larry','justin','scott','brandon','frank',
        'benjamin','gregory','sam','raymond','patrick','jack','dennis','jerry',
        'alexander','tyler','henry','adam','douglas','nathan','peter','zachary',
        'kyle','noah','liam','mason','ethan','oliver','elijah','aiden','lucas',
        'logan','carter','mike','chris','alex','max','joe','tom','matt','dave',
        'bob','rick','jim','andy','rob','jay','ron','dan','ken','brad','chad',
        // Brazilian / Portuguese male names
        'joao','joão','pedro','lucas','gabriel','mateus','matheus','rafael',
        'felipe','gustavo','rodrigo','andre','andrê','carlos','paulo','sergio',
        'sérgio','marcelo','leandro','thiago','diego','leonardo','caio','vitor',
        'victor','henrique','fernando','guilherme','julio','júlio','fabio','fábio',
        'renato','marcos','eduardo','anderson','bruno','cesar','césar','davi',
        'enzo','igor','ivan','jorge','luiz','luis','marco','murilo','neymar',
        'nilton','oscar','óscar','otavio','otávio','renan','ruan','tiago',
        'wagner','walter','willian','william','yago','yuri',
    ];

    private const FEMALE_INDICATORS = [
        'she/her','she / her','her/hers','ela/dela','ela',
        'mom','mother','wife','girl','woman','female','lady','queen',
        'mãe','esposa','mulher','namorada',
    ];

    // ─── Job / Profession Patterns ───────────────────────────────────────

    private const JOB_EMOJIS = ['💼', '🏢', '👨‍💼', '👔', '🧑‍💻', '👨‍💻'];

    private const JOB_KEYWORDS = [
        'ceo', 'cto', 'cfo', 'founder', 'co-founder', 'cofounder',
        'director', 'manager', 'engineer', 'developer', 'designer',
        'analyst', 'consultant', 'advisor', 'trader', 'broker',
        'entrepreneur', 'investor', 'freelancer', 'coach', 'mentor',
        // Portuguese
        'fundador', 'cofundador', 'diretor', 'gerente', 'engenheiro',
        'desenvolvedor', 'analista', 'consultor', 'empresário', 'investidor',
        'empreendedor', 'corretor', 'contador',
    ];

    // ─── Public API ───────────────────────────────────────────────────────

    public function filter(array $raw): ?array
    {
        $bio      = $raw['bio'] ?? '';
        $username = strtolower($raw['username'] ?? '');

        // 1. Must be crypto-related
        if (! $this->isCryptoRelated($bio)) {
            return null;
        }

        // 2. Must be Brazil
        $country = $this->detectCountry($bio);
        if ($country !== 'Brazil') {
            return null;
        }

        // 3. Must be male
        $gender = $this->detectGender($bio, $username);
        if ($gender !== 'male') {
            return null;
        }

        // 4. Extract extra info
        $age  = $this->extractAge($bio);
        $job  = $this->extractJob($bio);

        // 5. Score & Tag
        $score = $this->computeScore($bio, $age, $job);
        $tag   = $this->computeTag($score);

        return array_merge($raw, [
            'country' => $country,
            'gender'  => $gender,
            'age'     => $age,
            'job'     => $job,
            'score'   => $score,
            'tag'     => $tag,
        ]);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function isCryptoRelated(string $bio): bool
    {
        $lower = strtolower($bio);
        foreach (self::CRYPTO_KEYWORDS as $kw) {
            if (str_contains($lower, $kw)) return true;
        }
        return false;
    }

    private function detectCountry(string $bio): ?string
    {
        $lower = strtolower($bio);
        foreach (self::BRAZIL_INDICATORS as $indicator) {
            if (str_contains($bio, $indicator) || str_contains($lower, strtolower($indicator))) {
                return 'Brazil';
            }
        }
        return null;
    }

    private function detectGender(string $bio, string $username): string
    {
        $haystack = strtolower($bio . ' ' . $username);

        foreach (self::FEMALE_INDICATORS as $fi) {
            if (str_contains($haystack, $fi)) return 'female';
        }

        foreach (self::MALE_PRONOUNS as $mp) {
            if (str_contains($haystack, $mp)) return 'male';
        }

        // Check first segment of username
        $firstWord = preg_replace('/[^a-z]/', '', explode('_', $username)[0]);
        if (in_array($firstWord, self::MALE_NAMES, true)) return 'male';

        // Check first word of bio
        $bioFirst = preg_replace('/[^a-z]/', '', strtolower(explode(' ', trim($bio))[0] ?? ''));
        if (in_array($bioFirst, self::MALE_NAMES, true)) return 'male';

        return 'unknown';
    }

    /**
     * Extract age from bio.
     * Handles: "25 years old", "25yo", "25 y/o", "Age: 25",
     *           "Born: 1998", "Born in 1998", "🎂 1995", "DOB: 01/01/1998"
     */
    private function extractAge(string $bio): ?int
    {
        $currentYear = (int) date('Y');

        // Direct age: "25 years old", "25yo", "25 y/o", "25 anos"
        if (preg_match('/\b(\d{2})\s*(?:years?\s*old|yo|y\/o|anos?)\b/i', $bio, $m)) {
            $age = (int) $m[1];
            if ($age >= 18 && $age <= 80) return $age;
        }

        // "Age: 25" or "Age 25"
        if (preg_match('/\bage[:\s]+(\d{2})\b/i', $bio, $m)) {
            $age = (int) $m[1];
            if ($age >= 18 && $age <= 80) return $age;
        }

        // Birth year: "Born in 1995", "Born: 1995", "🎂 1995"
        if (preg_match('/(?:born\s*(?:in|:)?\s*|🎂\s*)(\d{4})/i', $bio, $m)) {
            $age = $currentYear - (int) $m[1];
            if ($age >= 16 && $age <= 80) return $age;
        }

        // DOB with date: "DOB: 15/06/1998" or "15-06-1998"
        if (preg_match('/\bDOB[:\s]+\d{1,2}[\/\-\.]\d{1,2}[\/\-\.](\d{4})/i', $bio, $m)) {
            $age = $currentYear - (int) $m[1];
            if ($age >= 16 && $age <= 80) return $age;
        }

        // Just a 4-digit birth year in range 1970–2005
        if (preg_match('/\b(19[7-9]\d|200[0-5])\b/', $bio, $m)) {
            $age = $currentYear - (int) $m[1];
            if ($age >= 18 && $age <= 55) return $age;
        }

        return null;
    }

    /**
     * Extract job/profession from bio.
     * Handles: emoji 💼, keywords like "CEO at...", "Software Engineer"
     */
    private function extractJob(string $bio): ?string
    {
        // Emoji-prefixed job line (e.g. "💼 CEO at Binance")
        foreach (self::JOB_EMOJIS as $emoji) {
            if (str_contains($bio, $emoji)) {
                // Grab text after emoji until newline or |
                if (preg_match('/' . preg_quote($emoji, '/') . '\s*([^\n|📍🌍🌎🌐]{3,60})/u', $bio, $m)) {
                    $job = trim($m[1]);
                    if (strlen($job) >= 3) return $job;
                }
            }
        }

        // Keyword-based: "CEO at Company", "Trader | Binance"
        $lower = strtolower($bio);
        foreach (self::JOB_KEYWORDS as $kw) {
            if (str_contains($lower, $kw)) {
                // Try to grab the phrase containing the keyword
                if (preg_match('/([A-Za-z\s]{2,30}' . preg_quote($kw, '/') . '[A-Za-z\s@]{0,30})/i', $bio, $m)) {
                    $job = trim($m[1]);
                    if (strlen($job) >= 3 && strlen($job) <= 80) return ucfirst(strtolower($job));
                }
                return ucfirst($kw);
            }
        }

        return null;
    }

    private function computeScore(string $bio, ?int $age, ?string $job): int
    {
        $score = 0;
        $lower = strtolower($bio);

        // Crypto keyword density
        foreach (self::CRYPTO_KEYWORDS as $kw) {
            if (str_contains($lower, $kw)) $score += 8;
        }

        // Bonus for age (active working age)
        if ($age !== null && $age >= 20 && $age <= 45) $score += 15;

        // Bonus for known job
        if ($job !== null) $score += 15;

        // Bio length (more info = more real)
        if (mb_strlen($bio) > 80) $score += 10;

        return min($score, 100);
    }

    private function computeTag(int $score): string
    {
        if ($score >= 60) return 'hot';
        if ($score >= 30) return 'warm';
        return 'cold';
    }
}

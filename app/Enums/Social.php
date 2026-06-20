<?php

declare(strict_types=1);

namespace App\Enums;

enum Social: string
{
    case Twitter = 'twitter';
    case LinkedIn = 'linkedin';
    case GitHub = 'github';
    case StackOverflow = 'stackoverflow';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case YouTube = 'youtube';
    case WhatsApp = 'whatsapp';
    case Website = 'website';

    public static function getSocialFromUrl(string $url): self
    {
        return match (true) {
            str_contains($url, 'twitter.com'),
            str_contains($url, 'x.com') => self::Twitter,
            str_contains($url, 'linkedin.com') => self::LinkedIn,
            str_contains($url, 'github.com') => self::GitHub,
            str_contains($url, 'stackoverflow.com') => self::StackOverflow,
            str_contains($url, 'facebook.com') => self::Facebook,
            str_contains($url, 'instagram.com') => self::Instagram,
            str_contains($url, 'youtube.com') => self::YouTube,
            str_contains($url, 'whatsapp.com') => self::WhatsApp,
            default => self::Website,
        };
    }
}

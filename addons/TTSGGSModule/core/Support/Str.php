<?php

namespace ModulesGarden\TTSGGSModule\Core\Support;

use Illuminate\Support\Collection;

/**
 * @method static of(string $string): \Illuminate\Support\Stringable
 * @method static after(string $subject, string $search): string
 * @method static afterLast(string $subject, string $search): string
 * @method static before(string $subject, string $search): string
 * @method static beforeLast(string $subject, string $search): string
 * @method static between(string $subject, string $from, string  $to): string
 * @method static ascii(string $value, string $language): string
 * @method static camel(string $value): string
 * @method static contains(string $haystack, string|string[] $needles): bool
 * @method static containsAll(string $haystack, string[] $needles): bool
 * @method static endsWith(string $haystack, string|string[] $needles): bool
 * @method static finish(string $value, string $cap): string
 * @method static is(string|array $pattern, string $value): bool
 * @method static isAscii(string $value): bool
 * @method static isUuid(string $value): bool
 * @method static kebab(string $value): string
 * @method static length(string $value, string|null $encoding): int
 * @method static limit(string $value, int $limit, string $end): string
 * @method static lower(string $value): string
 * @method static words(string $value, int $words, string $end): string
 * @method static parseCallback(string $callback, string|null $default): array<int, string|null>
 * @method static plural(string $value, int $count): string
 * @method static pluralStudly(string $value, int $count): string
 * @method static random(int $length): string
 * @method static replaceArray(string $search, array $replace, string $subject): string
 * @method static replaceFirst(string $search, string $replace, string $subject): string
 * @method static replaceLast(string $search, string $replace, string $subject): string
 * @method static start(string $value, string $prefix): string
 * @method static upper(string $value): string
 * @method static title(string $value): string
 * @method static singular(string $value): string
 * @method static slug(string $title, string $separator, string|null $language): string
 * @method static snake(string $value, string $delimiter): string
 * @method static startsWith(string $haystack, string|string[] $needles): bool
 * @method static studly(string $value): string
 * @method static substr(string $string, int $start, int|null $length): string
 * @method static substrCount(string $haystack, string $needle, int $offset, int|null $length): int
 * @method static ucfirst(string $value): string
 * @method static uuid(): \Ramsey\Uuid\UuidInterface
 * @method static orderedUuid(): \Ramsey\Uuid\UuidInterface
 * @method static createUuidsUsing(callable|null $factory): void
 * @method static createUuidsNormally(): void
 */
class Str extends \Illuminate\Support\Str
{
    /**
     * Generate a random, secure password.
     *
     * @param  int  $length
     * @param  bool  $letters
     * @param  bool  $numbers
     * @param  bool  $symbols
     * @param  bool  $spaces
     * @return string
     */
    public static function password($length = 32, $letters = true, $numbers = true, $symbols = true, $spaces = false)
    {
        return (new Collection)
            ->when($letters, fn ($c) => $c->merge([
                'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
                'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
                'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
                'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
                'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            ]))
            ->when($numbers, fn ($c) => $c->merge([
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            ]))
            ->when($symbols, fn ($c) => $c->merge([
                '~', '!', '#', '$', '%', '^', '&', '*', '(', ')', '-',
                '_', '.', ',', '<', '>', '?', '/', '\\', '{', '}', '[',
                ']', '|', ':', ';',
            ]))
            ->when($spaces, fn ($c) => $c->merge([' ']))
            ->pipe(fn ($c) => Collection::times($length, fn () => $c[random_int(0, $c->count() - 1)]))
            ->implode('');
    }

}
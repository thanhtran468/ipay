<?php

namespace IPay\Captcha;

use Symfony\Component\DomCrawler\Crawler;

final class CaptchaSolver
{
    private const LENGTH_TO_NUMBER_MAPPING = [
        66 => 0,
        49 => 1,
        53 => 2,
        70 => 3,
        76 => 4,
        58 => 5,
        64 => 6,
        50 => 7,
        75 => 8,
        69 => 9,
    ];

    public static function solve(string $svg): string
    {
        /** @var string[] */
        $svgPathCommands = (new Crawler($svg))
            ->filterXPath('//path[@fill!="none"]')
            ->each(function (Crawler $node): string {
                return (string) $node->attr('d');
            });

        sort($svgPathCommands, SORT_NATURAL);

        $result = '';
        foreach ($svgPathCommands as $svgPathCommand) {
            /** @var string */
            $symbolicPath = preg_replace('/[^A-Z]/', '', $svgPathCommand);
            $result .= static::LENGTH_TO_NUMBER_MAPPING[
                strlen($symbolicPath)
            ];
        }

        return $result;
    }
}

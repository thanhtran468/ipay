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
        $pathCommands = (new Crawler($svg))
            ->filterXPath('//path[@fill!="none"]')
            ->each(function (Crawler $node): string {
                return (string) $node->attr('d');
            });

        sort($pathCommands, SORT_NATURAL);

        $result = '';
        foreach ($pathCommands as $pathCommand) {
            /**
             * Only keep path commands.
             *
             * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/d#path_commands
             *
             * @var string
             */
            $paths = preg_replace('/[^MLHVCSQTAZmlhvcsqtaz]/', '', $pathCommand);
            $result .= static::LENGTH_TO_NUMBER_MAPPING[strlen($paths)];
        }

        return $result;
    }
}

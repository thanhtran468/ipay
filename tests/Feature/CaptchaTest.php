<?php
use IPay\Captcha\CaptchaSolver;

test('captcha solver', function (string $filename, string $result) {
    $svg = file_get_contents($filename);
    expect(CaptchaSolver::solve($svg))->toBe($result);
})->with([
    'captcha 1' => [__DIR__.'/Fixture/1.svg', '540701'],
    'captcha 2' => [__DIR__.'/Fixture/2.svg', '826778'],
    'captcha 3' => [__DIR__.'/Fixture/3.svg', '835697'],
]);

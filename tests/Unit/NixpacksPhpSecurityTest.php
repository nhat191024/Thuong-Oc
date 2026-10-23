<?php

use Symfony\Component\Process\Process;

it('pins the PHP runtime and Composer to the patched PHP 8.4 package set', function () {
    $configuration = file_get_contents(dirname(__DIR__, 2).'/nixpacks.toml');

    expect($configuration)
        ->toContain('nixpkgsArchive = "ee62357e90778a37aa7aa4a646e7ccd4fdb643b3"')
        ->toContain('(php84.withExtensions')
        ->toContain('"php84Packages.composer"');
});

it('stops the build when PHP is older than the security release', function (string $version, int $expectedExitCode) {
    $configuration = file_get_contents(dirname(__DIR__, 2).'/nixpacks.toml');

    expect(preg_match('/^    "(php -r .+)",$/m', $configuration, $matches))->toBe(1);

    $command = json_decode('"'.$matches[1].'"', true, 512, JSON_THROW_ON_ERROR);
    $script = str_replace('PHP_VERSION', var_export($version, true), substr($command, 8, -1));
    $process = new Process([PHP_BINARY, '-r', $script]);

    expect($process->run())->toBe($expectedExitCode);

    if ($expectedExitCode !== 0) {
        expect($process->getErrorOutput())->toContain('PHP 8.4.27 or newer is required for security fixes.');
    }
})->with([
    'previous runtime' => ['8.4.24', 1],
    'last vulnerable release' => ['8.4.26', 1],
    'patched release' => ['8.4.27', 0],
    'later patch release' => ['8.4.28', 0],
]);

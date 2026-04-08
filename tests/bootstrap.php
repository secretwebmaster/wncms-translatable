<?php

$autoloadCandidates = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
];

foreach ($autoloadCandidates as $autoloadCandidate) {
    if (file_exists($autoloadCandidate)) {
        require_once $autoloadCandidate;
        break;
    }
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Wncms\\Translatable\\Tests\\' => __DIR__ . '/',
    ];

    foreach ($prefixes as $prefix => $basePath) {
        if (! str_starts_with($class, $prefix)) {
            continue;
        }

        $relativePath = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $file = $basePath . $relativePath;

        if (is_file($file)) {
            require_once $file;
        }
    }
});

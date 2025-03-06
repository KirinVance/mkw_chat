<?php

function loadEnv() {
    $filePath = "../.env";
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode(' = ', $line, 2);
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

function logToFile(string $fileName, $data) {
    $file = fopen("../logs/".$fileName.".log", "a");
    fwrite($file, $data."\n");
    fclose($file);
}

function jsonResponse(array $data = [], int $status = 200) {
    echo json_encode(['response' => $data, 'status' => $status]);
    die();
}

<?php
// hleb/test_path.php
echo "Attempting to resolve: /hleb/public" . PHP_EOL;
$publicPath = realpath(__DIR__ . '/public'); // __DIR__ здесь будет /hleb
if ($publicPath === false) {
    echo "realpath(__DIR__ . '/public') returned false!" . PHP_EOL;
} else {
    echo "realpath(__DIR__ . '/public') returned: " . $publicPath . PHP_EOL;
}
var_dump($publicPath);
<?php
$c = file_get_contents(__DIR__ . '/../app/Http/Controllers/Api/V1/ProductController.php');
echo 'opens:' . substr_count($c, '{') . "\n";
echo 'closes:' . substr_count($c, '}') . "\n";

<?php
$lines = explode("\n", file_get_contents('e:/Nexa_Project/resources/views/settings/app/global.blade.php'));
foreach ($lines as $k => $v) {
    if (strpos($v, 'storageAudioRef') !== false) {
        echo ($k+1) . ': ' . trim($v) . "\n";
    }
    if (strpos($v, 'storage.refFromURL') !== false) {
        echo ($k+1) . ': ' . trim($v) . "\n";
    }
}

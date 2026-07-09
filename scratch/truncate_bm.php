<?php
$file = 'e:/Nexa_Project/resources/views/settings/app/businessModel.blade.php';
$lines = explode("\n", file_get_contents($file));
$keep = array_slice($lines, 0, 372); // Keep first 372 lines (1-indexed 1 to 372)
file_put_contents($file, implode("\n", $keep));
echo "Successfully truncated leftover JS code.";

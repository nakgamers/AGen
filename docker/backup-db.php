<?php
// Consistent online backup of the production SQLite database, with rotation.
$dir = '/data/backups';
if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}
$target = $dir . '/database-' . date('Ymd-His') . '.sqlite';
$db = new PDO('sqlite:/data/database.sqlite');
$db->exec("VACUUM INTO '" . str_replace("'", "''", $target) . "'");
$files = glob($dir . '/database-*.sqlite') ?: [];
usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
foreach (array_slice($files, 14) as $old) {
    unlink($old);
}
echo 'backup ok: ' . $target . PHP_EOL;

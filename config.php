<?php

const DB_NAME = 'if0_43408288_db';
   const DB_USER = 'if0_43408288';
   const DB_PASS = 'i hid it';

function db() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=sql201.infinityfree.com;dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    }
    return $pdo;
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Delta State is state_id 25 in this database
function lgas() {
    return db()->query("SELECT lga_id, lga_name FROM lga WHERE state_id = 25 ORDER BY lga_name")->fetchAll();
}

function top($title, $here) {
    $tabs = ['index.php' => 'Polling unit', 'lga.php' => 'LGA totals', 'add.php' => 'Add a result'];
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . e($title) . ' · Delta Tally</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@100..125,400..800&family=Newsreader:opsz,wght@6..72,400..700&display=swap">';
    echo '<link rel="stylesheet" href="assets/style.css"></head><body>';
    echo '<header class="hero"><div class="wrap"><p class="kicker">Delta State, 2011 elections</p><h1>Delta Tally</h1></div></header>';
    echo '<div class="adire"></div><nav class="wrap">';
    foreach ($tabs as $file => $label) {
        echo '<a href="' . $file . '"' . ($file === $here ? ' aria-current="page"' : '') . '>' . $label . '</a>';
    }
    echo '</nav><main class="wrap">';
}
function bottom() {
    echo '</main><div class="adire"></div><footer class="wrap">Results come from the bincom_test database.</footer><script src="assets/app.js"></script></body></html>';
}


function bars($rows) {
    if (!$rows) return;
    $max = max(1, (int)$rows[0]['total']);
    echo '<ol class="tally">';
    foreach ($rows as $i => $r) {
        $w = round(((int)$r['total'] / $max) * 100);
        echo '<li class="' . ($i === 0 ? 'win' : '') . '"><b>' . e($r['party_abbreviation']) . '</b>';
        echo '<span class="bar"><i style="width:' . $w . '%"></i></span><em>' . number_format((int)$r['total']) . '</em></li>';
    }
    echo '</ol>';
}

<?php
// Feeds the chained dropdowns: LGA polling units, or LGA  wards
require 'config.php';
header('Content-Type: application/json');
$lga = (int)($_GET['lga'] ?? 0);
if (($_GET['type'] ?? '') === 'wards') {
    $sql = "SELECT uniqueid AS id, ward_name AS label FROM ward WHERE lga_id = ? ORDER BY ward_name";
} else {
    $sql = "SELECT uniqueid AS id, CONCAT(COALESCE(polling_unit_name, 'Unnamed unit'), ' (', COALESCE(polling_unit_number, 'no number'), ')') AS label
            FROM polling_unit WHERE lga_id = ? ORDER BY polling_unit_name";
}
$st = db()->prepare($sql);
$st->execute([$lga]);
echo json_encode($st->fetchAll());

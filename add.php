<?php
// QUESTION 3: create a new polling unit and store the score of ALL parties for it
require 'config.php';
$parties = db()->query("SELECT partyid FROM party ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lga = (int)($_POST['lga'] ?? 0);
    $ward = (int)($_POST['ward'] ?? 0);
    $name = trim($_POST['unit_name'] ?? '');
    $agent = trim($_POST['agent'] ?? '');
    $scores = $_POST['scores'] ?? [];
    $st = db()->prepare("SELECT ward_id, uniqueid FROM ward WHERE uniqueid = ? AND lga_id = ?");
    $st->execute([$ward, $lga]);
    $w = $st->fetch();
    $clean = [];
    foreach ($parties as $p) {
        $v = $scores[$p] ?? '';
        if (!ctype_digit((string)$v)) { $error = 'Enter a whole number (0 or more) for every party.'; break; }
        $clean[$p] = (int)$v;
    }
    if (!$error && (!$w || $name === '' || $agent === '')) $error = 'Choose an LGA and a ward, then fill in the unit name and your name.';
    if (!$error) {
        try {
            $pdo = db();
            $pdo->beginTransaction();
            $next = $pdo->prepare("SELECT COALESCE(MAX(polling_unit_id), 0) + 1 FROM polling_unit WHERE lga_id = ?");
            $next->execute([$lga]);
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $pdo->prepare("INSERT INTO polling_unit (polling_unit_id, ward_id, lga_id, uniquewardid, polling_unit_name, entered_by_user, date_entered, user_ip_address)
                           VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)")
                ->execute([$next->fetchColumn(), $w['ward_id'], $lga, $w['uniqueid'], $name, $agent, $ip]);
            $newId = $pdo->lastInsertId();
            $ins = $pdo->prepare("INSERT INTO announced_pu_results (polling_unit_uniqueid, party_abbreviation, party_score, entered_by_user, date_entered, user_ip_address)
                                  VALUES (?, ?, ?, ?, NOW(), ?)");
            foreach ($clean as $p => $score) $ins->execute([$newId, substr($p, 0, 4), $score, $agent, $ip]); // column holds 4 letters, so LABOUR is stored as LABO like the existing rows
            $pdo->commit();
            header('Location: index.php?pu=' . $newId);
            exit;
        } catch (Exception $ex) {
            if (db()->inTransaction()) db()->rollBack();
            $error = 'Nothing was saved because the database refused the entry. Check config.php and try again.';
        }
    }
}
top('Add a result', 'add.php');
?>
<h2>Record a new polling unit’s result</h2>
<?php if ($error) echo '<p class="note err" role="alert">' . e($error) . '</p>'; ?>
<form method="post" class="sheet">
  <label>Local government
    <select id="lga" name="lga" required><option value="">Choose an LGA</option>
      <?php foreach (lgas() as $l) echo '<option value="' . $l['lga_id'] . '"' . (($_POST['lga'] ?? '') == $l['lga_id'] ? ' selected' : '') . '>' . e($l['lga_name']) . '</option>'; ?>
    </select></label>
  <label>Ward
    <select id="sub" name="ward" data-kind="wards" data-sel="<?= e($_POST['ward'] ?? '') ?>" required disabled><option>Pick an LGA first</option></select></label>
  <label>New polling unit name <input name="unit_name" maxlength="50" value="<?= e($_POST['unit_name'] ?? '') ?>" required></label>
  <label>Your name <input name="agent" maxlength="50" value="<?= e($_POST['agent'] ?? '') ?>" required></label>
  <fieldset><legend>Votes per party</legend>
    <?php foreach ($parties as $p): ?>
      <label class="vote"><b><?= e($p) ?></b><input type="number" name="scores[<?= e($p) ?>]" min="0" step="1" value="<?= e($_POST['scores'][$p] ?? '') ?>" required></label>
    <?php endforeach; ?>
  </fieldset>
  <button>Save result</button>
</form>
<?php bottom(); ?>

<?php
// QUESTION 1: show the result of any single polling unit
require 'config.php';
$id = (int)($_GET['pu'] ?? 0);
$unit = null; $rows = [];
if ($id) {
    $st = db()->prepare("SELECT p.lga_id, p.polling_unit_name, p.polling_unit_number, l.lga_name
                         FROM polling_unit p JOIN lga l ON l.lga_id = p.lga_id WHERE p.uniqueid = ? AND l.state_id = 25");
    $st->execute([$id]);
    $unit = $st->fetch();
    $st = db()->prepare("SELECT party_abbreviation, party_score AS total FROM announced_pu_results
                         WHERE polling_unit_uniqueid = ? ORDER BY party_score DESC");
    $st->execute([$id]);
    $rows = $st->fetchAll();
}
top('Polling unit result', 'index.php');
?>
<h2>See how one polling unit voted</h2>
<form method="get" class="sheet">
  <label>Local government
    <select id="lga" data-sel="<?= e($unit['lga_id'] ?? '') ?>"><option value="">Choose an LGA</option>
      <?php foreach (lgas() as $l) echo '<option value="' . $l['lga_id'] . '"' . (($unit['lga_id'] ?? 0) == $l['lga_id'] ? ' selected' : '') . '>' . e($l['lga_name']) . '</option>'; ?>
    </select></label>
  <label>Polling unit
    <select id="sub" name="pu" data-kind="units" data-sel="<?= $id ?: '' ?>" disabled><option>Pick an LGA first</option></select></label>
  <button>Show result</button>
</form>
<?php if ($id && !$unit): ?>
  <p class="note">We couldn’t find that polling unit. Pick one from the list above.</p>
<?php elseif ($unit): ?>
  <section class="result">
    <h3><?= e($unit['polling_unit_name'] ?: 'Unnamed unit') ?></h3>
    <p><?= e($unit['lga_name']) ?> · unit code <?= e($unit['polling_unit_number'] ?: 'not recorded') ?></p>
    <?php if ($rows) bars($rows); else echo '<p class="note">No result has been recorded for this unit yet. <a href="add.php">Add one</a>.</p>'; ?>
  </section>
<?php endif; bottom(); ?>

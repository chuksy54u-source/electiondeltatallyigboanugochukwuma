<?php
// QUESTION 2: add up every polling unit in an LGA (we do NOT use announced_lga_results here)
require 'config.php';
$lga = (int)($_GET['lga'] ?? 0);
$name = ''; $rows = [];
if ($lga) {
    $st = db()->prepare("SELECT lga_name FROM lga WHERE lga_id = ? AND state_id = 25");
    $st->execute([$lga]);
    $name = $st->fetchColumn();
    $st = db()->prepare("SELECT r.party_abbreviation, SUM(r.party_score) AS total
                         FROM announced_pu_results r JOIN polling_unit p ON p.uniqueid = r.polling_unit_uniqueid
                         WHERE p.lga_id = ? GROUP BY r.party_abbreviation ORDER BY total DESC");
    $st->execute([$lga]);
    $rows = $st->fetchAll();
}
top('LGA totals', 'lga.php');
?>
<h2>Add up a whole local government</h2>
<form method="get" class="sheet">
  <label>Local government
    <select name="lga" onchange="this.form.submit()"><option value="">Choose an LGA</option>
      <?php foreach (lgas() as $l) echo '<option value="' . $l['lga_id'] . '"' . ($lga == $l['lga_id'] ? ' selected' : '') . '>' . e($l['lga_name']) . '</option>'; ?>
    </select></label>
  <noscript><button>Show totals</button></noscript>
</form>
<?php if ($lga && $name): ?>
  <section class="result">
    <h3><?= e($name) ?></h3>
    <p>Sum of every polling-unit result in this LGA</p>
    <?php if ($rows) bars($rows); else echo '<p class="note">No polling-unit results have been recorded in this LGA yet.</p>'; ?>
  </section>
<?php endif; bottom(); ?>

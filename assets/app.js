// Chained dropdown: when the LGA changes, fill the second box from ajax.php
const lga = document.querySelector('#lga'), sub = document.querySelector('#sub');
async function fill(lgaId, selected) {
  sub.disabled = true;
  sub.replaceChildren(new Option('Loading…', ''));
  const items = await (await fetch('ajax.php?type=' + sub.dataset.kind + '&lga=' + lgaId)).json();
  sub.replaceChildren(new Option('Choose one', ''));
  items.forEach(i => sub.add(new Option(i.label, i.id)));   // Option() uses text, so names can’t inject HTML
  sub.disabled = false;
  if (selected) sub.value = selected;
}
if (lga && sub) {
  lga.addEventListener('change', () => lga.value ? fill(lga.value) : sub.replaceChildren(new Option('Pick an LGA first', '')));
  if (lga.value) fill(lga.value, sub.dataset.sel);
}

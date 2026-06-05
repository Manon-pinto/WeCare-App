function closeModal() {
  document.getElementById('modalAjouter').style.display = 'none';
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeModal();
});

document.getElementById('searchInput').addEventListener('input', function () {
  const q = this.value.toLowerCase();
  const rows = document.querySelectorAll('#soignantsBody tr');
  let count = 0;
  rows.forEach(row => {
    const show = row.textContent.toLowerCase().includes(q);
    row.style.display = show ? '' : 'none';
    if (show) count++;
  });
  document.getElementById('listCount').textContent = count + ' soignant' + (count > 1 ? 's' : '');
});

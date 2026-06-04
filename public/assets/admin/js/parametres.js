function showSection(id, el) {
  document.querySelectorAll('.params-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-param-item').forEach(n => n.classList.remove('active'));
  document.getElementById('section-' + id).classList.add('active');
  el.classList.add('active');
}

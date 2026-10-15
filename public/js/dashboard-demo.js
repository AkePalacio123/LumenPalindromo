function setActivePeriod(btn) {
  const parent = document.getElementById('period-selector');
  if (!parent) return;
  const buttons = parent.querySelectorAll('button');
  buttons.forEach(b => {
    b.className = 'px-space-md py-1 rounded text-label-md font-label-md text-on-surface-variant hover:text-on-surface transition-colors';
  });
  btn.className = 'px-space-md py-1 rounded bg-surface-container-lowest text-primary shadow-sm text-label-md font-label-md font-bold transition-all';
}

function filterTable(status) {
  const rows = document.querySelectorAll('tbody tr');
  rows.forEach(r => {
    if (status === 'all') {
      r.style.display = '';
    } else if (status === 'transito' && !r.innerText.includes('En Tránsito')) {
      r.style.display = 'none';
    } else if (status === 'retorno' && !r.innerText.includes('Retorno')) {
      r.style.display = 'none';
    } else if (status === 'entregado' && !r.innerText.includes('Entregado')) {
      r.style.display = 'none';
    } else {
      r.style.display = '';
    }
  });
}

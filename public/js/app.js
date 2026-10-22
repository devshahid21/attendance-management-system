(() => {
  const toggle = document.querySelector('[data-nav-toggle]');
  const sidebar = document.querySelector('.sidebar');
  const mobile = window.matchMedia('(max-width: 991px)');
  const closeNav = () => {
    document.body.classList.remove('nav-open');
    toggle?.setAttribute('aria-expanded', 'false');
    toggle?.setAttribute('aria-label', 'Open navigation');
    if (sidebar) sidebar.inert = mobile.matches;
  };
  const syncNav = () => { if (!mobile.matches) { closeNav(); if (sidebar) sidebar.inert = false; } else closeNav(); };
  toggle?.addEventListener('click', () => {
    const open = !document.body.classList.contains('nav-open');
    document.body.classList.toggle('nav-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    sidebar.inert = !open;
    if (open) sidebar.querySelector('.side-nav a')?.focus();
  });
  document.querySelector('[data-nav-close]')?.addEventListener('click', () => { closeNav(); toggle?.focus(); });
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && document.body.classList.contains('nav-open')) { closeNav(); toggle?.focus(); } });
  mobile.addEventListener('change', syncNav); syncNav();
  document.querySelectorAll('[data-dismiss-alert]').forEach(button => button.addEventListener('click', () => button.closest('.app-alert').remove()));
  document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }));
  const tableHints = Array.from(document.querySelectorAll('.table-responsive')).map(table => {
    const hint = document.createElement('p');
    hint.className = 'table-scroll-hint'; hint.textContent = 'Swipe left or right to view all columns.';
    table.before(hint); return { table, hint };
  });
  const updateTableHints = () => tableHints.forEach(({ table, hint }) => { hint.hidden = table.scrollWidth <= table.clientWidth + 1; });
  updateTableHints(); window.addEventListener('resize', updateTableHints);
  const paintClock = () => document.querySelectorAll('[data-clock]').forEach(el => {
    el.textContent = new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Karachi', hour: '2-digit', minute: '2-digit', second: '2-digit' }).format(new Date());
  });
  paintClock(); if (document.querySelector('[data-clock]')) setInterval(paintClock, 1000);
})();

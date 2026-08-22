document.addEventListener('DOMContentLoaded', function () {

  /* ---------- Tab switching ---------- */
  const tabs = document.querySelectorAll('.tab[data-tab]');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      const target = tab.getAttribute('data-tab');

      tabs.forEach(function (t) {
        t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
      });

      document.querySelectorAll('.tab-panel').forEach(function (panel) {
        panel.classList.toggle('is-active', panel.id === 'panel-' + target);
      });
    });
  });

  /* ---------- Salary calculator (Salary Info tab, HR only) ---------- */
  const ledger = document.getElementById('components-ledger');
  if (!ledger) return; // employee view has no Salary Info tab

  const employeeId = ledger.getAttribute('data-employee-id');

  const monthWage   = document.getElementById('month-wage');
  const yearWage     = document.getElementById('year-wage');
  const workingDays  = document.getElementById('working-days');
  const breakTime    = document.getElementById('break-time');
  const profTax      = document.getElementById('prof-tax');
  const capWarning    = document.getElementById('cap-warning');
  const totalEl        = document.getElementById('components-total');
  const saveStatus     = document.getElementById('save-status');
  const saveBtn         = document.getElementById('save-btn');
  const resetBtn         = document.getElementById('reset-btn');

  const rows = {
    basic: ledger.querySelector('[data-component="basic"]'),
    hra: ledger.querySelector('[data-component="hra"]'),
    standard_allowance: ledger.querySelector('[data-component="standard_allowance"]'),
    performance_bonus: ledger.querySelector('[data-component="performance_bonus"]'),
    leave_travel_allowance: ledger.querySelector('[data-component="leave_travel_allowance"]'),
    fixed_allowance: ledger.querySelector('[data-component="fixed_allowance"]'),
  };

  const pfEmployeeRow = ledger.parentElement.querySelector('[data-pf="employee"]');
  const pfEmployerRow = ledger.parentElement.querySelector('[data-pf="employer"]');

  function num(el, fallback) {
    if (!el) return fallback || 0;
    const v = parseFloat(el.value);
    return isNaN(v) ? (fallback || 0) : v;
  }

  function fmt(n) {
    return n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function setVal(row, n) {
    if (!row) return;
    const el = row.querySelector('[data-amount] .val');
    if (el) el.textContent = fmt(n);
  }

  function recalc() {
    const wage = num(monthWage);
    if (yearWage) yearWage.value = wage * 12;

    const basicPct = num(rows.basic && rows.basic.querySelector('.pct-input'));
    const hraPct = num(rows.hra && rows.hra.querySelector('.pct-input'));
    const standard = num(rows.standard_allowance && rows.standard_allowance.querySelector('.amount-input'));
    const bonusPct = num(rows.performance_bonus && rows.performance_bonus.querySelector('.pct-input'));
    const ltaPct = num(rows.leave_travel_allowance && rows.leave_travel_allowance.querySelector('.pct-input'));

    const basic = wage * (basicPct / 100);
    const hra = basic * (hraPct / 100);
    const bonus = wage * (bonusPct / 100);
    const lta = basic * (ltaPct / 100);

    const subtotal = basic + hra + standard + bonus + lta;
    const fixed = wage - subtotal;
    const total = basic + hra + standard + bonus + lta + fixed;

    setVal(rows.basic, basic);
    setVal(rows.hra, hra);
    setVal(rows.standard_allowance, standard);
    setVal(rows.performance_bonus, bonus);
    setVal(rows.leave_travel_allowance, lta);
    setVal(rows.fixed_allowance, fixed);
    if (totalEl) totalEl.querySelector('.val').textContent = fmt(total);

    if (capWarning) capWarning.style.display = fixed < 0 ? 'block' : 'none';

    const pfEmployeePct = num(pfEmployeeRow && pfEmployeeRow.querySelector('.pf-pct'));
    const pfEmployerPct = num(pfEmployerRow && pfEmployerRow.querySelector('.pf-pct'));
    setVal(pfEmployeeRow, basic * (pfEmployeePct / 100));
    setVal(pfEmployerRow, basic * (pfEmployerPct / 100));

    if (saveStatus) saveStatus.textContent = 'Unsaved changes';
  }

  ledger.parentElement.querySelectorAll('input').forEach(function (input) {
    if (input === yearWage) return;
    input.addEventListener('input', recalc);
  });

  function payload() {
    return {
      month_wage: num(monthWage),
      working_days_per_week: num(workingDays, 5),
      break_time_hrs: num(breakTime, 1),
      components: {
        basic: { percent: num(rows.basic && rows.basic.querySelector('.pct-input')) },
        hra: { percent: num(rows.hra && rows.hra.querySelector('.pct-input')) },
        standard_allowance: { amount: num(rows.standard_allowance && rows.standard_allowance.querySelector('.amount-input')) },
        performance_bonus: { percent: num(rows.performance_bonus && rows.performance_bonus.querySelector('.pct-input')) },
        leave_travel_allowance: { percent: num(rows.leave_travel_allowance && rows.leave_travel_allowance.querySelector('.pct-input')) },
      },
      pf: {
        employee_percent: num(pfEmployeeRow && pfEmployeeRow.querySelector('.pf-pct')),
        employer_percent: num(pfEmployerRow && pfEmployerRow.querySelector('.pf-pct')),
      },
      professional_tax: num(profTax),
    };
  }

  if (saveBtn) {
    saveBtn.addEventListener('click', function () {
      if (!employeeId) return;
      saveStatus.textContent = 'Saving…';
      fetch('../api/save_salary.php?id=' + encodeURIComponent(employeeId), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload()),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          saveStatus.textContent = data.ok ? 'Saved' : ('Error: ' + data.error);
        })
        .catch(function () {
          saveStatus.textContent = 'Error: could not reach server';
        });
    });
  }

  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      window.location.reload();
    });
  }
});

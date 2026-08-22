<?php
/**
 * Renders the "My Profile" card: avatar + meta, tabs, and panels.
 * Used by both hr/employee_profile.php (HR viewing/editing any employee,
 * full access) and employee/profile.php (employee viewing their own
 * profile — no Salary Info tab).
 *
 * @param array  $employee       Employee record (name, department, ... , salary).
 * @param string $employeeId     Record key, e.g. "emp-0001".
 * @param bool   $canEditSalary  True for HR — renders the editable Salary Info tab.
 */
function render_profile_card(array $employee, string $employeeId, bool $canEditSalary): void
{
    $salary = $employee['salary'];
    $calc = calculate_salary($salary);
    ?>
    <section class="profile-card">
      <div class="profile-head">
        <div class="avatar"><?= htmlspecialchars(initials($employee['name'])) ?></div>
        <div>
          <h1 class="profile-head__name"><?= htmlspecialchars($employee['name']) ?></h1>
          <p class="profile-head__role"><?= htmlspecialchars($employee['department']) ?> · Login ID <?= htmlspecialchars($employee['login_id']) ?></p>
          <dl class="meta-grid">
            <div><dt>Department</dt><dd><?= htmlspecialchars($employee['department']) ?></dd></div>
            <div><dt>Manager</dt><dd><?= htmlspecialchars($employee['manager']) ?></dd></div>
            <div><dt>Email</dt><dd><?= htmlspecialchars($employee['email']) ?></dd></div>
            <div><dt>Location</dt><dd><?= htmlspecialchars($employee['location']) ?></dd></div>
            <div><dt>Mobile</dt><dd><?= htmlspecialchars($employee['mobile']) ?></dd></div>
            <div><dt>Date of joining</dt><dd><?= htmlspecialchars($employee['date_of_joining']) ?></dd></div>
          </dl>
        </div>
      </div>

      <div class="tabs" role="tablist" aria-label="Profile sections">
        <button class="tab" role="tab" aria-selected="true" data-tab="resume" id="tab-resume">Resume</button>
        <button class="tab" role="tab" aria-selected="false" data-tab="private" id="tab-private">Private Info</button>
        <?php if ($canEditSalary): ?>
          <button class="tab" role="tab" aria-selected="false" data-tab="salary" id="tab-salary">Salary Info</button>
        <?php endif; ?>
        <button class="tab" role="tab" aria-selected="false" data-tab="security" id="tab-security">Security</button>
      </div>

      <div class="tab-panel is-active" id="panel-resume" role="tabpanel" aria-labelledby="tab-resume">
        <div class="empty-note">Resume, skills and certifications go here.</div>
      </div>

      <div class="tab-panel" id="panel-private" role="tabpanel" aria-labelledby="tab-private">
        <div class="empty-note">Bank details, PAN, address and emergency contact go here.</div>
      </div>

      <?php if ($canEditSalary): ?>
      <div class="tab-panel" id="panel-salary" role="tabpanel" aria-labelledby="tab-salary">

        <div class="salary-summary">
          <div class="wage-field">
            <label for="month-wage">Month wage</label>
            <div class="amount-row">
              <span class="currency">₹</span>
              <input type="number" id="month-wage" min="0" step="1" value="<?= htmlspecialchars($salary['month_wage']) ?>">
            </div>
            <span class="unit">/ month</span>
          </div>
          <div class="wage-field small">
            <label for="year-wage">Yearly wage</label>
            <div class="amount-row">
              <span class="currency">₹</span>
              <input type="number" id="year-wage" readonly tabindex="-1" value="<?= htmlspecialchars($salary['month_wage'] * 12) ?>">
            </div>
            <span class="unit">/ year</span>
          </div>
          <div class="wage-field small">
            <label for="working-days">Working days / week</label>
            <div class="amount-row">
              <input type="number" id="working-days" min="1" max="7" value="<?= htmlspecialchars($salary['working_days_per_week']) ?>">
            </div>
            <span class="unit">days</span>
          </div>
          <div class="wage-field small">
            <label for="break-time">Break time</label>
            <div class="amount-row">
              <input type="number" id="break-time" min="0" step="0.5" value="<?= htmlspecialchars($salary['break_time_hrs']) ?>">
            </div>
            <span class="unit">/hrs</span>
          </div>
        </div>

        <div class="section-title"><h3>Salary components</h3><span class="rule"></span></div>
        <p class="section-hint">Basic and Performance Bonus are set as a % of wage. HRA and Leave Travel Allowance are a % of Basic. Fixed Allowance auto-fills the remainder — it can't be edited directly.</p>

        <div class="ledger" id="components-ledger" data-employee-id="<?= htmlspecialchars($employeeId) ?>">
          <div class="ledger-row head">
            <div>Component</div>
            <div class="ledger-percent" style="justify-content:flex-end;">% of basis</div>
            <div style="text-align:right;">Amount / month</div>
          </div>

          <div class="ledger-row" data-component="basic" data-basis="wage">
            <div class="ledger-label">
              <span class="name">Basic Salary</span>
              <span class="note">Define basic salary the company must pay based on monthly wages<span class="basis-pill">of wage</span></span>
            </div>
            <div class="ledger-percent">
              <input type="number" class="pct-input" min="0" max="100" step="0.01" value="<?= $salary['components']['basic']['percent'] ?>">
              <span class="pct-sign">%</span>
            </div>
            <div class="ledger-amount" data-amount>₹<span class="val"><?= inr($calc['basic']) ?></span></div>
          </div>

          <div class="ledger-row" data-component="hra" data-basis="basic">
            <div class="ledger-label">
              <span class="name">House Rent Allowance</span>
              <span class="note">HRA provided to employees<span class="basis-pill">of basic</span></span>
            </div>
            <div class="ledger-percent">
              <input type="number" class="pct-input" min="0" max="100" step="0.01" value="<?= $salary['components']['hra']['percent'] ?>">
              <span class="pct-sign">%</span>
            </div>
            <div class="ledger-amount" data-amount>₹<span class="val"><?= inr($calc['hra']) ?></span></div>
          </div>

          <div class="ledger-row" data-component="standard_allowance" data-basis="fixed">
            <div class="ledger-label">
              <span class="name">Standard Allowance</span>
              <span class="note">A standard allowance is a pre-defined, fixed amount provided as part of their salary<span class="basis-pill">fixed</span></span>
            </div>
            <div class="ledger-percent">
              <input type="number" class="amount-input" min="0" step="1" value="<?= $salary['components']['standard_allowance']['amount'] ?>">
            </div>
            <div class="ledger-amount" data-amount>₹<span class="val"><?= inr($calc['standard_allowance']) ?></span></div>
          </div>

          <div class="ledger-row" data-component="performance_bonus" data-basis="wage">
            <div class="ledger-label">
              <span class="name">Performance Bonus</span>
              <span class="note">Variable amount paid during payroll, defined as % of wage<span class="basis-pill">of wage</span></span>
            </div>
            <div class="ledger-percent">
              <input type="number" class="pct-input" min="0" max="100" step="0.01" value="<?= $salary['components']['performance_bonus']['percent'] ?>">
              <span class="pct-sign">%</span>
            </div>
            <div class="ledger-amount" data-amount>₹<span class="val"><?= inr($calc['performance_bonus']) ?></span></div>
          </div>

          <div class="ledger-row" data-component="leave_travel_allowance" data-basis="basic">
            <div class="ledger-label">
              <span class="name">Leave Travel Allowance</span>
              <span class="note">Paid by the company to employees to cover travel expenses<span class="basis-pill">of basic</span></span>
            </div>
            <div class="ledger-percent">
              <input type="number" class="pct-input" min="0" max="100" step="0.01" value="<?= $salary['components']['leave_travel_allowance']['percent'] ?>">
              <span class="pct-sign">%</span>
            </div>
            <div class="ledger-amount" data-amount>₹<span class="val"><?= inr($calc['leave_travel_allowance']) ?></span></div>
          </div>

          <div class="ledger-row" data-component="fixed_allowance" data-basis="remainder">
            <div class="ledger-label">
              <span class="name">Fixed Allowance</span>
              <span class="note">Wage minus the total of all other components<span class="basis-pill">remainder</span></span>
            </div>
            <div class="ledger-percent">—</div>
            <div class="ledger-amount" data-amount>₹<span class="val"><?= inr($calc['fixed_allowance']) ?></span></div>
          </div>

          <div class="ledger-row total">
            <div class="ledger-label"><span class="name">Total</span></div>
            <div></div>
            <div class="ledger-amount" id="components-total">₹<span class="val"><?= inr($calc['total']) ?></span></div>
          </div>
        </div>

        <div class="cap-warning" id="cap-warning">
          ⚠ Components total exceeds the defined wage — Fixed Allowance has gone negative.
        </div>

        <div class="two-up">
          <div>
            <div class="section-title"><h3>Provident Fund (PF)</h3><span class="rule"></span></div>
            <div class="mini-ledger">
              <div class="ledger-row" data-pf="employee" data-basis="basic">
                <div class="ledger-label"><span class="name">Employee</span><span class="note">% of basic salary</span></div>
                <div class="ledger-percent">
                  <input type="number" class="pf-pct" min="0" max="100" step="0.01" value="<?= $salary['pf']['employee_percent'] ?>">
                  <span class="pct-sign">%</span>
                </div>
                <div class="ledger-amount" data-amount>₹<span class="val"><?= inr($calc['pf_employee']) ?></span></div>
              </div>
              <div class="ledger-row" data-pf="employer" data-basis="basic">
                <div class="ledger-label"><span class="name">Employer</span><span class="note">% of basic salary</span></div>
                <div class="ledger-percent">
                  <input type="number" class="pf-pct" min="0" max="100" step="0.01" value="<?= $salary['pf']['employer_percent'] ?>">
                  <span class="pct-sign">%</span>
                </div>
                <div class="ledger-amount" data-amount>₹<span class="val"><?= inr($calc['pf_employer']) ?></span></div>
              </div>
            </div>
          </div>

          <div>
            <div class="section-title"><h3>Tax deductions</h3><span class="rule"></span></div>
            <div class="mini-ledger">
              <div class="ledger-row">
                <div class="ledger-label"><span class="name">Professional Tax</span><span class="note">Deducted from the gross salary</span></div>
                <div></div>
                <div class="ledger-amount">
                  <input type="number" id="prof-tax" min="0" step="1" value="<?= $salary['professional_tax'] ?>"
                         style="width:70px;text-align:right;font-family:var(--font-mono);border:1px solid var(--line);border-radius:7px;padding:5px 6px;background:var(--lilac-50);">
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="save-bar">
          <span class="save-status" id="save-status">Saved</span>
          <button class="btn btn-ghost" type="button" id="reset-btn">Reset</button>
          <button class="btn btn-primary" type="button" id="save-btn">Save changes</button>
        </div>
      </div>
      <?php endif; ?>

      <div class="tab-panel" id="panel-security" role="tabpanel" aria-labelledby="tab-security">
        <div class="empty-note">Password, two-factor and login history go here.</div>
      </div>

    </section>
    <?php
}

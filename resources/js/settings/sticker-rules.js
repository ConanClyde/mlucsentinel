/**
 * Sticker Rules Settings
 */

const qs = (id) => document.getElementById(id);

let config = null;
let colors = [];
let stakeholderTypes = [];
let stickerRulesRealtimeBound = false;

export function initializeStickerRules() {
    loadStickerConfig();
    bindStickerRulesRealtime();
}

function loadStickerConfig() {
    fetch('/api/settings/sticker-config', {
        headers: { 'Accept': 'application/json' }
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                config = data.config;
                colors = data.colors || [];
                stakeholderTypes = data.stakeholder_types || [];
                renderUI();
            }
        })
        .catch(() => window.showErrorModal('Failed to load sticker configuration'));
}

function renderUI() {
    if (!config) return;

    // Render color dropdowns
    renderColorOptions(qs('staff-color'));
    renderColorOptions(qs('security-color'));
    renderColorOptions(qs('student-color-12'));
    renderColorOptions(qs('student-color-34'));
    renderColorOptions(qs('student-color-56'));
    renderColorOptions(qs('student-color-78'));
    renderColorOptions(qs('student-color-90'));
    renderColorOptions(qs('student-color-no_plate'));

    // Set expiration years
    qs('sticker-expiration-student-years').value = config.student_expiration_years || 4;
    qs('sticker-expiration-staff-years').value = config.staff_expiration_years || 4;
    qs('sticker-expiration-security-years').value = config.security_expiration_years || 4;
    qs('sticker-expiration-stakeholder-years').value = config.stakeholder_expiration_years || 4;

    // Set color selections
    setSelectValue('staff-color', config.staff_color || 'maroon');
    setSelectValue('security-color', config.security_color || 'maroon');

    const sm = config.student_map || {};
    setSelectValue('student-color-12', sm['12'] || 'blue');
    setSelectValue('student-color-34', sm['34'] || 'green');
    setSelectValue('student-color-56', sm['56'] || 'yellow');
    setSelectValue('student-color-78', sm['78'] || 'pink');
    setSelectValue('student-color-90', sm['90'] || 'orange');
    setSelectValue('student-color-no_plate', sm['no_plate'] || 'white');

    // Render stakeholder color rules
    renderStakeholderColorRules();

    // Bind save button
    bindSave();
}

function renderColorOptions(selectEl) {
    if (!selectEl) return;
    selectEl.innerHTML = colors.map(c => `<option value="${c.key}">${c.label}</option>`).join('');
}

function setSelectValue(id, val) {
    const el = qs(id);
    if (el) el.value = val;
}

function renderStakeholderColorRules() {
    const container = qs('stakeholder-color-rules');
    if (!container) return;

    if (stakeholderTypes.length === 0) {
        container.innerHTML = '<p class="text-[#706f6c] dark:text-[#A1A09A] col-span-2">No stakeholder types defined. Add them in the Stakeholders tab.</p>';
        return;
    }

    container.innerHTML = stakeholderTypes.map(t => {
        const currentColor = (config.stakeholder_map && config.stakeholder_map[t.id]) || 'white';
        const options = colors.map(c => `<option value="${c.key}" ${c.key === currentColor ? 'selected' : ''}>${c.label}</option>`).join('');
        return `
            <div>
                <label class="form-label">${t.name}</label>
                <select id="stakeholder-color-${t.id}" class="form-input w-full">${options}</select>
            </div>
        `;
    }).join('');
}

function bindSave() {
    const btn = qs('save-sticker-config');
    if (!btn) return;

    btn.replaceWith(btn.cloneNode(true)); // Remove old listeners
    const newBtn = qs('save-sticker-config');

    newBtn.addEventListener('click', () => {
        const studentYears = parseInt(qs('sticker-expiration-student-years').value || '4', 10);
        const staffYears = parseInt(qs('sticker-expiration-staff-years').value || '4', 10);
        const securityYears = parseInt(qs('sticker-expiration-security-years').value || '4', 10);
        const stakeholderYears = parseInt(qs('sticker-expiration-stakeholder-years').value || '4', 10);
        const staffColor = qs('staff-color').value;
        const securityColor = qs('security-color').value;
        const studentMap = {
            '12': qs('student-color-12').value,
            '34': qs('student-color-34').value,
            '56': qs('student-color-56').value,
            '78': qs('student-color-78').value,
            '90': qs('student-color-90').value,
            'no_plate': qs('student-color-no_plate').value,
        };

        const stakeholderMap = {};
        stakeholderTypes.forEach(t => {
            const sel = qs(`stakeholder-color-${t.id}`);
            if (sel) stakeholderMap[t.id] = sel.value;
        });

        fetch('/api/settings/sticker-config', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                student_expiration_years: studentYears,
                staff_expiration_years: staffYears,
                security_expiration_years: securityYears,
                stakeholder_expiration_years: stakeholderYears,
                staff_color: staffColor,
                security_color: securityColor,
                student_map: studentMap,
                stakeholder_map: stakeholderMap,
            })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.showSuccessModal('Saved', 'Sticker rules updated successfully');
                    config = data.config;
                } else {
                    window.showErrorModal(data.message || 'Failed to save');
                }
            })
            .catch(() => window.showErrorModal('Failed to save sticker rules'));
    });
}

function bindStickerRulesRealtime() {
    if (stickerRulesRealtimeBound) return;
    if (!window.Echo) return;
    try {
        window.Echo.channel('sticker-rules')
            .listen('.sticker-rules.updated', (event) => {
                // Reload sticker config
                loadStickerConfig();
            });
        stickerRulesRealtimeBound = true;
    } catch (e) {
        console.warn('Realtime not available for sticker-rules:', e);
    }
}

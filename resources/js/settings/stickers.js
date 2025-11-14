let loaded = false;
let colors = [];
let stakeholderTypes = [];
let config = null;
let palette = [];

export function initializeStickers() {
  if (loaded) return;
  loaded = true;
  loadStickerConfig();
}

function qs(id) {
  return document.getElementById(id);
}

function renderColorOptions(selectEl) {
  if (!selectEl) return;
  selectEl.innerHTML = colors
    .map(c => `<option value="${c.value}">${c.label}</option>`)
    .join('');
}

function setSelectValue(id, value) {
  const el = qs(id);
  if (!el) return;
  if (!el.options.length) renderColorOptions(el);
  el.value = value;
}

function mapStakeholderTypeNameToColor(name) {
  if (!config || !config.stakeholder_map) return null;
  return config.stakeholder_map[name] || null;
}

function bindSave() {
  const btn = qs('save-sticker-config');
  if (!btn) return;
  btn.addEventListener('click', () => {
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
          window.showSuccessModal('Saved', 'Sticker rules updated');
          config = data.config;
        } else {
          window.showErrorModal(data.message || 'Failed to save');
        }
      })
      .catch(() => window.showErrorModal('Failed to save sticker rules'));
  });
}

// Palette management
function loadPalette() {
  const tbody = qs('palette-table-body');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading colors...</td></tr>';
  fetch('/api/settings/sticker-palette', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
      if (!data.success) return;
      palette = data.data || [];
      renderPalette();
    })
    .catch(() => {
      tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-6 text-center text-sm text-red-600 dark:text-red-400">Failed to load colors</td></tr>';
    });
}

function renderPalette() {
  const tbody = qs('palette-table-body');
  if (!tbody) return;
  if (!palette.length) {
    tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">No colors found.</td></tr>';
    return;
  }
  tbody.innerHTML = palette.map(c => `
    <tr class="hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors" data-key="${c.key}">
      <td class="px-4 py-3">
        <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${c.name}</span>
      </td>
      <td class="px-4 py-3">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600" style="background-color: ${c.hex}"></div>
          <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${c.hex}</span>
        </div>
      </td>
      <td class="px-4 py-3">
        <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${c.key}</span>
      </td>
      <td class="px-4 py-3">
        <div class="flex items-center justify-center gap-2">
          <button class="btn-edit" data-action="edit" data-key="${c.key}" data-name="${c.name}" data-hex="${c.hex}" title="Edit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
          </button>
          <button class="btn-delete" data-action="delete" data-key="${c.key}" data-name="${c.name}" title="Delete">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>
        </div>
      </td>
    </tr>
  `).join('');

  tbody.querySelectorAll('button[data-action="edit"]').forEach(btn => {
    btn.addEventListener('click', () => openEditStickerColorModal(btn.getAttribute('data-key'), btn.getAttribute('data-name'), btn.getAttribute('data-hex')));
  });
  tbody.querySelectorAll('button[data-action="delete"]').forEach(btn => {
    btn.addEventListener('click', () => openDeleteStickerColorModal(btn.getAttribute('data-key'), btn.getAttribute('data-name')));
  });
}

// Add Color modal
export function openAddStickerColorModal() {
  const colorInput = qs('modal-sticker-color');
  const hexInput = qs('modal-sticker-color-hex');
  if (colorInput && hexInput) {
    colorInput.addEventListener('input', function(){ hexInput.value = this.value; validateAddStickerColorForm(); });
    hexInput.addEventListener('input', function(){ if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(this.value)) { colorInput.value = this.value; } validateAddStickerColorForm(); });
  }
  const nameInput = qs('modal-sticker-color-name');
  if (nameInput) nameInput.addEventListener('input', validateAddStickerColorForm);
  qs('add-sticker-color-modal').classList.remove('hidden');
  validateAddStickerColorForm();
}

export function closeAddStickerColorModal() {
  qs('add-sticker-color-modal').classList.add('hidden');
  qs('modal-sticker-color-name').value = '';
  qs('modal-sticker-color').value = '#3B82F6';
  qs('modal-sticker-color-hex').value = '#3B82F6';
  hideStickerColorError('modal-sticker-color-name', 'modal-sticker-color-name-error');
  hideStickerColorError('modal-sticker-color-hex', 'modal-sticker-color-error');
}

function showStickerColorError(inputId, errorId, message) {
  const errorEl = qs(errorId);
  if (errorEl) { errorEl.textContent = message; errorEl.classList.remove('hidden'); }
  const inputEl = qs(inputId);
  if (inputEl) { inputEl.classList.add('border-red-500'); }
}

function hideStickerColorError(inputId, errorId) {
  const errorEl = qs(errorId);
  if (errorEl) { errorEl.classList.add('hidden'); errorEl.textContent = ''; }
  const inputEl = qs(inputId);
  if (inputEl) { inputEl.classList.remove('border-red-500'); }
}

function validateAddStickerColorForm() {
  const name = (qs('modal-sticker-color-name').value || '').trim();
  const color = (qs('modal-sticker-color-hex').value || qs('modal-sticker-color').value || '').trim();
  const btn = qs('add-sticker-color-btn');
  if (!name || !color || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color)) { btn.disabled = true; return false; }
  hideStickerColorError('modal-sticker-color-name','modal-sticker-color-name-error');
  hideStickerColorError('modal-sticker-color-hex','modal-sticker-color-error');
  btn.disabled = false; return true;
}

export function addStickerColor() {
  const name = (qs('modal-sticker-color-name').value || '').trim();
  const hex = (qs('modal-sticker-color-hex').value || qs('modal-sticker-color').value || '').trim();
  if (!name) { showStickerColorError('modal-sticker-color-name','modal-sticker-color-name-error','Enter a color name'); return; }
  if (!/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex)) { showStickerColorError('modal-sticker-color-hex','modal-sticker-color-error','Enter a valid hex color'); return; }
  fetch('/api/settings/sticker-palette', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
    body: JSON.stringify({ name, hex })
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        window.showSuccessModal('Success!', 'Color added successfully');
        closeAddStickerColorModal();
        loadPalette();
        // refresh options
        loadStickerConfig();
      } else {
        showStickerColorError('modal-sticker-color-name','modal-sticker-color-name-error', data.message || 'Failed to add color');
      }
    })
    .catch(() => showStickerColorError('modal-sticker-color-name','modal-sticker-color-name-error','Failed to add color'));
}

// Edit Color modal
export function openEditStickerColorModal(key, name, hex) {
  qs('edit-sticker-color-key').value = key;
  qs('edit-sticker-color-name').value = name;
  qs('edit-sticker-color').value = hex || '#3B82F6';
  qs('edit-sticker-color-hex').value = hex || '#3B82F6';

  const colorInput = qs('edit-sticker-color');
  const hexInput = qs('edit-sticker-color-hex');
  if (colorInput && hexInput) {
    colorInput.addEventListener('input', function(){ hexInput.value = this.value; validateEditStickerColorForm(); });
    hexInput.addEventListener('input', function(){ if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(this.value)) { colorInput.value = this.value; } validateEditStickerColorForm(); });
  }
  const nameInput = qs('edit-sticker-color-name');
  if (nameInput) nameInput.addEventListener('input', validateEditStickerColorForm);

  qs('edit-sticker-color-modal').classList.remove('hidden');
  validateEditStickerColorForm();
}

export function closeEditStickerColorModal() {
  qs('edit-sticker-color-modal').classList.add('hidden');
  qs('edit-sticker-color-key').value = '';
  qs('edit-sticker-color-name').value = '';
  qs('edit-sticker-color').value = '#3B82F6';
  qs('edit-sticker-color-hex').value = '#3B82F6';
  hideStickerColorError('edit-sticker-color-name','edit-sticker-color-name-error');
  hideStickerColorError('edit-sticker-color-hex','edit-sticker-color-error');
}

function validateEditStickerColorForm() {
  const name = (qs('edit-sticker-color-name').value || '').trim();
  const hex = (qs('edit-sticker-color-hex').value || qs('edit-sticker-color').value || '').trim();
  const btn = qs('update-sticker-color-btn');
  if (!name || !hex || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex)) { btn.disabled = true; return false; }
  hideStickerColorError('edit-sticker-color-name','edit-sticker-color-name-error');
  hideStickerColorError('edit-sticker-color-hex','edit-sticker-color-error');
  btn.disabled = false; return true;
}

export function updateStickerColor() {
  const key = qs('edit-sticker-color-key').value;
  const name = (qs('edit-sticker-color-name').value || '').trim();
  const hex = (qs('edit-sticker-color-hex').value || qs('edit-sticker-color').value || '').trim();
  fetch(`/api/settings/sticker-palette/${encodeURIComponent(key)}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
    body: JSON.stringify({ name, hex })
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        window.showSuccessModal('Success!', 'Color updated successfully');
        closeEditStickerColorModal();
        loadPalette();
        loadStickerConfig();
      } else {
        showStickerColorError('edit-sticker-color-name','edit-sticker-color-name-error', data.message || 'Failed to update color');
      }
    })
    .catch(() => showStickerColorError('edit-sticker-color-name','edit-sticker-color-name-error','Failed to update color'));
}

let colorToDelete = null;
export function openDeleteStickerColorModal(key, name) {
  colorToDelete = key;
  const msg = qs('deleteStickerColorMessage');
  if (msg) msg.textContent = `Delete color "${name}"? This cannot be undone.`;
  qs('delete-sticker-color-modal').classList.remove('hidden');
}

export function closeDeleteStickerColorModal() {
  qs('delete-sticker-color-modal').classList.add('hidden');
  colorToDelete = null;
}

export function confirmDeleteStickerColor() {
  if (!colorToDelete) return;
  fetch(`/api/settings/sticker-palette/${encodeURIComponent(colorToDelete)}`, {
    method: 'DELETE',
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        window.showSuccessModal('Success!', 'Color deleted successfully');
        closeDeleteStickerColorModal();
        loadPalette();
        loadStickerConfig();
      } else {
        window.showErrorModal(data.message || 'Failed to delete color');
      }
    })
    .catch(() => window.showErrorModal('Failed to delete color'));
}

function renderStakeholderTypes() {
  const tbody = qs('stakeholder-types-tbody');
  if (!tbody) return;
  if (!stakeholderTypes.length) {
    tbody.innerHTML = `<tr><td colspan="3" class="px-4 py-6 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">No stakeholder types</td></tr>`;
    return;
  }
  tbody.innerHTML = stakeholderTypes.map(t => {
    const selectedColor = mapStakeholderTypeNameToColor(t.name) || 'white';
    return `
      <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]" data-id="${t.id}">
        <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
          <input type="text" class="form-input w-full" id="stk-name-${t.id}" value="${t.name}" />
        </td>
        <td class="px-4 py-3">
          <select id="stakeholder-color-${t.id}" class="form-input w-full"></select>
        </td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-2 justify-center">
            <button class="btn btn-secondary" data-action="update" data-id="${t.id}">Update</button>
            <button class="btn btn-danger" data-action="delete" data-id="${t.id}">Delete</button>
          </div>
        </td>
      </tr>
    `;
  }).join('');

  stakeholderTypes.forEach(t => {
    renderColorOptions(qs(`stakeholder-color-${t.id}`));
    setSelectValue(`stakeholder-color-${t.id}`, mapStakeholderTypeNameToColor(t.name) || 'white');
  });

  tbody.querySelectorAll('button[data-action="update"]').forEach(btn => {
    btn.addEventListener('click', () => updateStakeholderType(parseInt(btn.getAttribute('data-id'), 10)));
  });
  tbody.querySelectorAll('button[data-action="delete"]').forEach(btn => {
    btn.addEventListener('click', () => deleteStakeholderType(parseInt(btn.getAttribute('data-id'), 10)));
  });
}

function bindStakeholderActions() {
  const addBtn = qs('add-stakeholder-type');
  if (addBtn) {
    addBtn.addEventListener('click', () => {
      const name = (qs('new-stakeholder-type-name').value || '').trim();
      if (!name) return window.showErrorModal('Enter a stakeholder type name');
      fetch('/api/stakeholder-types', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name })
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            qs('new-stakeholder-type-name').value = '';
            stakeholderTypes.push(data.data);
            renderStakeholderTypes();
          } else {
            window.showErrorModal(data.message || 'Failed to add type');
          }
        })
        .catch(() => window.showErrorModal('Failed to add type'));
    });
  }
}

function updateStakeholderType(id) {
  const name = (qs(`stk-name-${id}`).value || '').trim();
  if (!name) return window.showErrorModal('Enter a name');
  fetch(`/api/stakeholder-types/${id}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ name })
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const idx = stakeholderTypes.findIndex(x => x.id === id);
        if (idx !== -1) stakeholderTypes[idx] = data.data;
        renderStakeholderTypes();
      } else {
        window.showErrorModal(data.message || 'Failed to update type');
      }
    })
    .catch(() => window.showErrorModal('Failed to update type'));
}

function deleteStakeholderType(id) {
  fetch(`/api/stakeholder-types/${id}`, {
    method: 'DELETE',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        stakeholderTypes = stakeholderTypes.filter(x => x.id !== id);
        renderStakeholderTypes();
      } else {
        window.showErrorModal(data.message || 'Failed to delete type');
      }
    })
    .catch(() => window.showErrorModal('Failed to delete type'));
}

function loadStickerConfig() {
  const container = qs('content-stickers');
  if (!container) return;
  fetch('/api/settings/sticker-config', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
      if (!data.success) return;
      config = data.config;
      colors = data.colors || [];
      stakeholderTypes = data.stakeholder_types || [];

      renderColorOptions(qs('staff-color'));
      renderColorOptions(qs('security-color'));
      renderColorOptions(qs('student-color-12'));
      renderColorOptions(qs('student-color-34'));
      renderColorOptions(qs('student-color-56'));
      renderColorOptions(qs('student-color-78'));
      renderColorOptions(qs('student-color-90'));
      renderColorOptions(qs('student-color-no_plate'));

      qs('sticker-expiration-student-years').value = config.student_expiration_years || 4;
      qs('sticker-expiration-staff-years').value = config.staff_expiration_years || 4;
      qs('sticker-expiration-security-years').value = config.security_expiration_years || 4;
      qs('sticker-expiration-stakeholder-years').value = config.stakeholder_expiration_years || 4;
      setSelectValue('staff-color', config.staff_color || 'maroon');
      setSelectValue('security-color', config.security_color || 'maroon');

      const sm = config.student_map || {};
      setSelectValue('student-color-12', sm['12'] || 'blue');
      setSelectValue('student-color-34', sm['34'] || 'green');
      setSelectValue('student-color-56', sm['56'] || 'yellow');
      setSelectValue('student-color-78', sm['78'] || 'pink');
      setSelectValue('student-color-90', sm['90'] || 'orange');
      setSelectValue('student-color-no_plate', sm['no_plate'] || 'white');

      renderStakeholderTypes();
      bindSave();
      bindStakeholderActions();
      loadPalette();
    })
    .catch(() => window.showErrorModal('Failed to load sticker rules'));
}

window.initializeStickers = initializeStickers;
window.openAddStickerColorModal = openAddStickerColorModal;
window.closeAddStickerColorModal = closeAddStickerColorModal;
window.addStickerColor = addStickerColor;
window.openEditStickerColorModal = openEditStickerColorModal;
window.closeEditStickerColorModal = closeEditStickerColorModal;
window.updateStickerColor = updateStickerColor;
window.openDeleteStickerColorModal = openDeleteStickerColorModal;
window.closeDeleteStickerColorModal = closeDeleteStickerColorModal;
window.confirmDeleteStickerColor = confirmDeleteStickerColor;

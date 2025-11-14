const currencyFormatter = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
});

const dateTimeFormatter = new Intl.DateTimeFormat('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
});

document.addEventListener('DOMContentLoaded', () => {
    attachReceiptListeners();
    initializeModalControls();
});

function attachReceiptListeners() {
    document.querySelectorAll('[data-receipt-url]').forEach((button) => {
        button.addEventListener('click', () => openReceiptModal(button.getAttribute('data-receipt-url')));
    });
}

function initializeModalControls() {
    const modal = document.getElementById('userReceiptModal');
    const closeButtons = [
        document.getElementById('closeUserReceiptModal'),
        document.getElementById('userReceiptCloseBtn'),
    ];

    closeButtons.forEach((btn) => {
        if (btn) {
            btn.addEventListener('click', hideReceiptModal);
        }
    });

    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                hideReceiptModal();
            }
        });
    }
}

function openReceiptModal(url) {
    if (!url) {
        return;
    }

    fetch(url, {
        headers: {
            Accept: 'application/json',
        },
    })
        .then(async (response) => {
            if (!response.ok) {
                const errorData = await response.json().catch(() => null);
                throw new Error(errorData?.message || 'Unable to load receipt details.');
            }

            return response.json();
        })
        .then((data) => {
            renderReceiptContent(data);
        })
        .catch((error) => {
            renderReceiptError(error.message || 'Unable to load receipt details.');
        });
}

function showReceiptModal() {
    const modal = document.getElementById('userReceiptModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function hideReceiptModal() {
    const modal = document.getElementById('userReceiptModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function renderReceiptContent(data) {
    if (!data) {
        renderReceiptError('Receipt details are unavailable.');
        return;
    }

    const content = document.getElementById('userReceiptContent');
    const amount = typeof data.amount === 'number' ? data.amount : parseFloat(data.amount ?? 0);
    const vehicleCount = data.vehicle_count || 1;
    const perVehicleAmount = vehicleCount > 0 ? amount / vehicleCount : amount;
    const vehicles = Array.isArray(data.vehicles) ? data.vehicles : [];
    const status = capitalize(data.status || 'paid');

    const vehicleMarkup = vehicles.length
        ? vehicles
              .map(
                  (vehicle) => `
            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-[#161615] rounded-lg">
                <div>
                    <p class="font-medium text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${escapeHtml(
                        vehicle.type_name || 'Vehicle Sticker',
                    )}</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                        ${escapeHtml(
                            vehicle.plate_no ||
                                [vehicle.color, vehicle.number].filter(Boolean).join(' ') ||
                                'No plate information',
                        )}
                    </p>
                </div>
                <p class="font-semibold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">${formatCurrency(perVehicleAmount)}</p>
            </div>
        `,
              )
              .join('')
        : `<p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Vehicle details unavailable.</p>`;

    const paidDate = data.paid_at ? formatDate(data.paid_at) : 'Pending confirmation';
    const requestedDate = data.created_at ? formatDate(data.created_at) : 'Not available';
    const customerName = data.user?.full_name || `${data.user?.first_name ?? ''} ${data.user?.last_name ?? ''}`.trim();

    if (content) {
        content.innerHTML = `
            <div class="space-y-5">
                <div class="p-5 rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-100 dark:border-green-800">
                    <p class="text-xs font-semibold uppercase tracking-wider text-green-600 dark:text-green-300">Receipt</p>
                    <h2 class="text-2xl font-bold text-green-700 dark:text-green-300 mt-2">${status}</h2>
                    <p class="text-sm text-green-800/80 dark:text-green-200/70">Payment reference ${
                        escapeHtml(data.reference || 'Pending reference')
                    }</p>
                </div>

                <div class="p-5 bg-white dark:bg-[#1a1a1a] rounded-2xl border border-[#e3e3e0] dark:border-[#3E3E3A] shadow-sm space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide">Paid Date</p>
                            <p class="text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">${paidDate}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide">Requested On</p>
                            <p class="text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">${requestedDate}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide">Customer</p>
                            <p class="text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">${escapeHtml(
                                customerName || 'Your account',
                            )}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide">Vehicle Count</p>
                            <p class="text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">${vehicleCount}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide mb-3">Vehicle Stickers</p>
                        <div class="space-y-3">
                            ${vehicleMarkup}
                        </div>
                    </div>

                    <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-4">
                        <div class="flex items-center justify-between">
                            <p class="text-lg font-semibold text-[#706f6c] dark:text-[#A1A09A]">Total Amount</p>
                            <p class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">${formatCurrency(amount)}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        showReceiptModal();
    }
}

function renderReceiptError(message) {
    const content = document.getElementById('userReceiptContent');

    if (content) {
        content.innerHTML = `
            <div class="flex flex-col items-center justify-center py-16 text-center space-y-4">
                <svg class="w-10 h-10 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10A8 8 0 11.001 9.999 8 8 0 0118 10zm-8-4a.75.75 0 00-.75.75v3.5a.75.75 0 001.5 0v-3.5A.75.75 0 0010 6zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC] font-semibold">${escapeHtml(message)}</p>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Please try again or refresh the page.</p>
            </div>
        `;
        showReceiptModal();
    }
}

function formatCurrency(amount) {
    return currencyFormatter.format(isNaN(amount) ? 0 : amount);
}

function formatDate(value) {
    try {
        return dateTimeFormatter.format(new Date(value));
    } catch {
        return 'Not available';
    }
}

function capitalize(value) {
    if (!value || typeof value !== 'string') {
        return '';
    }

    return value.charAt(0).toUpperCase() + value.slice(1);
}

function escapeHtml(value) {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}


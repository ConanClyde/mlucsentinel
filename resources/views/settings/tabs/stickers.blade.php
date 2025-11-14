<div id="content-stickers" class="settings-content hidden space-y-4 md:space-y-6">
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Sticker Color Palette</h3>
            <button onclick="openAddStickerColorModal()" class="btn btn-primary text-sm">Add Color</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-[#161615] border-y border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Color Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Hex</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Key</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="sticker-palette-tbody">
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Sticker Rules</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Student Expiration (Years)</label>
                <input type="number" min="1" max="10" id="sticker-expiration-student-years" class="form-input w-full" value="4" />
            </div>
            <div>
                <label class="form-label">Staff Expiration (Years)</label>
                <input type="number" min="1" max="10" id="sticker-expiration-staff-years" class="form-input w-full" value="4" />
            </div>
            <div>
                <label class="form-label">Security Expiration (Years)</label>
                <input type="number" min="1" max="10" id="sticker-expiration-security-years" class="form-input w-full" value="4" />
            </div>
            <div>
                <label class="form-label">Stakeholder Expiration (Years)</label>
                <input type="number" min="1" max="10" id="sticker-expiration-stakeholder-years" class="form-input w-full" value="4" />
            </div>
            <div class="grid grid-cols-2 gap-4 md:col-span-2">
                <div>
                    <label class="form-label">Staff Color</label>
                    <select id="staff-color" class="form-input w-full"></select>
                </div>
                <div>
                    <label class="form-label">Security Color</label>
                    <select id="security-color" class="form-input w-full"></select>
                </div>
            </div>
        </div>
        <div class="mt-6">
            <h4 class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Student Plate Mapping</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Last digit 1-2</label>
                    <select id="student-color-12" class="form-input w-full"></select>
                </div>
                <div>
                    <label class="form-label">Last digit 3-4</label>
                    <select id="student-color-34" class="form-input w-full"></select>
                </div>
                <div>
                    <label class="form-label">Last digit 5-6</label>
                    <select id="student-color-56" class="form-input w-full"></select>
                </div>
                <div>
                    <label class="form-label">Last digit 7-8</label>
                    <select id="student-color-78" class="form-input w-full"></select>
                </div>
                <div>
                    <label class="form-label">Last digit 9-0</label>
                    <select id="student-color-90" class="form-input w-full"></select>
                </div>
                <div>
                    <label class="form-label">No plate</label>
                    <select id="student-color-no_plate" class="form-input w-full"></select>
                </div>
            </div>
        </div>
        <div class="mt-6">
            <button id="save-sticker-config" class="btn btn-primary">Save Rules</button>
        </div>
    </div>

    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Stakeholder Types & Colors</h3>
            <div class="flex items-center gap-2">
                <input id="new-stakeholder-type-name" type="text" class="form-input" placeholder="New type (e.g., Contractor)" />
                <button id="add-stakeholder-type" class="btn btn-secondary">Add Type</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <th class="px-4 py-2 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Type Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Sticker Color</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="stakeholder-types-tbody">
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

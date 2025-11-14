<div id="content-sticker-rules" class="settings-content hidden">
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Expiration Years</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
        </div>

        <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 mt-6">Color Assignment Rules</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="form-label">Staff Color</label>
                <select id="staff-color" class="form-input w-full"></select>
            </div>
            <div>
                <label class="form-label">Security Color</label>
                <select id="security-color" class="form-input w-full"></select>
            </div>
        </div>

        <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 mt-6">Student Plate Number Rules</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="form-label">Plates ending in 1-2</label>
                <select id="student-color-12" class="form-input w-full"></select>
            </div>
            <div>
                <label class="form-label">Plates ending in 3-4</label>
                <select id="student-color-34" class="form-input w-full"></select>
            </div>
            <div>
                <label class="form-label">Plates ending in 5-6</label>
                <select id="student-color-56" class="form-input w-full"></select>
            </div>
            <div>
                <label class="form-label">Plates ending in 7-8</label>
                <select id="student-color-78" class="form-input w-full"></select>
            </div>
            <div>
                <label class="form-label">Plates ending in 9-0</label>
                <select id="student-color-90" class="form-input w-full"></select>
            </div>
            <div>
                <label class="form-label">No Plate Number</label>
                <select id="student-color-no_plate" class="form-input w-full"></select>
            </div>
        </div>

        <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4 mt-6">Stakeholder Type Color Rules</h3>
        <div id="stakeholder-color-rules" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Populated by JS -->
        </div>

        <div class="flex justify-end mt-6">
            <button id="save-sticker-config" class="btn-primary">
                Save Sticker Rules
            </button>
        </div>
    </div>
</div>

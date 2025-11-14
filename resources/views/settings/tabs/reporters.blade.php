<!-- Reporters Settings -->
<div id="content-reporters" class="settings-content hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Reporter Roles</h3>
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Manage reporter roles and which user types they can report</p>
        </div>
        <button onclick="openAddReporterRoleModal()" class="btn btn-primary text-sm">
            Add Reporter Role
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-[#161615] border-y border-[#e3e3e0] dark:border-[#3E3E3A]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Can Report</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="reporter-roles-table-body" class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Loading reporter roles...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@include('settings.modals.reporter-roles-modals')

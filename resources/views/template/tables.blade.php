<div class="flex justify-between items-center mb-8 pb-4 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
    <div>
        <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-1">User Management</h1>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Manage your team members and their account permissions</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <x-heroicon-o-plus class="w-4 h-4 inline-block mr-2" />Add New User
    </button>
</div>

<!-- Users Table -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            <tr>
                <td>1</td>
                <td>
                    <div class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">John Doe</div>
                    <div class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Admin</div>
                </td>
                <td>john@example.com</td>
                <td><span class="badge badge-active">Active</span></td>
                <td class="flex items-center gap-2">
                    <button class="btn-view" onclick="openViewModal(1, 'John Doe', 'john@example.com', 'Admin')" title="View">
                        <x-heroicon-s-eye class="w-4 h-4" />
                    </button>
                    <button class="btn-edit" onclick="openEditModal(1, 'John Doe', 'john@example.com', 'Admin')" title="Edit">
                        <x-heroicon-s-pencil class="w-4 h-4" />
                    </button>
                    <button class="btn-disable">
                        <x-heroicon-s-trash class="w-4 h-4" />
                    </button>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>
                    <div class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Jane Smith</div>
                    <div class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">User</div>
                </td>
                <td>jane@example.com</td>
                <td><span class="badge badge-active">Active</span></td>
                <td class="flex items-center gap-2">
                    <button class="btn-view" onclick="openViewModal(2, 'Jane Smith', 'jane@example.com', 'User')" title="View">
                        <x-heroicon-s-eye class="w-4 h-4" />
                    </button>
                    <button class="btn-edit" onclick="openEditModal(2, 'Jane Smith', 'jane@example.com', 'User')" title="Edit">
                        <x-heroicon-s-pencil class="w-4 h-4" />
                    </button>
                    <button class="btn-delete" onclick="openDeleteModal(2, 'Jane Smith')" title="Delete">
                        <x-heroicon-s-trash class="w-4 h-4" />
                    </button>
                </td>
            </tr>
            <tr>
                <td>3</td>
                <td>
                    <div class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Bob Johnson</div>
                    <div class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">User</div>
                </td>
                <td>bob@example.com</td>
                <td><span class="badge badge-inactive">Inactive</span></td>
                <td class="flex items-center gap-2">
                    <button class="btn-view" onclick="openViewModal(3, 'Bob Johnson', 'bob@example.com', 'User')" title="View">
                        <x-heroicon-s-eye class="w-4 h-4" />
                    </button>
                    <button class="btn-edit" onclick="openEditModal(3, 'Bob Johnson', 'bob@example.com', 'User')" title="Edit">
                        <x-heroicon-s-pencil class="w-4 h-4" />
                    </button>
                    <button class="btn-delete" onclick="openDeleteModal(3, 'Bob Johnson')" title="Delete">
                        <x-heroicon-s-trash class="w-4 h-4" />
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

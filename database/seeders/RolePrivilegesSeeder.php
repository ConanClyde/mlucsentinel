<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use App\Models\Privilege;
use Illuminate\Database\Seeder;

class RolePrivilegesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder migrates the existing hardcoded role permissions
     * to the new database-driven privilege system.
     */
    public function run(): void
    {
        // Security Administrator Privileges - Full access to all user types
        $this->assignPrivilegesToRole('Security', [
            'view_dashboard',
            'view_dashboard_stats',
            'view_dashboard_patrol',
            'view_dashboard_analytics',
            'view_dashboard_heatmap',
            'export_dashboard',
            // Settings - Available to all
            'view_settings_appearance',
            'view_settings_notifications',
            'view_settings_security',
            // Settings - System configuration
            'view_settings_college',
            'view_settings_program',
            'view_settings_vehicle_type',
            'view_settings_location_type',
            'view_settings_fees',
            // All user types - full access
            'view_students', 'register_students', 'edit_students', 'delete_students',
            'view_staff', 'register_staff', 'edit_staff', 'delete_staff',
            'view_security', 'register_security', 'edit_security', 'delete_security',
            'view_stakeholders', 'register_stakeholders', 'edit_stakeholders', 'delete_stakeholders',
            'view_reporters', 'register_reporters', 'edit_reporters', 'delete_reporters',
            'view_administrators', 'register_administrators', 'edit_administrators', 'delete_administrators',
            // Vehicles
            'view_vehicles',
            'edit_vehicles',
            'delete_vehicles',
            // Patrol
            'view_patrol_monitor',
            'view_patrol_history',
            // Campus Map
            'manage_campus_map',
        ], 'Manages security personnel, patrol monitoring, and user registrations');

        // SAS (Student Affairs & Services) Administrator Privileges
        $this->assignPrivilegesToRole('SAS (Student Affairs & Services)', [
            'view_dashboard',
            'view_dashboard_stats',
            'view_dashboard_analytics',
            'view_dashboard_heatmap',
            'export_dashboard',
            // Settings - Available to all
            'view_settings_appearance',
            'view_settings_notifications',
            'view_settings_security',
            // Settings - System configuration
            'view_settings_college',
            'view_settings_program',
            'view_settings_vehicle_type',
            'view_settings_location_type',
            'view_settings_fees',
            'manage_reports',
            // Reporters only - full access
            'view_reporters', 'register_reporters', 'edit_reporters', 'delete_reporters',
            // All others - view only
            'view_students', 'view_staff', 'view_security', 'view_stakeholders', 'view_administrators',
            // Vehicles
            'view_vehicles',
            // Campus Map
            'view_campus_map',
        ], 'Manages student affairs, reporters, and student violation reports');

        // DRRM Administrator Privileges
        $this->assignPrivilegesToRole('DRRM', [
            'view_dashboard',
            'view_dashboard_stats',
            'view_dashboard_analytics',
            'view_dashboard_heatmap',
            'export_dashboard',
            // Settings - Available to all
            'view_settings_appearance',
            'view_settings_notifications',
            'view_settings_security',
            // Settings - System configuration
            'view_settings_college',
            'view_settings_program',
            'view_settings_vehicle_type',
            'view_settings_location_type',
            'view_settings_fees',
            // Reporters only - full access
            'view_reporters', 'register_reporters', 'edit_reporters', 'delete_reporters',
            // All others - view only
            'view_students', 'view_staff', 'view_security', 'view_stakeholders', 'view_administrators',
            // Vehicles
            'view_vehicles',
            // Campus Map
            'view_campus_map',
        ], 'Disaster Risk Reduction and Management administrator with reporter management');

        // Chancellor Administrator Privileges (Reports)
        $this->assignPrivilegesToRole('Chancellor', [
            'view_dashboard',
            'view_dashboard_stats',
            'view_dashboard_revenue',
            'view_dashboard_analytics',
            'view_dashboard_heatmap',
            'export_dashboard',
            // Settings - Available to all
            'view_settings_appearance',
            'view_settings_notifications',
            'view_settings_security',
            // Settings - System configuration
            'view_settings_college',
            'view_settings_program',
            'view_settings_vehicle_type',
            'view_settings_location_type',
            'view_settings_fees',
            'manage_reports',
            // All user types - view only
            'view_students', 'view_staff', 'view_security', 'view_stakeholders', 'view_reporters', 'view_administrators',
            // Vehicles
            'view_vehicles',
            // Campus Map
            'view_campus_map',
        ], 'Executive oversight with read-only access and full report viewing');

        // Marketing Administrator Privileges (Stickers + Read-only)
        $this->assignPrivilegesToRole('Marketing', [
            'view_dashboard',
            'view_dashboard_stats',
            'view_dashboard_revenue',
            'view_dashboard_analytics',
            'export_dashboard',
            // Settings - Available to all
            'view_settings_appearance',
            'view_settings_notifications',
            'view_settings_security',
            // Settings - System configuration
            'view_settings_college',
            'view_settings_program',
            'view_settings_vehicle_type',
            'view_settings_location_type',
            'view_settings_fees',
            'view_stickers',
            'download_stickers',
            // All user types - view only
            'view_students', 'view_staff', 'view_security', 'view_stakeholders', 'view_reporters', 'view_administrators',
            // Vehicles
            'view_vehicles',
            // Campus Map
            'view_campus_map',
        ], 'Manages vehicle stickers and marketing materials');

        // Planning Administrator Privileges (Read-only)
        $this->assignPrivilegesToRole('Planning', [
            'view_dashboard',
            'view_dashboard_stats',
            'view_dashboard_analytics',
            'view_dashboard_heatmap',
            'export_dashboard',
            // Settings - Available to all
            'view_settings_appearance',
            'view_settings_notifications',
            'view_settings_security',
            // Settings - System configuration
            'view_settings_college',
            'view_settings_program',
            'view_settings_vehicle_type',
            'view_settings_location_type',
            'view_settings_fees',
            // All user types - view only
            'view_students', 'view_staff', 'view_security', 'view_stakeholders', 'view_reporters', 'view_administrators',
            // Vehicles
            'view_vehicles',
            // Campus Map
            'view_campus_map',
        ], 'Planning and analytics with read-only access');

        // Auxiliary Services Administrator Privileges (Read-only)
        $this->assignPrivilegesToRole('Auxiliary Services', [
            'view_dashboard',
            'view_dashboard_stats',
            // Settings - Available to all
            'view_settings_appearance',
            'view_settings_notifications',
            'view_settings_security',
            // Settings - System configuration
            'view_settings_college',
            'view_settings_program',
            'view_settings_vehicle_type',
            'view_settings_location_type',
            'view_settings_fees',
            // All user types - view only
            'view_students', 'view_staff', 'view_security', 'view_stakeholders', 'view_reporters', 'view_administrators',
            // Vehicles
            'view_vehicles',
            // Campus Map
            'view_campus_map',
        ], 'Auxiliary services support with read-only access');
    }

    /**
     * Assign privileges to a specific role.
     */
    private function assignPrivilegesToRole(string $roleName, array $privilegeNames, string $description): void
    {
        $role = AdminRole::where('name', $roleName)->first();

        if (! $role) {
            $this->command->warn("Role '{$roleName}' not found. Skipping...");

            return;
        }

        // Update role description and flags
        // Check if role has any register/edit/delete privileges for any user type
        $hasRegister = in_array('register_students', $privilegeNames) ||
                       in_array('register_staff', $privilegeNames) ||
                       in_array('register_security', $privilegeNames) ||
                       in_array('register_stakeholders', $privilegeNames) ||
                       in_array('register_reporters', $privilegeNames) ||
                       in_array('register_administrators', $privilegeNames);

        $hasEdit = in_array('edit_students', $privilegeNames) ||
                   in_array('edit_staff', $privilegeNames) ||
                   in_array('edit_security', $privilegeNames) ||
                   in_array('edit_stakeholders', $privilegeNames) ||
                   in_array('edit_reporters', $privilegeNames) ||
                   in_array('edit_administrators', $privilegeNames);

        $hasDelete = in_array('delete_students', $privilegeNames) ||
                     in_array('delete_staff', $privilegeNames) ||
                     in_array('delete_security', $privilegeNames) ||
                     in_array('delete_stakeholders', $privilegeNames) ||
                     in_array('delete_reporters', $privilegeNames) ||
                     in_array('delete_administrators', $privilegeNames);

        $role->update([
            'description' => $description,
            'is_active' => true,
            'can_register_users' => $hasRegister,
            'can_edit_users' => $hasEdit,
            'can_delete_users' => $hasDelete,
        ]);

        // Get privilege IDs
        $privileges = Privilege::whereIn('name', $privilegeNames)->pluck('id');

        // Sync privileges (removes old, adds new)
        $role->privileges()->sync($privileges);

        $this->command->info('Assigned '.count($privileges)." privileges to '{$roleName}' role.");
    }
}

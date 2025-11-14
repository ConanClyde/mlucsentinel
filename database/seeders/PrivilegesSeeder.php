<?php

namespace Database\Seeders;

use App\Models\Privilege;
use Illuminate\Database\Seeder;

class PrivilegesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $privileges = [
            // Dashboard Privileges
            [
                'name' => 'view_dashboard',
                'display_name' => 'View Dashboard',
                'description' => 'Can view the admin dashboard (basic access)',
                'category' => 'dashboard',
            ],
            [
                'name' => 'view_dashboard_stats',
                'display_name' => 'View Dashboard Statistics',
                'description' => 'Can view basic statistics cards (users, vehicles, reports)',
                'category' => 'dashboard',
            ],
            [
                'name' => 'view_dashboard_revenue',
                'display_name' => 'View Revenue & Payments',
                'description' => 'Can view revenue and payment statistics',
                'category' => 'dashboard',
            ],
            [
                'name' => 'view_dashboard_patrol',
                'display_name' => 'View Patrol Statistics',
                'description' => 'Can view patrol check-in statistics and coverage',
                'category' => 'dashboard',
            ],
            [
                'name' => 'view_dashboard_analytics',
                'display_name' => 'View Analytics & Charts',
                'description' => 'Can view analytics charts, graphs, and visualizations',
                'category' => 'dashboard',
            ],
            [
                'name' => 'view_dashboard_heatmap',
                'display_name' => 'View Violation Heatmap',
                'description' => 'Can view violation location heatmap',
                'category' => 'dashboard',
            ],
            [
                'name' => 'export_dashboard',
                'display_name' => 'Export Dashboard Data',
                'description' => 'Can export dashboard statistics to CSV',
                'category' => 'dashboard',
            ],

            // Users Management Privileges - Students
            [
                'name' => 'view_students',
                'display_name' => 'View Students',
                'description' => 'Can view list of all students',
                'category' => 'users',
            ],
            [
                'name' => 'register_students',
                'display_name' => 'Register Students',
                'description' => 'Can register new students',
                'category' => 'users',
            ],
            [
                'name' => 'edit_students',
                'display_name' => 'Edit Students',
                'description' => 'Can edit existing student information',
                'category' => 'users',
            ],
            [
                'name' => 'delete_students',
                'display_name' => 'Delete Students',
                'description' => 'Can delete students',
                'category' => 'users',
            ],

            // Users Management Privileges - Staff
            [
                'name' => 'view_staff',
                'display_name' => 'View Staff',
                'description' => 'Can view list of all staff members',
                'category' => 'users',
            ],
            [
                'name' => 'register_staff',
                'display_name' => 'Register Staff',
                'description' => 'Can register new staff members',
                'category' => 'users',
            ],
            [
                'name' => 'edit_staff',
                'display_name' => 'Edit Staff',
                'description' => 'Can edit existing staff information',
                'category' => 'users',
            ],
            [
                'name' => 'delete_staff',
                'display_name' => 'Delete Staff',
                'description' => 'Can delete staff members',
                'category' => 'users',
            ],

            // Users Management Privileges - Security
            [
                'name' => 'view_security',
                'display_name' => 'View Security',
                'description' => 'Can view list of all security personnel',
                'category' => 'users',
            ],
            [
                'name' => 'register_security',
                'display_name' => 'Register Security',
                'description' => 'Can register new security personnel',
                'category' => 'users',
            ],
            [
                'name' => 'edit_security',
                'display_name' => 'Edit Security',
                'description' => 'Can edit existing security personnel information',
                'category' => 'users',
            ],
            [
                'name' => 'delete_security',
                'display_name' => 'Delete Security',
                'description' => 'Can delete security personnel',
                'category' => 'users',
            ],

            // Users Management Privileges - Stakeholders
            [
                'name' => 'view_stakeholders',
                'display_name' => 'View Stakeholders',
                'description' => 'Can view list of all stakeholders',
                'category' => 'users',
            ],
            [
                'name' => 'register_stakeholders',
                'display_name' => 'Register Stakeholders',
                'description' => 'Can register new stakeholders',
                'category' => 'users',
            ],
            [
                'name' => 'edit_stakeholders',
                'display_name' => 'Edit Stakeholders',
                'description' => 'Can edit existing stakeholder information',
                'category' => 'users',
            ],
            [
                'name' => 'delete_stakeholders',
                'display_name' => 'Delete Stakeholders',
                'description' => 'Can delete stakeholders',
                'category' => 'users',
            ],

            // Users Management Privileges - Reporters
            [
                'name' => 'view_reporters',
                'display_name' => 'View Reporters',
                'description' => 'Can view list of all reporters',
                'category' => 'users',
            ],
            [
                'name' => 'register_reporters',
                'display_name' => 'Register Reporters',
                'description' => 'Can register new reporters',
                'category' => 'users',
            ],
            [
                'name' => 'edit_reporters',
                'display_name' => 'Edit Reporters',
                'description' => 'Can edit existing reporter information',
                'category' => 'users',
            ],
            [
                'name' => 'delete_reporters',
                'display_name' => 'Delete Reporters',
                'description' => 'Can delete reporters',
                'category' => 'users',
            ],

            // Users Management Privileges - Administrators
            [
                'name' => 'view_administrators',
                'display_name' => 'View Administrators',
                'description' => 'Can view list of all administrators',
                'category' => 'users',
            ],
            [
                'name' => 'register_administrators',
                'display_name' => 'Register Administrators',
                'description' => 'Can register new administrators',
                'category' => 'users',
            ],
            [
                'name' => 'edit_administrators',
                'display_name' => 'Edit Administrators',
                'description' => 'Can edit existing administrator information',
                'category' => 'users',
            ],
            [
                'name' => 'delete_administrators',
                'display_name' => 'Delete Administrators',
                'description' => 'Can delete administrators',
                'category' => 'users',
            ],

            // Users Management Privileges - Pending Registrations
            [
                'name' => 'manage_pending_registrations',
                'display_name' => 'Manage Pending Registrations',
                'description' => 'Can view, approve, reject, and manage pending registrations',
                'category' => 'users',
            ],

            // Reports Privileges (consolidated)
            [
                'name' => 'manage_reports',
                'display_name' => 'View & Manage Reports',
                'description' => 'Can view, export, and manage violation reports',
                'category' => 'reports',
            ],

            // Vehicles Privileges
            [
                'name' => 'view_vehicles',
                'display_name' => 'View Vehicles',
                'description' => 'Can view list of all registered vehicles',
                'category' => 'vehicles',
            ],
            [
                'name' => 'edit_vehicles',
                'display_name' => 'Edit Vehicles',
                'description' => 'Can edit vehicle information',
                'category' => 'vehicles',
            ],
            [
                'name' => 'delete_vehicles',
                'display_name' => 'Delete Vehicles',
                'description' => 'Can delete vehicles',
                'category' => 'vehicles',
            ],

            // Stickers Privileges
            [
                'name' => 'view_stickers',
                'display_name' => 'View Stickers',
                'description' => 'Can view vehicle stickers page',
                'category' => 'stickers',
            ],
            [
                'name' => 'download_stickers',
                'display_name' => 'Download Stickers',
                'description' => 'Can download vehicle stickers',
                'category' => 'stickers',
            ],

            // Patrol Privileges
            [
                'name' => 'view_patrol_monitor',
                'display_name' => 'View Patrol Monitor',
                'description' => 'Can view real-time patrol monitoring page',
                'category' => 'patrol',
            ],
            [
                'name' => 'view_patrol_history',
                'display_name' => 'View Patrol History',
                'description' => 'Can view patrol history and logs',
                'category' => 'patrol',
            ],

            // Campus Map Privileges
            [
                'name' => 'manage_campus_map',
                'display_name' => 'Manage Campus Map',
                'description' => 'Can view, edit, and delete campus map locations and patrol points',
                'category' => 'campus_map',
            ],

            // Settings Privileges
            [
                'name' => 'view_settings_appearance',
                'display_name' => 'View Appearance Settings',
                'description' => 'Can view and manage appearance settings (theme, colors)',
                'category' => 'settings',
            ],
            [
                'name' => 'view_settings_notifications',
                'display_name' => 'View Notification Settings',
                'description' => 'Can view and manage notification preferences',
                'category' => 'settings',
            ],
            [
                'name' => 'view_settings_security',
                'display_name' => 'View Security Settings',
                'description' => 'Can view and manage security settings (2FA, password, activity logs)',
                'category' => 'settings',
            ],
            [
                'name' => 'view_settings_college',
                'display_name' => 'View College Settings',
                'description' => 'Can view and manage college settings',
                'category' => 'settings',
            ],
            [
                'name' => 'view_settings_program',
                'display_name' => 'View Program Settings',
                'description' => 'Can view and manage program settings',
                'category' => 'settings',
            ],
            [
                'name' => 'view_settings_vehicle_type',
                'display_name' => 'View Vehicle Type Settings',
                'description' => 'Can view and manage vehicle type settings',
                'category' => 'settings',
            ],
            [
                'name' => 'view_settings_location_type',
                'display_name' => 'View Location Type Settings',
                'description' => 'Can view and manage location type settings',
                'category' => 'settings',
            ],
            [
                'name' => 'view_settings_fees',
                'display_name' => 'View Fees Settings',
                'description' => 'Can view and manage fees settings',
                'category' => 'settings',
            ],
            [
                'name' => 'manage_settings',
                'display_name' => 'Manage Settings',
                'description' => 'Can manage system settings and configurations',
                'category' => 'settings',
            ],
            [
                'name' => 'manage_fees',
                'display_name' => 'Manage Fees',
                'description' => 'Can manage fees and payment amounts',
                'category' => 'settings',
            ],
            [
                'name' => 'manage_roles',
                'display_name' => 'Manage Roles',
                'description' => 'Can manage admin roles and privileges',
                'category' => 'settings',
            ],
        ];

        foreach ($privileges as $privilege) {
            Privilege::updateOrCreate(
                ['name' => $privilege['name']],
                $privilege
            );
        }
    }
}

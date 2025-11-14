<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\ReporterRole;
use Illuminate\Database\Seeder;

class ReporterRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allUserTypes = collect(UserType::cases())->map(fn ($type) => $type->value)->toArray();

        $roles = [
            [
                'name' => 'SBO (Student Body Organization)',
                'description' => 'Student Body Organization representatives tasked with documenting student-related violations and activities.',
                'default_expiration_years' => null,
                'is_active' => true,
                'can_report' => [UserType::Student->value],
            ],
            [
                'name' => 'DRRM Facilitators',
                'description' => 'Disaster Risk Reduction and Management facilitators empowered to escalate incidents involving any campus user type.',
                'default_expiration_years' => null,
                'is_active' => true,
                'can_report' => $allUserTypes,
            ],
            [
                'name' => 'SAS Facilitators',
                'description' => 'Student Affairs and Services facilitators monitoring campus-wide events with authority to report any user type.',
                'default_expiration_years' => null,
                'is_active' => true,
                'can_report' => $allUserTypes,
            ],
            [
                'name' => 'Security Guard',
                'description' => 'Campus security personnel patrolling all zones and permitted to report any user type.',
                'default_expiration_years' => null,
                'is_active' => true,
                'can_report' => $allUserTypes,
            ],
        ];

        foreach ($roles as $roleData) {
            $canReport = $roleData['can_report'];
            unset($roleData['can_report']);

            $role = ReporterRole::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            // Sync the user types this role can report
            $role->syncUserTypes($canReport);
        }
    }
}

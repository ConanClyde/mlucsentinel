# API Documentation

This document summarizes the authenticated HTTP API endpoints exposed by the application. Most API routes sit behind the standard `web` guard and are reachable only after login. Additional middleware—such as `user.type`, `privilege`, `global.admin`, `throttle`, and `file.upload.security`—further restrict access.

## Authentication & Session

- `GET /login` — Render login form. Guest only.
- `POST /login` — Attempt login (rate limited to 5 requests per minute).
- `GET /2fa/verify`, `POST /2fa/verify` — Two-factor challenge during login.
- `GET /forgot-password` — Render reset-request form.
- `POST /forgot-password` — Send reset code (rate limited to 5/hour).
- `GET /reset-password` — Render password reset form.
- `POST /reset-password` — Reset password (rate limited to 5/hour).
- `POST /validate-reset-code` — Validate reset code (rate limited to 10/hour).
- `GET /logout` — Show logout confirmation when authenticated.
- `POST /logout` — Terminate the session.

## Notifications (authenticated)
Middleware: `auth`

- `GET /notifications` — List notifications.
- `POST /notifications/{id}/read` — Mark a specific notification as read.
- `POST /notifications/mark-all-read` — Mark every notification as read.
- `DELETE /notifications/clear-all` — Remove all notifications.

## Profile & Settings (authenticated)
Middleware: `auth` (additional `global.admin` for some settings)

- `GET /profile` — View profile.
- `POST /profile/update` — Update profile details.
- `POST /profile/change-password` — Change password.
- `POST /profile/verify-password` — Verify current password.
- `DELETE /profile/delete` — Delete the account.
- `GET /settings` — View personal settings.
- `GET /settings/activity-logs` — Fetch activity logs.
- `POST /settings/2fa/enable` — Begin 2FA setup.
- `POST /settings/2fa/confirm` — Confirm 2FA setup.
- `POST /settings/2fa/disable` — Disable 2FA.
- `POST /settings/2fa/recovery-codes` — Fetch recovery codes.
- `POST /settings/2fa/recovery-codes/regenerate` — Regenerate recovery codes.
- `GET /settings/roles` — View admin role management (global administrators).

## Admin APIs
Middleware: `auth`, `user.type:global_administrator,administrator`, often `throttle:60,1` and privilege checks.

### Campus Map
- `GET /campus-map` — Campus map view (`privilege:view_campus_map`).
- `GET /campus-map/download-stickers` — Download full sticker set (`privilege:view_campus_map`).
- `GET /api/map-locations` — List map locations (`privilege:view_campus_map`).
- `GET /api/map-locations/{location}` — View single location (`privilege:view_campus_map`).
- `POST /api/map-locations` — Create location (`privilege:edit_campus_map`).
- `PUT /api/map-locations/{location}` — Update location (`privilege:edit_campus_map`).
- `DELETE /api/map-locations/{location}` — Delete location (`privilege:edit_campus_map`).
- `POST /api/map-locations/{location}/toggle-active` — Toggle active state (`privilege:edit_campus_map`).
- `GET /api/map-location-types` — List location types (`privilege:view_campus_map` or `view_settings_location_type` depending on context).
- `POST /api/map-location-types` — Create type (`privilege:view_settings_location_type`).
- `PUT /api/map-location-types/{mapLocationType}` — Update type (`privilege:view_settings_location_type`).
- `DELETE /api/map-location-types/{mapLocationType}` — Delete type (`privilege:view_settings_location_type`).

### Colleges & Programs
Middleware: `privilege:view_settings_college` (for list/update), `privilege:view_settings_program` for programs.

- `GET /api/colleges` — List colleges.
- `POST /api/colleges` — Create college.
- `PUT /api/colleges/{college}` — Update college.
- `DELETE /api/colleges/{college}` — Delete college.
- `GET /api/colleges/{college}/programs` — List programs for a college.
- `GET /api/programs` — List programs.
- `POST /api/programs` — Create program.
- `PUT /api/programs/{program}` — Update program.
- `DELETE /api/programs/{program}` — Delete program.

### Fees

- `GET /api/fees` — List fees (`privilege:view_settings_fees`).
- `PUT /api/fees/{fee}` — Update fee (`privilege:manage_fees`, rate limited to 10/minute).

### Vehicle Types
Middleware: `privilege:view_settings_vehicle_type`

- `GET /api/vehicle-types` — List vehicle types.
- `POST /api/vehicle-types` — Create vehicle type.
- `PUT /api/vehicle-types/{vehicleType}` — Update vehicle type.
- `DELETE /api/vehicle-types/{vehicleType}` — Delete vehicle type.

### Admin Roles & Privileges (global administrators only)
- `GET /api/admin-roles` — List admin roles.
- `GET /api/admin-roles/privileges` — List available privileges.
- `POST /api/admin-roles` — Create admin role.
- `PUT /api/admin-roles/{role}` — Update admin role.
- `DELETE /api/admin-roles/{role}` — Delete admin role.

### Reporter Roles (global administrators only)
- `GET /api/reporter-roles` — List reporter roles.
- `GET /api/reporter-roles/user-types` — Available user types for reporter roles.
- `POST /api/reporter-roles` — Create role.
- `PUT /api/reporter-roles/{role}` — Update role.
- `DELETE /api/reporter-roles/{role}` — Delete role.
- `POST /api/reporter-roles/{role}/toggle-active` — Toggle active state.

### Stakeholder Types & Sticker Configuration (global administrators only)
- `GET /api/stakeholder-types` — List stakeholder types.
- `POST /api/stakeholder-types` — Create type.
- `PUT /api/stakeholder-types/{stakeholderType}` — Update type.
- `DELETE /api/stakeholder-types/{stakeholderType}` — Delete type.
- `GET /api/settings/sticker-config` — Fetch sticker issuance configuration.
- `PUT /api/settings/sticker-config` — Update sticker configuration.
- `GET /api/settings/sticker-palette` — Fetch palette.
- `POST /api/settings/sticker-palette` — Add sticker color.
- `PUT /api/settings/sticker-palette/{key}` — Update sticker color.
- `DELETE /api/settings/sticker-palette/{key}` — Delete sticker color.

### Bulk Operations
- `POST /api/bulk/users/import` — Import users (CSV/Excel) (`privilege:edit_students`).
- `POST /api/bulk/users/update` — Bulk update users.
- `POST /api/bulk/users/delete` — Bulk delete users.
- `POST /api/bulk/users/status` — Bulk status update.
- `POST /api/bulk/vehicles/update` — Bulk update vehicles (`privilege:edit_vehicles`).
- `POST /api/bulk/vehicles/delete` — Bulk delete vehicles.
- `POST /api/bulk/vehicles/status` — Bulk status update.

### Metrics (dashboard analytics)
- `GET /api/metrics/overview` — Aggregate dashboard metrics.
- `GET /api/metrics/violations-per-day` — Daily violation stats.
- `GET /api/metrics/payments-monthly` — Monthly payment stats.
- `GET /api/metrics/patrol-24h` — 24h patrol stats.

## Admin Views & Actions (privilege-gated)
Although not JSON APIs, the following endpoints serve HTML/admin workflows:

- `GET /users` — Admin user list (`privilege:view_students`).
- `GET /vehicles` — Vehicle management view (`privilege:view_vehicles`).
- `GET /vehicles/data` — Vehicle data table.
- `DELETE /vehicles/{vehicle}` — Delete vehicle (`privilege:delete_vehicles`).
- `GET /reports` — Reports management (`privilege:manage_reports`).
- `GET /reports/export` — Export reports.
- `PUT /reports/{report}/status` — Update report status.
- `GET /dashboard/export` — Export dashboard (`privilege:export_dashboard`).
- `GET /stickers` et al. — Manage sticker issuance (various privileges).
- `GET /patrol-history` — Patrol logs view (`privilege:view_patrol_monitor`).
- `GET /patrol-history/export` — Export patrol logs.

## Registration Workflows
All registration routes use `file.upload.security` middleware when file uploads are accepted.

- Student Registration (`privilege:register_students`)
  - `GET /registration/student`
  - `POST /registration/student`
  - `POST` checks for email, student ID, license number, plate number.
- Staff Registration (`privilege:register_staff`)
  - Similar endpoints for staff.
- Security Registration (`privilege:register_security`)
  - Includes security ID check.
- Stakeholder Registration (`privilege:register_stakeholders`)
  - Includes email/license/plate checks.
- Reporter Registration (`privilege:register_reporters`)
  - Form, create, and email check endpoints.
- Administrator Registration (global admin)
  - Form, create, and email validation.

## Reporter & Security APIs
Middleware: `auth`, `user.type` combinations.

- `GET /report-user` — Vehicle lookup form (reporters & security).
- `GET /report-user/{vehicle}` — Prefilled report form.
- `POST /report-user/search` — Search vehicle by plate/license.
- `POST /report-user/submit` — Submit violation report (`file.upload.security`).
- `GET /my-reports` — Reporter’s submitted reports.
- `GET /my-vehicles` — Security user vehicle list.
- Patrol (`security` only)
  - `GET /security/patrol-scanner` — QR scanner view.
  - `GET /security/patrol-checkin` — Manual check-in form.
  - `POST /security/patrol-checkin` — Submit check-in.
  - `GET /security/patrol-history` — View personal patrol history.

## Broadcasting
- `Broadcast::routes(['middleware' => ['web', 'auth']])` — Authenticated broadcasting endpoints for Echo/Reverb integration.

---

### Notes

- **Authentication**: All `/api/**` endpoints use session authentication (`web` guard). Ensure the client retains cookies or uses Sanctum if available.
- **Authorization**: Access is enforced via `user.type`, `privilege`, and `global.admin` middleware. Verify assigned privileges before calling the API.
- **Rate Limiting**: Login, password workflows, generic API access, and fee updates have rate limits to prevent abuse.
- **File Uploads**: Endpoints with `file.upload.security` enforce file integrity checks (see `App\Rules\SecureFileUpload`).


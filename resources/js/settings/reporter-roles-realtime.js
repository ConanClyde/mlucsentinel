/**
 * Reporter Roles Real-time Manager
 * Handles real-time updates for reporter roles via Laravel Echo
 */

export class ReporterRolesRealtime {
    constructor() {
        this.channel = null;
        this.roles = [];
    }

    /**
     * Initialize real-time updates
     */
    init(initialRoles = []) {
        this.roles = initialRoles;
        this.setupEchoListeners();
        console.log('✅ Reporter Roles real-time initialized');
    }

    /**
     * Setup Laravel Echo listeners
     */
    setupEchoListeners() {
        if (!window.Echo) {
            console.warn('⚠️ Echo not available for reporter roles real-time');
            return;
        }

        // Listen to reporter-roles channel
        this.channel = window.Echo.channel('reporter-roles');

        // Listen for role created event
        this.channel.listen('.reporter-role.created', (event) => {
            console.log('📢 Reporter role created:', event);
            this.handleRoleCreated(event.role);
        });

        // Listen for role updated event
        this.channel.listen('.reporter-role.updated', (event) => {
            console.log('📢 Reporter role updated:', event);
            this.handleRoleUpdated(event.role);
        });

        // Listen for role deleted event
        this.channel.listen('.reporter-role.deleted', (event) => {
            console.log('📢 Reporter role deleted:', event);
            this.handleRoleDeleted(event.roleId);
        });

        console.log('✅ Reporter Roles Echo listeners setup');
    }

    /**
     * Handle role created event
     */
    handleRoleCreated(role) {
        // Add to local array if not exists
        const exists = this.roles.find(r => r.id === role.id);
        if (!exists) {
            this.roles.unshift(role);
        }

        // Trigger reload if the reporter roles module is loaded
        if (window.loadReporterRoles && typeof window.loadReporterRoles === 'function') {
            window.loadReporterRoles();
        }
    }

    /**
     * Handle role updated event
     */
    handleRoleUpdated(role) {
        // Update in local array
        const index = this.roles.findIndex(r => r.id === role.id);
        if (index !== -1) {
            this.roles[index] = role;
        }

        // Trigger reload if the reporter roles module is loaded
        if (window.loadReporterRoles && typeof window.loadReporterRoles === 'function') {
            window.loadReporterRoles();
        }
    }

    /**
     * Handle role deleted event
     */
    handleRoleDeleted(roleId) {
        // Remove from local array
        this.roles = this.roles.filter(r => r.id !== roleId);

        // Trigger reload if the reporter roles module is loaded
        if (window.loadReporterRoles && typeof window.loadReporterRoles === 'function') {
            window.loadReporterRoles();
        }
    }

    /**
     * Cleanup
     */
    destroy() {
        if (this.channel) {
            window.Echo.leave('reporter-roles');
            this.channel = null;
        }
        console.log('🔌 Reporter Roles real-time disconnected');
    }
}

// Make it globally available
window.ReporterRolesRealtime = ReporterRolesRealtime;

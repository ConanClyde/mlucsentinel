// My Vehicles Real-time Updates

class MyVehiclesRealtime {
    constructor(userId) {
        this.userId = userId;
        this.vehiclesContainer = document.querySelector('.vehicles-grid');
        this.vehicleCount = document.querySelector('.vehicle-count');
        this.emptyState = document.querySelector('.empty-state');
    }

    init() {
        console.log('My Vehicles Real-time initialized for user:', this.userId);
        
        // Check if Echo is available
        if (typeof window.Echo === 'undefined') {
            console.error('❌ Laravel Echo is not initialized! Real-time updates will not work.');
            return;
        }
        
        console.log('✅ Echo is available, setting up listeners...');
        this.listenToVehicleUpdates();
    }

    listenToVehicleUpdates() {
        console.log('📡 Subscribing to vehicles channel...');
        
        window.Echo.channel('vehicles')
            .listen('.vehicle.updated', (event) => {
                console.log('🚗 Vehicle event received:', event);
                
                const { vehicle, action } = event;
                
                console.log(`Checking if vehicle belongs to user ${this.userId}:`, vehicle.user_id === this.userId);
                
                // Only process events for this user's vehicles
                if (vehicle.user_id === this.userId) {
                    console.log(`✅ Processing ${action} for vehicle:`, vehicle.id);
                    
                    switch (action) {
                        case 'created':
                            this.addVehicle(vehicle);
                            this.showNotification('Vehicle Added', 'A new vehicle has been registered to your account.', 'success');
                            break;
                        case 'updated':
                            this.updateVehicle(vehicle);
                            this.showNotification('Vehicle Updated', 'One of your vehicles has been updated.', 'info');
                            break;
                        case 'deleted':
                            this.removeVehicle(vehicle);
                            this.showNotification('Vehicle Deleted', 'One of your vehicles has been removed.', 'warning');
                            break;
                    }
                } else {
                    console.log('⏭️ Skipping - vehicle belongs to another user');
                }
            });
        
        console.log('✅ Vehicles channel listener set up successfully');
    }

    addVehicle(vehicle) {
        // Check if empty state is showing
        if (this.emptyState && !this.emptyState.classList.contains('hidden')) {
            this.emptyState.classList.add('hidden');
            this.vehiclesContainer.classList.remove('hidden');
        }

        // Create vehicle card
        const cardHtml = this.createVehicleCard(vehicle);
        this.vehiclesContainer.insertAdjacentHTML('afterbegin', cardHtml);
        
        // Update count
        this.updateVehicleCount(1);
        
        // Add entrance animation
        const newCard = this.vehiclesContainer.firstElementChild;
        newCard.classList.add('animate-fade-in');
    }

    updateVehicle(vehicle) {
        const existingCard = document.querySelector(`[data-vehicle-id="${vehicle.id}"]`);
        if (existingCard) {
            const newCardHtml = this.createVehicleCard(vehicle);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = newCardHtml;
            existingCard.replaceWith(tempDiv.firstElementChild);
        }
    }

    removeVehicle(vehicle) {
        const card = document.querySelector(`[data-vehicle-id="${vehicle.id}"]`);
        if (card) {
            // Add exit animation
            card.classList.add('animate-fade-out');
            setTimeout(() => {
                card.remove();
                this.updateVehicleCount(-1);
                
                // Show empty state if no vehicles left
                const remainingVehicles = this.vehiclesContainer.querySelectorAll('[data-vehicle-id]');
                if (remainingVehicles.length === 0) {
                    this.vehiclesContainer.classList.add('hidden');
                    if (this.emptyState) {
                        this.emptyState.classList.remove('hidden');
                    }
                }
            }, 300);
        }
    }

    createVehicleCard(vehicle) {
        const typeName = vehicle.type?.name || 'N/A';
        const plateNo = vehicle.plate_no || 'N/A';
        const stickerPath = vehicle.sticker || '';
        
        return `
            <div data-vehicle-id="${vehicle.id}" class="bg-white dark:bg-[#2a2a2a] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] overflow-hidden hover:shadow-md transition-shadow">
                <!-- Sticker Image -->
                <div class="bg-gray-50 dark:bg-[#1a1a1a] flex items-center justify-center border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                    ${stickerPath ? `
                        <img src="${window.location.origin}${stickerPath}" 
                             alt="Vehicle Sticker" 
                             class="w-full h-auto object-contain"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <div class="w-full h-32 bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 text-xs" style="display: none;">
                            No Image
                        </div>
                    ` : `
                        <div class="w-full h-32 bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 text-xs">
                            No Sticker
                        </div>
                    `}
                </div>

                <!-- Vehicle Details -->
                <div class="p-2 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="space-y-1 text-center">
                        <!-- Vehicle Type -->
                        <div class="text-[#706f6c] dark:text-[#A1A09A]">
                            <span class="text-xs font-medium">${typeName}</span>
                        </div>

                        <!-- Plate Number -->
                        <div>
                            <span class="text-sm font-bold text-[#1b1b18] dark:text-[#EDEDEC]">${plateNo}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    updateVehicleCount(delta) {
        if (this.vehicleCount) {
            const currentCount = parseInt(this.vehicleCount.textContent) || 0;
            this.vehicleCount.textContent = currentCount + delta;
        }
    }

    showNotification(title, message, type = 'info') {
        // Create a simple toast notification
        const toast = document.createElement('div');
        const bgColor = {
            success: 'bg-green-500',
            info: 'bg-blue-500',
            warning: 'bg-yellow-500',
            error: 'bg-red-500'
        }[type] || 'bg-gray-500';
        
        toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slide-in`;
        toast.innerHTML = `
            <div class="font-semibold">${title}</div>
            <div class="text-sm">${message}</div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            toast.classList.add('animate-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
}

// Export for use in blade template
window.MyVehiclesRealtime = MyVehiclesRealtime;


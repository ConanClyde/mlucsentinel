/**
 * Campus Map Management
 * 
 * Note: The following values are passed from Blade via window.campusMapData:
 * - window.campusMapData.locationTypes
 * - window.campusMapData.locations
 */

// Global state
let locationTypes = window.campusMapData?.locationTypes || [];
let locations = window.campusMapData?.locations || [];
let isDrawing = false;
let currentPoints = [];
let currentScale = 1;
let panX = 0;
let panY = 0;
let editingLocationId = null;
let locationsVisible = true;
let hasDragged = false;
let isDraggingPoint = false;
let draggingPointIndex = -1;
let deletingLocationId = null;
let aspectRatio = 1; // Default aspect ratio
let isDraggingLabel = false;
let customLabelPosition = null; // {x, y} or null to use calculated center
let activeTooltips = []; // Track all active tooltips for cleanup
let labelDragStart = null; // {mouseX, mouseY, labelX, labelY} - initial positions when drag starts

document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
    renderLocations();
    
    // Initialize real-time updates if available
    if (window.CampusMapRealtime) {
        window.campusMapRealtime = new window.CampusMapRealtime();
        window.campusMapRealtime.init(locations);
    }
});

function hideMapSkeleton() {
    const skeleton = document.getElementById('map-loading-skeleton');
    if (skeleton) {
        skeleton.classList.add('opacity-0', 'transition-opacity', 'duration-300');
        setTimeout(() => skeleton.remove(), 300);
    }
}

function initializeMapDimensions() {
    const img = document.getElementById('campus-map');
    const container = document.getElementById('campus-map-container');
    
    // Set container height based on image aspect ratio
    if (img && img.complete && img.naturalHeight > 0) {
        // Hide skeleton with fade-out transition
        hideMapSkeleton();
        
        aspectRatio = img.naturalHeight / img.naturalWidth; // Update global aspectRatio
        
        // Use CSS aspect-ratio for responsive sizing
        container.style.aspectRatio = `${img.naturalWidth} / ${img.naturalHeight}`;
        
        console.log('🖼️ Image dimensions:', img.naturalWidth, 'x', img.naturalHeight);
        console.log('📐 Aspect ratio:', aspectRatio);
        
        // DON'T update viewBox - keep it at 0 0 100 100 for percentage-based coordinates
        // The drawing canvas already uses viewBox="0 0 100 100" which works with our percentage system
        console.log('✅ Map dimensions initialized');
    }
}

// Export for module usage
window.campusMapModule = {
    initializeMapDimensions
};

// Expose globally for inline onload handler (handled in blade template now)
// window.initializeMapDimensions = initializeMapDimensions;

function initializeMap() {
    const container = document.getElementById('campus-map-container');
    const wrapper = document.getElementById('map-wrapper');
    const img = document.getElementById('campus-map');
    
    // Initialize dimensions when image loads
    if (img.complete) {
        initializeMapDimensions();
    } else {
        img.addEventListener('load', initializeMapDimensions);
    }
    
    // Handle image error
    img.addEventListener('error', function() {
        handleMapError(this);
    });
    
    // Zoom controls
    document.getElementById('zoom-in-btn').addEventListener('click', () => zoomMap(0.2));
    document.getElementById('zoom-out-btn').addEventListener('click', () => zoomMap(-0.2));
    document.getElementById('reset-zoom-btn').addEventListener('click', resetZoom);

    // Drawing mode
    document.getElementById('add-location-btn').addEventListener('click', enableDrawingMode);
    const cancelDrawingBtn = document.getElementById('cancel-drawing-btn');
    if (cancelDrawingBtn) {
        cancelDrawingBtn.addEventListener('click', cancelDrawingMode);
    }
    document.getElementById('undo-point-btn').addEventListener('click', undoLastPoint);
    // Complete polygon button removed - all actions handled by Save Location button

    // Map click for adding points
    container.addEventListener('click', handleMapClick);

    // Mouse drag to pan (works even while drawing)
    let isDraggingMap = false;
    let dragStartX, dragStartY;
    let startPanX, startPanY;
    
    container.addEventListener('mousedown', (e) => {
        if (e.button === 0) { // Left click
            // Only start map dragging if not dragging a point or label
            if (!isDraggingPoint && !isDraggingLabel) {
                isDraggingMap = true;
                hasDragged = false;
                dragStartX = e.clientX;
                dragStartY = e.clientY;
                startPanX = panX;
                startPanY = panY;
                container.style.cursor = 'grabbing';
                
                // Disable transition while dragging for smooth movement
                wrapper.style.transition = 'none';
            }
        } else if (e.button === 2 && editingLocationId) { // Right click in edit mode
            // Start label dragging from anywhere on the map
            e.preventDefault();
            e.stopPropagation();
            isDraggingLabel = true;
            hasDragged = false; // Prevent map dragging
            container.style.cursor = 'grabbing';
            
            // Store initial mouse position and label position for relative movement
            const wrapper = document.getElementById('map-wrapper');
            const rect = wrapper.getBoundingClientRect();
            
            // Get current label position
            let currentLabelX = 0;
            let currentLabelY = 0;
            if (customLabelPosition) {
                currentLabelX = customLabelPosition.x;
                currentLabelY = customLabelPosition.y;
            } else {
                // Use the calculated center from the location data
                const location = locations.find(l => l.id === editingLocationId);
                if (location && location.center_x && location.center_y) {
                    currentLabelX = location.center_x;
                    currentLabelY = location.center_y;
                }
            }
            
            // Calculate mouse position in percentage coordinates
            const mouseX = ((e.clientX - rect.left) / rect.width) * 100;
            const mouseY = ((e.clientY - rect.top) / rect.height) * 100;
            
            // Store the initial offset
            labelDragStart = {
                mouseX: mouseX,
                mouseY: mouseY,
                labelX: currentLabelX,
                labelY: currentLabelY
            };
        }
    });
    
    container.addEventListener('mousemove', (e) => {
        const wrapper = document.getElementById('map-wrapper');
        const rect = wrapper.getBoundingClientRect();
        
        // Handle label dragging (right-click) - works even when pointer leaves label
        if (isDraggingLabel && labelDragStart) {
            // Calculate current mouse position in percentage coordinates
            const currentMouseX = ((e.clientX - rect.left) / rect.width) * 100;
            const currentMouseY = ((e.clientY - rect.top) / rect.height) * 100;
            
            // Calculate the delta (how much mouse moved)
            const deltaX = currentMouseX - labelDragStart.mouseX;
            const deltaY = currentMouseY - labelDragStart.mouseY;
            
            // Apply delta to the label's starting position
            customLabelPosition = {
                x: parseFloat((labelDragStart.labelX + deltaX).toFixed(4)),
                y: parseFloat((labelDragStart.labelY + deltaY).toFixed(4))
            };
            
            // Update label position
            updateLabelPosition();
            
            return;
        }
        
        // Handle point dragging (left-click)
        if (isDraggingPoint && draggingPointIndex >= 0) {
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            
            // Update the point position
            currentPoints[draggingPointIndex] = {
                x: parseFloat(x.toFixed(4)),
                y: parseFloat(y.toFixed(4))
            };
            
            // Update the hidden vertices input
            const verticesInput = document.getElementById('location-vertices');
            if (verticesInput) {
                verticesInput.value = JSON.stringify(currentPoints);
            }
            
            drawPreviewPolygon();
            return;
        }
        
        // Handle map dragging
        if (!isDraggingMap) return;
        
        const deltaX = e.clientX - dragStartX;
        const deltaY = e.clientY - dragStartY;
        
        // If moved more than 10 pixels, consider it a drag (increased from 5)
        if (Math.abs(deltaX) > 10 || Math.abs(deltaY) > 10) {
            hasDragged = true;
            
            panX = startPanX + deltaX;
            panY = startPanY + deltaY;
            
            applyMapTransform();
        }
    });
    
    container.addEventListener('mouseup', (e) => {
        // Reset label dragging
        if (isDraggingLabel) {
            // Prevent context menu if we were dragging
            if (e.button === 2) {
                e.preventDefault();
                e.stopPropagation();
            }
            isDraggingLabel = false;
            labelDragStart = null; // Clear drag start data
            updateLabelPosition(); // Final position update
            container.style.cursor = editingLocationId ? 'grab' : (isDrawing ? 'crosshair' : 'grab');
        }
        
        // Reset point dragging
        if (isDraggingPoint) {
            isDraggingPoint = false;
            draggingPointIndex = -1;
            // Reset cursor on all point groups
            document.querySelectorAll('#preview-points g').forEach(g => {
                g.style.cursor = 'grab';
            });
        }
        
        if (isDraggingMap) {
            isDraggingMap = false;
            container.style.cursor = isDrawing ? 'crosshair' : 'grab';
            
            // Re-enable transition
            wrapper.style.transition = 'transform 0.3s ease-out';
        }
    });
    
    container.addEventListener('mouseleave', () => {
        // Don't stop label dragging on mouseleave - allow dragging to continue
        // Only stop map dragging
        if (isDraggingMap) {
            isDraggingMap = false;
            container.style.cursor = isDrawing ? 'crosshair' : 'default';
            wrapper.style.transition = 'transform 0.3s ease-out';
        }
    });
    
    // Also handle mouseup on the document to catch cases where mouse leaves container
    document.addEventListener('mouseup', (e) => {
        // Stop label dragging if it's active (handles case where mouse leaves container)
        if (isDraggingLabel) {
            // Prevent context menu if we were dragging with right-click
            if (e.button === 2) {
                e.preventDefault();
                e.stopPropagation();
            }
            isDraggingLabel = false;
            labelDragStart = null; // Clear drag start data
            updateLabelPosition(); // Final position update
            container.style.cursor = editingLocationId ? 'grab' : (isDrawing ? 'crosshair' : 'grab');
        }
    });
    
    // Prevent context menu on right-click anywhere when in edit mode and dragging
    document.addEventListener('contextmenu', (e) => {
        if (editingLocationId && (isDraggingLabel || container.contains(e.target))) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, { capture: true });
    
    // Set initial cursor
    container.style.cursor = 'grab';
    
    // Also handle mousemove on document level for label dragging when mouse leaves container
    document.addEventListener('mousemove', (e) => {
        if (isDraggingLabel && editingLocationId && labelDragStart) {
            const container = document.getElementById('campus-map-container');
            const wrapper = document.getElementById('map-wrapper');
            if (!container || !wrapper) return;
            
            const rect = wrapper.getBoundingClientRect();
            
            // Check if mouse is still over the map area (with some tolerance)
            if (e.clientX >= rect.left - 50 && e.clientX <= rect.right + 50 &&
                e.clientY >= rect.top - 50 && e.clientY <= rect.bottom + 50) {
                
                // Calculate current mouse position in percentage coordinates
                const currentMouseX = ((e.clientX - rect.left) / rect.width) * 100;
                const currentMouseY = ((e.clientY - rect.top) / rect.height) * 100;
                
                // Calculate the delta (how much mouse moved)
                const deltaX = currentMouseX - labelDragStart.mouseX;
                const deltaY = currentMouseY - labelDragStart.mouseY;
                
                // Apply delta to the label's starting position
                customLabelPosition = {
                    x: parseFloat((labelDragStart.labelX + deltaX).toFixed(4)),
                    y: parseFloat((labelDragStart.labelY + deltaY).toFixed(4))
                };
                
                // Update the actual label in the locations SVG
                updateLabelPosition();
            }
        }
    });
    
    // Mouse wheel zoom (zoom toward cursor like Google Maps)
    container.addEventListener('wheel', (e) => {
        e.preventDefault();
        
        const delta = e.deltaY > 0 ? -0.1 : 0.1; // Scroll down = zoom out, scroll up = zoom in
        const oldScale = currentScale;
        const newScale = Math.min(Math.max(1, currentScale + delta), 3);
        
        if (newScale !== currentScale) {
            // Get mouse position relative to container center
            const rect = container.getBoundingClientRect();
            const mouseX = e.clientX - rect.left - rect.width / 2;
            const mouseY = e.clientY - rect.top - rect.height / 2;
            
            // Calculate the zoom point offset from center
            // When zooming with center origin, we need to adjust pan to keep cursor point stationary
            const scaleRatio = newScale / oldScale;
            
            // Adjust pan: move by the difference in how far the point is from center at different scales
            panX = panX - (mouseX - panX) * (scaleRatio - 1);
            panY = panY - (mouseY - panY) * (scaleRatio - 1);
            
            // Update scale
            currentScale = newScale;
            
            // applyMapTransform will automatically call updateOnZoomChange()
            applyMapTransform();
        }
    }, { passive: false });

    // Touch events for mobile dragging and pinch-to-zoom
    let touchStartDistance = 0;
    let touchStartScale = 1;
    let touchStartCenterX = 0;
    let touchStartCenterY = 0;
    let touchStartPanX = 0;
    let touchStartPanY = 0;
    let isPinching = false;
    let isTouchDragging = false;

    container.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
            // Single touch - dragging
            if (!isDraggingPoint && !isDraggingLabel) {
                isTouchDragging = true;
                const touch = e.touches[0];
                dragStartX = touch.clientX;
                dragStartY = touch.clientY;
                startPanX = panX;
                startPanY = panY;
                hasDragged = false;
                
                // Disable transition while dragging
                wrapper.style.transition = 'none';
            }
        } else if (e.touches.length === 2) {
            // Two touches - pinch to zoom
            e.preventDefault();
            isPinching = true;
            isTouchDragging = false;
            
            const touch1 = e.touches[0];
            const touch2 = e.touches[1];
            
            // Calculate distance between two touches
            const dx = touch2.clientX - touch1.clientX;
            const dy = touch2.clientY - touch1.clientY;
            touchStartDistance = Math.sqrt(dx * dx + dy * dy);
            
            // Store initial scale and pan
            touchStartScale = currentScale;
            touchStartPanX = panX;
            touchStartPanY = panY;
            
            // Calculate center point between two touches
            const rect = container.getBoundingClientRect();
            touchStartCenterX = ((touch1.clientX + touch2.clientX) / 2) - rect.left - rect.width / 2;
            touchStartCenterY = ((touch1.clientY + touch2.clientY) / 2) - rect.top - rect.height / 2;
        }
    }, { passive: false });

    container.addEventListener('touchmove', (e) => {
        if (e.touches.length === 1 && isTouchDragging && !isPinching) {
            // Single touch dragging
            e.preventDefault();
            const touch = e.touches[0];
            const deltaX = touch.clientX - dragStartX;
            const deltaY = touch.clientY - dragStartY;
            
            // If moved more than 10 pixels, consider it a drag
            if (Math.abs(deltaX) > 10 || Math.abs(deltaY) > 10) {
                hasDragged = true;
                
                panX = startPanX + deltaX;
                panY = startPanY + deltaY;
                
                applyMapTransform();
            }
        } else if (e.touches.length === 2 && isPinching) {
            // Pinch to zoom
            e.preventDefault();
            
            const touch1 = e.touches[0];
            const touch2 = e.touches[1];
            
            // Calculate current distance
            const dx = touch2.clientX - touch1.clientX;
            const dy = touch2.clientY - touch1.clientY;
            const currentDistance = Math.sqrt(dx * dx + dy * dy);
            
            // Calculate scale change
            const scaleChange = currentDistance / touchStartDistance;
            const newScale = Math.min(Math.max(1, touchStartScale * scaleChange), 3);
            
            if (newScale !== currentScale) {
                // Calculate the zoom point offset from center
                const scaleRatio = newScale / touchStartScale;
                
                // Adjust pan to keep center point stationary
                panX = touchStartPanX - (touchStartCenterX - touchStartPanX) * (scaleRatio - 1);
                panY = touchStartPanY - (touchStartCenterY - touchStartPanY) * (scaleRatio - 1);
                
                currentScale = newScale;
                applyMapTransform();
            }
        }
    }, { passive: false });

    container.addEventListener('touchend', (e) => {
        if (e.touches.length === 0) {
            // All touches ended
            isTouchDragging = false;
            isPinching = false;
            wrapper.style.transition = 'transform 0.3s ease-out';
        } else if (e.touches.length === 1 && isPinching) {
            // One touch ended during pinch - switch to dragging
            isPinching = false;
            isTouchDragging = true;
            const touch = e.touches[0];
            dragStartX = touch.clientX;
            dragStartY = touch.clientY;
            startPanX = panX;
            startPanY = panY;
        }
    });

    // ESC to cancel drawing
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isDrawing) {
            cancelDrawingMode();
        }
    });

    // Type change updates color automatically
    const locationTypeSelect = document.getElementById('location-type');
    const colorInput = document.getElementById('location-color');
    
    if (locationTypeSelect && colorInput) {
        locationTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const defaultColor = selectedOption.dataset.color;
            if (defaultColor) {
                colorInput.value = defaultColor;
                
                // Redraw preview polygon if in edit mode to update point colors
                if (isDrawing && currentPoints.length > 0) {
                    drawPreviewPolygon();
                }
            }
        });
    }

    // Form submission
    const locationForm = document.getElementById('location-form');
    if (locationForm) {
        locationForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Handle window resize to maintain aspect ratio
    window.addEventListener('resize', function() {
        initializeMapDimensions();
    });
}

function enableDrawingMode() {
    isDrawing = true;
    currentPoints = [];
    
    // Disable Add Location button
    const addBtn = document.getElementById('add-location-btn');
    if (addBtn) {
        addBtn.disabled = true;
        addBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    
    // Show the form card immediately when Add Location is clicked
    const listCard = document.getElementById('locations-list-card');
    const formCard = document.getElementById('location-form-card');
    const form = document.getElementById('location-form');
    
    if (listCard && formCard && form) {
        // Reset form
        form.reset();
        editingLocationId = null;
        customLabelPosition = null;
        document.querySelectorAll('[id^="error-"]').forEach(el => el.classList.add('hidden'));
        
        // Set form title
        const formTitle = document.getElementById('form-card-title');
        if (formTitle) formTitle.textContent = 'Add New Location';
        
        // Update vertices info
        const verticesInfo = document.getElementById('form-vertices-info');
        if (verticesInfo) verticesInfo.textContent = '0 points added';
        
        // Clear vertices input
        const verticesInput = document.getElementById('location-vertices');
        if (verticesInput) verticesInput.value = JSON.stringify([]);
        
        // Hide edit mode instructions for new locations
        const editInstructions = document.getElementById('edit-mode-instructions');
        if (editInstructions) editInstructions.classList.add('hidden');
        
        // Hide list card, show form card
        listCard.classList.add('hidden');
        formCard.classList.remove('hidden');
    }
    
    document.getElementById('drawing-notice').classList.remove('hidden');
    document.getElementById('campus-map-container').style.cursor = 'crosshair';
    updatePointCounter();
    updateDrawingButtons();
}

function cancelDrawingMode() {
    isDrawing = false;
    currentPoints = [];
    customLabelPosition = null;
    editingLocationId = null; // Clear editing state
    document.getElementById('drawing-notice').classList.add('hidden');
    document.getElementById('campus-map-container').style.cursor = 'default';
    document.getElementById('preview-polygon').classList.add('hidden');
    document.getElementById('preview-points').innerHTML = '';
    
    // Re-enable Add Location button
    const addBtn = document.getElementById('add-location-btn');
    if (addBtn) {
        addBtn.disabled = false;
        addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    
    // Re-render locations to restore normal mode
    renderLocations();
}

function handleMapClick(e) {
    console.log('🔵 handleMapClick called. isDrawing:', isDrawing, 'hasDragged:', hasDragged);
    
    if (!isDrawing) {
        console.log('❌ Not in drawing mode');
        return;
    }
    
    // Don't add point if user was dragging
    if (hasDragged) {
        console.log('❌ User was dragging, not adding point');
        return;
    }
    
    // Get the wrapper (drawing canvas is inside it, so it transforms together)
    const wrapper = document.getElementById('map-wrapper');
    const rect = wrapper.getBoundingClientRect();
    
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    
    console.log('🖱️ Click at:', { clientX: e.clientX, clientY: e.clientY });
    console.log('📏 Wrapper rect:', rect);
    console.log('📍 Calculated point:', { x, y });
    
    // In edit mode with existing polygon, try to add point on nearest edge
    if (editingLocationId && currentPoints.length >= 3) {
        const pointAdded = addPointOnNearestEdge(x, y, currentPoints);
        if (pointAdded) {
            // Point was added on an edge
            updatePointCounter();
            updateDrawingButtons();
            drawPreviewPolygon();
            
            // Update the hidden vertices input
            const verticesInput = document.getElementById('location-vertices');
            if (verticesInput) {
                verticesInput.value = JSON.stringify(currentPoints);
            }
            return;
        }
    }
    
    // Add point normally (new location or click not on edge)
    currentPoints.push({ x: parseFloat(x.toFixed(4)), y: parseFloat(y.toFixed(4)) });
    console.log('✅ Point added! Total points:', currentPoints.length);
    
    // Update the hidden vertices input
    const verticesInput = document.getElementById('location-vertices');
    if (verticesInput) {
        verticesInput.value = JSON.stringify(currentPoints);
    }
    
    updatePointCounter();
    updateDrawingButtons();
    drawPreviewPolygon();
}

function handlePolygonLineClick(e) {
    // Prevent the click from bubbling to map click handler
    e.stopPropagation();
    
    if (!editingLocationId || currentPoints.length < 3) return;
    
    // Get click coordinates in percentage space
    const wrapper = document.getElementById('map-wrapper');
    const rect = wrapper.getBoundingClientRect();
    
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    
    // Add point on nearest edge
    const added = addPointOnNearestEdge(x, y, currentPoints);
    if (added) {
        updatePointCounter();
        updateDrawingButtons();
        drawPreviewPolygon();
        
        // Update the hidden vertices input
        const verticesInput = document.getElementById('location-vertices');
        if (verticesInput) {
            verticesInput.value = JSON.stringify(currentPoints);
        }
    }
}

function addPointOnNearestEdge(clickX, clickY, points) {
    if (points.length < 2) return false;
    
    let minDistance = Infinity;
    let bestEdgeIndex = -1;
    let bestPoint = null;
    
    // Check each edge of the polygon
    for (let i = 0; i < points.length; i++) {
        const p1 = points[i];
        const p2 = points[(i + 1) % points.length]; // Wrap around to first point
        
        // Calculate closest point on this line segment
        const closestPoint = getClosestPointOnSegment(clickX, clickY, p1, p2);
        
        // Calculate distance from click to closest point on edge
        const distance = Math.sqrt(
            Math.pow(clickX - closestPoint.x, 2) + 
            Math.pow(clickY - closestPoint.y, 2)
        );
        
        // Consider the edge if click is reasonably close (within 2% of map)
        if (distance < 2 && distance < minDistance) {
            minDistance = distance;
            bestEdgeIndex = i;
            bestPoint = closestPoint;
        }
    }
    
    // Insert the new point after the first point of the best edge
    if (bestEdgeIndex >= 0 && bestPoint) {
        currentPoints.splice(bestEdgeIndex + 1, 0, {
            x: parseFloat(bestPoint.x.toFixed(4)),
            y: parseFloat(bestPoint.y.toFixed(4))
        });
        return true;
    }
    
    return false;
}

function getClosestPointOnSegment(px, py, p1, p2) {
    // Vector from p1 to p2
    const dx = p2.x - p1.x;
    const dy = p2.y - p1.y;
    
    // Length squared of the line segment
    const lengthSquared = dx * dx + dy * dy;
    
    // If line segment has zero length, return p1
    if (lengthSquared === 0) {
        return { x: p1.x, y: p1.y };
    }
    
    // Calculate the projection parameter t
    // t = dot product of (px-p1) and (p2-p1) divided by length squared
    const t = Math.max(0, Math.min(1, 
        ((px - p1.x) * dx + (py - p1.y) * dy) / lengthSquared
    ));
    
    // Return the closest point on the segment
    return {
        x: p1.x + t * dx,
        y: p1.y + t * dy
    };
}

function undoLastPoint() {
    if (currentPoints.length > 0) {
        currentPoints.pop();
        
        // Update the hidden vertices input
        const verticesInput = document.getElementById('location-vertices');
        if (verticesInput) {
            verticesInput.value = JSON.stringify(currentPoints);
        }
        
        updatePointCounter();
        updateDrawingButtons();
        drawPreviewPolygon();
    }
}

function completePolygon() {
    if (currentPoints.length < 3) {
        alert('You need at least 3 points to create a polygon.');
        return;
    }
    
    // Form card should already be visible when adding location
    // Just update the vertices input with current points
    const verticesInput = document.getElementById('location-vertices');
    if (verticesInput) {
        verticesInput.value = JSON.stringify(currentPoints);
    }
    
    // Update vertices info
    const verticesInfo = document.getElementById('form-vertices-info');
    if (verticesInfo) {
        verticesInfo.textContent = `${currentPoints.length} points added`;
    }
    
    // Stop drawing mode but keep form visible
    isDrawing = false;
    document.getElementById('drawing-notice').classList.add('hidden');
    document.getElementById('campus-map-container').style.cursor = 'default';
    document.getElementById('preview-polygon').classList.add('hidden');
    document.getElementById('preview-points').innerHTML = '';
    
    // Remove label preview if it exists
    const labelPreview = document.getElementById('preview-label-group');
    if (labelPreview) labelPreview.remove();
}

function updatePointCounter() {
    const counter = document.getElementById('point-counter');
    if (counter) {
        counter.textContent = `Points: ${currentPoints.length}`;
    }
}

function updateDrawingButtons() {
    const undoBtn = document.getElementById('undo-point-btn');
    
    if (undoBtn) undoBtn.disabled = currentPoints.length === 0;
    // Complete polygon button removed - all actions handled by Save Location button
}

function getLocationColor() {
    if (!editingLocationId) return '#2563eb';
    
    // Get color from the selected location type
    const locationTypeSelect = document.getElementById('location-type');
    if (locationTypeSelect) {
        const selectedOption = locationTypeSelect.options[locationTypeSelect.selectedIndex];
        const defaultColor = selectedOption.dataset.color;
        if (defaultColor) return defaultColor;
    }
    
    // Fallback: get color from the location data if available
    const location = locations.find(l => l.id === editingLocationId);
    return location?.color || '#2563eb';
}

/**
 * Calculate size in SVG coordinates (0-100) from desired pixel size
 * Sizes scale down when zooming out for better visibility
 */
function getFixedSize(pixelSize) {
    const container = document.getElementById('campus-map-container');
    if (!container) return pixelSize / 100; // Fallback
    
    // Get actual container width
    const containerWidth = container.offsetWidth;
    if (containerWidth === 0) return pixelSize / 100; // Fallback
    
    // Convert pixels to percentage: (pixels / containerWidth) * 100
    // When zoomed in (scale > 1), divide to keep size fixed
    // When zoomed out (scale < 1), multiply by scale to make smaller
    const percentageSize = (pixelSize / containerWidth) * 100;
    if (currentScale > 1) {
        // Zoomed in: divide to maintain visual size
        return percentageSize / currentScale;
    } else {
        // Zoomed out: multiply to make smaller
        return percentageSize * currentScale;
    }
}

/**
 * Calculate label font size based on zoom level
 * Smaller at default/zoomed out, larger when zoomed in
 */
function getLabelFontSize(basePixelSize = 20) {
    const container = document.getElementById('campus-map-container');
    if (!container) return basePixelSize / 100; // Fallback
    
    const containerWidth = container.offsetWidth;
    if (containerWidth === 0) return basePixelSize / 100; // Fallback
    
    // Scale font size based on zoom level
    let scaleMultiplier = 1;
    
    if (currentScale <= 1) {
        // Default/zoomed out: make smaller (0.6x at scale 1, even smaller when < 1)
        scaleMultiplier = 0.6 * currentScale;
    } else if (currentScale <= 1.5) {
        // First zoom in: slightly larger (0.8x)
        scaleMultiplier = 0.8;
    } else if (currentScale <= 2) {
        // Second zoom in: normal size (1x)
        scaleMultiplier = 1.0;
    } else {
        // Further zoom in: keep at normal size (labels stay readable)
        scaleMultiplier = 1.0;
    }
    
    const adjustedPixelSize = basePixelSize * scaleMultiplier;
    const percentageSize = (adjustedPixelSize / containerWidth) * 100;
    
    // Apply inverse scale to maintain coordinate alignment
    if (currentScale > 1) {
        return percentageSize / currentScale;
    } else {
        return percentageSize * currentScale;
    }
}

/**
 * Update label position in the SVG
 */
function updateLabelPosition() {
    if (editingLocationId && customLabelPosition) {
        const svg = document.getElementById('locations-svg');
        if (svg) {
            const locationGroup = svg.querySelector(`g[data-location-id="${editingLocationId}"]`);
            if (locationGroup) {
                const label = locationGroup.querySelector(`text[data-label-location-id="${editingLocationId}"]`);
                
                if (label) {
                    label.setAttribute('x', customLabelPosition.x);
                    label.setAttribute('y', customLabelPosition.y);
                    label.setAttribute('transform-origin', `${customLabelPosition.x} ${customLabelPosition.y}`);
                }
            }
        }
    }
}

/**
 * Update all rendered elements when zoom changes
 */
function updateOnZoomChange() {
    // Clean up all tooltips when zoom changes
    activeTooltips.forEach(tooltip => {
        if (tooltip && tooltip.parentNode) {
            tooltip.remove();
        }
    });
    activeTooltips = [];
    
    // Redraw preview polygon if in drawing/edit mode
    if (isDrawing && currentPoints.length > 0) {
        drawPreviewPolygon();
    }
    
    // Re-render locations to update their sizes
    renderLocations();
}

function drawPreviewPolygon() {
    const pointsGroup = document.getElementById('preview-points');
    const polygon = document.getElementById('preview-polygon');
    
    if (!pointsGroup || !polygon) return;
    
    console.log('🎨 Drawing preview polygon. Points:', currentPoints.length, currentPoints);
    
    // Clear previous points
    pointsGroup.innerHTML = '';
    
    if (currentPoints.length === 0) {
        polygon.classList.add('hidden');
        return;
    }
    
    // Draw points with truly fixed pixel size regardless of zoom
    // 12px radius visually, converted to SVG coordinates accounting for zoom
    const fixedRadius = getFixedSize(12); // 12px visual radius
    const fixedFontSize = getFixedSize(16); // 16px visual font size (for point numbers)
    
    // Get the actual location color if editing, otherwise use blue
    const locationColor = editingLocationId ? getLocationColor() : '#2563eb';
    
    currentPoints.forEach((point, index) => {
        console.log(`➕ Adding point ${index + 1} at (${point.x}, ${point.y})`);
        
        // Create a group for each point (so we can make it draggable)
        const pointGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        pointGroup.setAttribute('data-point-index', index);
        pointGroup.style.cursor = 'grab';
        pointGroup.style.pointerEvents = 'auto'; // Enable mouse events
        
        // Use ellipse to compensate for aspect ratio, making it appear as a perfect circle
        const ellipse = document.createElementNS('http://www.w3.org/2000/svg', 'ellipse');
        ellipse.setAttribute('cx', point.x);
        ellipse.setAttribute('cy', point.y);
        ellipse.setAttribute('rx', fixedRadius); // Horizontal radius
        ellipse.setAttribute('ry', fixedRadius / aspectRatio); // Vertical radius - DIVIDE to compensate
        ellipse.setAttribute('fill', locationColor); // Use location type color
        ellipse.setAttribute('stroke', 'none');
        pointGroup.appendChild(ellipse);
        
        // Add point number
        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', point.x);
        text.setAttribute('y', point.y); // No offset, perfectly centered
        text.setAttribute('fill', '#ffffff'); // White text
        text.setAttribute('font-size', fixedFontSize);
        text.setAttribute('font-weight', 'bold');
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('dominant-baseline', 'central'); // Use 'central' for perfect vertical centering
        text.style.pointerEvents = 'none'; // Text doesn't block mouse events
        text.textContent = index + 1;
        pointGroup.appendChild(text);
        
        // Add drag handlers (left-click)
        pointGroup.addEventListener('mousedown', (e) => {
            if (e.button === 0) { // Left click
                e.stopPropagation();
                isDraggingPoint = true;
                draggingPointIndex = index;
                pointGroup.style.cursor = 'grabbing';
            }
        });
        
        // Add right-click handler to remove point
        pointGroup.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Don't allow removing if we only have 3 points (minimum for polygon)
            if (currentPoints.length <= 3) {
                alert('Cannot remove point. A polygon requires at least 3 points.');
                return;
            }
            
            // Remove the point
            currentPoints.splice(index, 1);
            updatePointCounter();
            updateDrawingButtons();
            drawPreviewPolygon();
            
            // Update the hidden vertices input
            const verticesInput = document.getElementById('location-vertices');
            if (verticesInput) {
                verticesInput.value = JSON.stringify(currentPoints);
            }
        });
        
        pointsGroup.appendChild(pointGroup);
        console.log('✅ Point group appended:', pointGroup);
    });
    
    console.log('📊 Points group children count:', pointsGroup.children.length);
    
        // Draw polygon if we have at least 3 points
        if (currentPoints.length >= 3) {
            const pointsString = currentPoints.map(p => `${p.x},${p.y}`).join(' ');
            // Stroke width with truly fixed pixel size (3px visually, increased from 2px)
            const fixedStrokeWidth = getFixedSize(3);
            
            polygon.setAttribute('points', pointsString);
            polygon.setAttribute('stroke-width', fixedStrokeWidth);
            polygon.setAttribute('stroke', locationColor);
            polygon.classList.remove('hidden');
            
            // Make polygon clickable in edit mode to add points on edges
            if (editingLocationId) {
                polygon.style.pointerEvents = 'auto';
                polygon.style.cursor = 'crosshair';
                // Remove existing listener if any, then add new one
                polygon.replaceWith(polygon.cloneNode(true));
                const newPolygon = document.getElementById('preview-polygon');
                newPolygon.addEventListener('click', handlePolygonLineClick);
            } else {
                polygon.style.pointerEvents = 'none';
            }
            
            console.log('🔷 Polygon created with points:', pointsString);
        } else {
            polygon.classList.add('hidden');
        }
    }
    
    // Re-apply transform after drawing (in case zoom changed)
    applyMapTransform();


function openLocationModal(vertices, locationData = null) {
    const listCard = document.getElementById('locations-list-card');
    const formCard = document.getElementById('location-form-card');
    const form = document.getElementById('location-form');
    const editInstructions = document.getElementById('edit-mode-instructions');
    
    if (!formCard || !form) return;
    
    // Reset form
    form.reset();
    editingLocationId = null;
    customLabelPosition = null; // Reset custom label position
    document.querySelectorAll('[id^="error-"]').forEach(el => el.classList.add('hidden'));
    
    // Set vertices
    const verticesInput = document.getElementById('location-vertices');
    const verticesInfo = document.getElementById('form-vertices-info');
    
    if (verticesInput) verticesInput.value = JSON.stringify(vertices);
    if (verticesInfo) verticesInfo.textContent = `${vertices.length} points added`;
    
    // If editing existing location
    if (locationData) {
        editingLocationId = locationData.id;
        const formTitle = document.getElementById('form-card-title');
        if (formTitle) formTitle.textContent = 'Edit Location';
        
        const locationIdInput = document.getElementById('location-id');
        if (locationIdInput) locationIdInput.value = locationData.id;
        
        const locationTypeSelect = document.getElementById('location-type');
        if (locationTypeSelect) {
            locationTypeSelect.value = locationData.type_id;
            
            // Trigger change event to set color based on type
            const changeEvent = new Event('change', { bubbles: true });
            locationTypeSelect.dispatchEvent(changeEvent);
        }
        
        const locationNameInput = document.getElementById('location-name');
        if (locationNameInput) locationNameInput.value = locationData.name;
        
        const locationCodeInput = document.getElementById('location-code');
        if (locationCodeInput) locationCodeInput.value = locationData.short_code || '';
        
        const locationDescInput = document.getElementById('location-description');
        if (locationDescInput) locationDescInput.value = locationData.description || '';
        
        // Set custom label position if it exists
        if (locationData.center_x && locationData.center_y) {
            customLabelPosition = {
                x: parseFloat(locationData.center_x),
                y: parseFloat(locationData.center_y)
            };
        }
        
        // When editing, show existing points on the map and enable editing mode
        currentPoints = [...vertices];
        isDrawing = true;
        // Re-render locations to show edit mode (no hover, draggable label)
        renderLocations();
        drawPreviewPolygon();
        document.getElementById('drawing-notice').classList.remove('hidden');
        document.getElementById('campus-map-container').style.cursor = 'crosshair';
        updatePointCounter();
        updateDrawingButtons();
        
        // Show edit mode instructions
        if (editInstructions) editInstructions.classList.remove('hidden');
    } else {
        const formTitle = document.getElementById('form-card-title');
        if (formTitle) formTitle.textContent = 'Add New Location';
        
        // Hide edit mode instructions for new locations
        if (editInstructions) editInstructions.classList.add('hidden');
    }
    
    // Hide list card, show form card
    if (listCard) listCard.classList.add('hidden');
    formCard.classList.remove('hidden');
}

function closeLocationModal() {
    const listCard = document.getElementById('locations-list-card');
    const formCard = document.getElementById('location-form-card');
    
    if (formCard) formCard.classList.add('hidden');
    if (listCard) listCard.classList.remove('hidden');
    
    editingLocationId = null;
    
    // Re-enable Add Location button
    const addBtn = document.getElementById('add-location-btn');
    if (addBtn) {
        addBtn.disabled = false;
        addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    
    // Also cancel drawing mode if active
    if (isDrawing) {
        cancelDrawingMode();
    }
}

// Alias for consistency
function closeLocationForm() {
    closeLocationModal();
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {};
    
    // Convert form data to object
    formData.forEach((value, key) => {
        if (key === 'vertices') {
            data[key] = JSON.parse(value);
        } else {
            data[key] = value;
        }
    });
    
    // Add custom label position if set
    if (customLabelPosition) {
        data.center_x = customLabelPosition.x;
        data.center_y = customLabelPosition.y;
    }
    
    // Clear previous errors
    document.querySelectorAll('[id^="error-"]').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
    
    try {
        const url = editingLocationId 
            ? `/api/map-locations/${editingLocationId}`
            : '/api/map-locations';
        
        const method = editingLocationId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // Success
            closeLocationModal();
            
            // Update locations array
            if (editingLocationId) {
                const index = locations.findIndex(l => l.id === editingLocationId);
                if (index !== -1) {
                    locations[index] = result.data;
                }
            } else {
                locations.push(result.data);
            }
            
            // Mark action for real-time updates (to prevent duplicate notifications)
            if (window.campusMapRealtime && typeof window.campusMapRealtime.markUserAction === 'function') {
                window.campusMapRealtime.markUserAction(result.data.id);
            }
            
            renderLocations();
            
            // Reload page to update table
            location.reload();
        } else {
            // Validation errors
            if (result.errors) {
                Object.keys(result.errors).forEach(key => {
                    const errorElement = document.getElementById(`error-${key}`);
                    if (errorElement) {
                        errorElement.textContent = result.errors[key][0];
                        errorElement.classList.remove('hidden');
                    }
                });
            } else {
                alert(result.message || 'An error occurred');
            }
        }
    } catch (error) {
        console.error('Error saving location:', error);
        alert('Failed to save location. Please try again.');
    }
}

function renderLocations() {
    if (!locationsVisible) return;
    
    const svg = document.getElementById('locations-svg');
    if (!svg) return;
    
    // Clear existing locations (except defs)
    while (svg.children.length > 1) {
        svg.removeChild(svg.lastChild);
    }
    
    locations.forEach(location => {
        if (!location.is_active || !location.vertices || location.vertices.length < 3) return;
        
        const isEditingThis = editingLocationId === location.id;
        
        // Create polygon group
        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.setAttribute('class', 'location-polygon');
        g.setAttribute('data-location-id', location.id);
        g.style.cursor = isEditingThis ? 'default' : 'pointer';
        g.style.pointerEvents = isEditingThis ? 'none' : 'auto'; // Disable pointer events when editing
        
        // Skip rendering the polygon when editing (it's shown in preview instead)
        // But we still need to render the label for dragging
        if (!isEditingThis) {
        
            // Draw polygon (initially invisible, only shows on hover)
            const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
            const pointsString = location.vertices.map(v => `${v.x},${v.y}`).join(' ');
            polygon.setAttribute('points', pointsString);
            polygon.setAttribute('fill', 'transparent');
            polygon.setAttribute('stroke', 'none');
            polygon.setAttribute('class', 'transition-all duration-200');
            
            let tooltip = null;
            
            g.addEventListener('mouseenter', function(e) {
                // Stroke width with truly fixed pixel size (3px visually, increased from 2px)
                const fixedStrokeWidth = getFixedSize(3);
                polygon.setAttribute('fill', location.color + '40'); // Show with 25% opacity
                polygon.setAttribute('stroke', location.color);
                polygon.setAttribute('stroke-width', fixedStrokeWidth.toString());
                polygon.setAttribute('filter', 'url(#glow)');
                
                // Clean up any existing tooltips first
                activeTooltips.forEach(t => {
                    if (t && t.parentNode) t.remove();
                });
                activeTooltips = [];
                
                // Show tooltip immediately
                tooltip = document.createElement('div');
                tooltip.className = 'absolute bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] px-3 py-2 rounded-lg text-sm font-medium shadow-lg z-50 pointer-events-none';
                tooltip.style.left = e.clientX + 10 + 'px';
                tooltip.style.top = e.clientY + 10 + 'px';
                tooltip.textContent = location.name;
                document.body.appendChild(tooltip);
                activeTooltips.push(tooltip);
            });
            
            g.addEventListener('mouseleave', function() {
                polygon.setAttribute('fill', 'transparent'); // Hide again
                polygon.setAttribute('stroke', 'none');
                polygon.removeAttribute('filter');
                
                // Remove tooltip
                if (tooltip) {
                    tooltip.remove();
                    activeTooltips = activeTooltips.filter(t => t !== tooltip);
                    tooltip = null;
                }
            });
            
            // Update tooltip position on mouse move
            g.addEventListener('mousemove', function(e) {
                if (tooltip) {
                    tooltip.style.left = e.clientX + 10 + 'px';
                    tooltip.style.top = e.clientY + 10 + 'px';
                }
            });
            
            // Click to view details
            g.addEventListener('click', () => viewLocationDetails(location.id));
            
            g.appendChild(polygon);
        }
        
        // Add label if there's a center point (render label even when editing)
        let label = null;
        if (location.center_x && location.center_y) {
            // Use fixed small font size like dashboard (SVG units, scales naturally)
            label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', location.center_x);
            label.setAttribute('y', location.center_y);
            label.setAttribute('data-label-location-id', location.id);
            label.setAttribute('text-anchor', 'middle');
            label.setAttribute('dominant-baseline', 'central');
            label.setAttribute('fill', '#4b5563'); // Dark gray (gray-600)
            label.setAttribute('stroke', '#ffffff'); // White outline
            label.setAttribute('stroke-width', 0.15); // Small fixed stroke like dashboard
            label.setAttribute('paint-order', 'stroke fill'); // Draw stroke behind fill
            label.setAttribute('font-weight', '700'); // Bold for better visibility
            label.setAttribute('font-size', 1); // Fixed small size like dashboard (SVG units)
            label.setAttribute('font-family', 'Inter, ui-sans-serif, system-ui, sans-serif');
            // Apply transform to counter the aspect ratio stretch
            label.setAttribute('transform', `scale(1, ${1 / aspectRatio})`);
            label.setAttribute('transform-origin', `${location.center_x} ${location.center_y}`);
            
            // Make label draggable in edit mode (the entire map will handle right-click dragging)
            if (isEditingThis) {
                label.style.cursor = 'grab';
                label.style.pointerEvents = 'auto';
            } else {
                label.setAttribute('style', 'pointer-events: none;');
            }
            
            label.textContent = location.short_code || location.name.substring(0, 3).toUpperCase();
        }
        
        if (label) g.appendChild(label);
        svg.appendChild(g);
    });
}

function viewLocationDetails(id) {
    const location = locations.find(l => l.id === id);
    if (!location) return;
    
    const modal = document.getElementById('viewLocationModal');
    const content = document.getElementById('viewLocationContent');
    
    if (!modal || !content) return;
    
    // Process sticker path like students page does
    let stickerImage = null;
    if (location.sticker_path) {
        stickerImage = location.sticker_path.startsWith('/storage/') || location.sticker_path.startsWith('http') 
            ? location.sticker_path 
            : '/storage/' + location.sticker_path;
        
        // URL encode the path to handle special characters like #
        // Only encode if it doesn't already contain encoded characters
        if (!stickerImage.includes('%')) {
            // Split path and filename, encode the filename
            const pathParts = stickerImage.split('/');
            const filename = pathParts.pop();
            const encodedFilename = encodeURIComponent(filename);
            stickerImage = pathParts.join('/') + '/' + encodedFilename;
        }
    }
    
    content.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Name</label>
                        <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC] font-medium">${location.name}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Short Code</label>
                        <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC]">${location.short_code || 'N/A'}</p>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Type</label>
                    <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC]">${location.type.name}</p>
                </div>
                ${location.description ? `
                <div>
                    <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Description</label>
                    <p class="text-base text-[#1b1b18] dark:text-[#EDEDEC]">${location.description}</p>
                </div>
                ` : ''}
            </div>
            ${stickerImage ? `
            <div>
                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2 block">Sticker</label>
                <div class="inline-block p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <img src="${stickerImage}" alt="Location Sticker" class="h-auto max-h-96 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] object-contain" style="width: auto;">
                </div>
            </div>
            ` : `
            <div>
                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2 block">Sticker</label>
                <div class="inline-block p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No sticker available</p>
                </div>
            </div>
            `}
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeViewLocationModal() {
    const modal = document.getElementById('viewLocationModal');
    if (modal) modal.classList.add('hidden');
}

function closeDeleteLocationModal() {
    const modal = document.getElementById('deleteLocationModal');
    if (modal) modal.classList.add('hidden');
    deletingLocationId = null;
}

function toggleLocationsVisibility() {
    locationsVisible = !locationsVisible;
    const btn = document.getElementById('toggle-locations-btn');
    const overlay = document.getElementById('locations-overlay');
    
    if (!btn || !overlay) return;
    
    if (locationsVisible) {
        btn.textContent = 'Hide Locations';
        overlay.classList.remove('hidden');
        renderLocations();
    } else {
        btn.textContent = 'Show Locations';
        overlay.classList.add('hidden');
    }
}

async function editLocation(id) {
    try {
        // Disable Add Location button
        const addBtn = document.getElementById('add-location-btn');
        if (addBtn) {
            addBtn.disabled = true;
            addBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
        
        const response = await fetch(`/api/map-locations/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            const location = result.data;
            openLocationModal(location.vertices, location);
        } else {
            alert('Failed to load location data');
            // Re-enable button on error
            if (addBtn) {
                addBtn.disabled = false;
                addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    } catch (error) {
        console.error('Error loading location:', error);
        alert('Failed to load location');
        // Re-enable button on error
        const addBtn = document.getElementById('add-location-btn');
        if (addBtn) {
            addBtn.disabled = false;
            addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }
}

function deleteLocation(id) {
    const location = locations.find(l => l.id === id);
    if (!location) return;
    
    deletingLocationId = id;
    const modal = document.getElementById('deleteLocationModal');
    const message = document.getElementById('deleteLocationMessage');
    
    if (!modal || !message) return;
    
    message.innerHTML = `
        <p class="text-[#1b1b18] dark:text-[#EDEDEC]">
            Are you sure you want to delete the location <strong class="font-semibold">${location.name}</strong>?
        </p>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-2">
            This action cannot be undone.
        </p>
    `;
    
    modal.classList.remove('hidden');
}

async function confirmDeleteLocation() {
    if (!deletingLocationId) return;
    
    // Mark action for real-time updates (to prevent duplicate notifications)
    if (window.campusMapRealtime && typeof window.campusMapRealtime.markUserAction === 'function') {
        window.campusMapRealtime.markUserAction(deletingLocationId);
    }
    
    try {
        const response = await fetch(`/api/map-locations/${deletingLocationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            locations = locations.filter(l => l.id !== deletingLocationId);
            renderLocations();
            closeDeleteLocationModal();
            location.reload();
        } else {
            alert(result.message || 'Failed to delete location');
        }
    } catch (error) {
        console.error('Error deleting location:', error);
        alert('Failed to delete location');
    }
}

function zoomMap(delta) {
    currentScale = Math.min(Math.max(1, currentScale + delta), 3); // Min zoom is 1 (100%), max is 3 (300%)
    applyMapTransform();
}

function resetZoom() {
    currentScale = 1;
    panX = 0;
    panY = 0;
    applyMapTransform();
}

function applyMapTransform() {
    const wrapper = document.getElementById('map-wrapper');
    const container = document.getElementById('campus-map-container');
    const drawingCanvas = document.getElementById('drawing-canvas');
    const locationsSvg = document.getElementById('locations-svg');
    
    if (!wrapper || !container) return;
    
    // Get actual dimensions
    const containerWidth = container.offsetWidth;
    const containerHeight = container.offsetHeight;
    
    // Calculate scaled dimensions
    const scaledWidth = containerWidth * currentScale;
    const scaledHeight = containerHeight * currentScale;
    
    // Calculate how much extra space we have when scaled (due to center origin)
    const extraWidth = (scaledWidth - containerWidth) / 2;
    const extraHeight = (scaledHeight - containerHeight) / 2;
    
    // Constrain pan values
    if (currentScale > 1) {
        // With center origin, the image expands equally in all directions
        // We can pan in both directions within the extra space
        const maxPanX = extraWidth;
        const minPanX = -extraWidth;
        const maxPanY = extraHeight;
        const minPanY = -extraHeight;
        
        panX = Math.max(minPanX, Math.min(maxPanX, panX));
        panY = Math.max(minPanY, Math.min(maxPanY, panY));
    } else {
        // At 100% zoom, reset pan to center
        panX = 0;
        panY = 0;
    }
    
    wrapper.style.transform = `translate(${panX}px, ${panY}px) scale(${currentScale})`;
    
    // Automatically update all element sizes when zoom changes
    updateOnZoomChange();
}

// Expose functions to global scope for onclick handlers
window.viewLocationDetails = viewLocationDetails;
window.editLocation = editLocation;
window.deleteLocation = deleteLocation;
window.closeViewLocationModal = closeViewLocationModal;
window.closeDeleteLocationModal = closeDeleteLocationModal;
window.confirmDeleteLocation = confirmDeleteLocation;
window.closeLocationForm = closeLocationForm;

/**
 * Modern Google Places Autocomplete Module
 * Uses the new PlaceAutocompleteElement for better performance and future compatibility
 * Version: 1.0.0
 */

class ModernPlacesAutocomplete {
    constructor() {
        this.initialized = false;
        this.elements = new Map();
        this.config = {
            types: ['address'],
            componentRestrictions: { country: 'in' },
            fields: ['formatted_address', 'geometry', 'name', 'place_id']
        };
    }

    /**
     * Initialize the Places API
     */
    async init() {
        if (this.initialized) return true;
        
        try {
            await this.waitForGoogleAPI();
            this.initialized = true;
            console.log('Modern Places API initialized successfully');
            return true;
        } catch (error) {
            console.error('Failed to initialize Modern Places API:', error);
            throw error;
        }
    }

    /**
     * Wait for Google Maps API to be loaded
     */
    waitForGoogleAPI() {
        return new Promise((resolve, reject) => {
            if (window.google && window.google.maps && window.google.maps.places) {
                resolve();
                return;
            }

            let attempts = 0;
            const maxAttempts = 20;
            const interval = setInterval(() => {
                if (window.google && window.google.maps && window.google.maps.places) {
                    clearInterval(interval);
                    resolve();
                    return;
                }

                attempts++;
                if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    reject(new Error('Google Maps API failed to load'));
                }
            }, 500);
        });
    }

    /**
     * Create autocomplete instances for from and to inputs
     */
    async createAutocomplete({ fromInputId, toInputId, fromLatId, fromLngId, toLatId, toLngId }) {
        if (!this.initialized) {
            await this.init();
        }

        // Create from location autocomplete
        const fromInput = document.getElementById(fromInputId);
        const fromAutocomplete = new google.maps.places.Autocomplete(fromInput, this.config);
        fromAutocomplete.addListener('place_changed', () => {
            const place = fromAutocomplete.getPlace();
            if (place.geometry) {
                document.getElementById(fromLatId).value = place.geometry.location.lat();
                document.getElementById(fromLngId).value = place.geometry.location.lng();
            }
        });
        this.elements.set('from', fromAutocomplete);

        // Create to location autocomplete
        const toInput = document.getElementById(toInputId);
        const toAutocomplete = new google.maps.places.Autocomplete(toInput, this.config);
        toAutocomplete.addListener('place_changed', () => {
            const place = toAutocomplete.getPlace();
            if (place.geometry) {
                document.getElementById(toLatId).value = place.geometry.location.lat();
                document.getElementById(toLngId).value = place.geometry.location.lng();
            }
        });
        this.elements.set('to', toAutocomplete);

        // Prevent form submission on enter
        [fromInput, toInput].forEach(input => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });

        console.log('Autocomplete elements created successfully');
    }

    /**
     * Apply consistent styling to the autocomplete element
     */
    applyStyles(element) {
        element.style.cssText = `
            width: 100%;
            margin: 0;
            padding: 0;
            border: none;
            background: transparent;
        `;
    }

    /**
     * Clean up autocomplete elements
     */
    cleanup() {
        for (const element of this.elements.values()) {
            element.remove();
        }
        this.elements.clear();
    }

    /**
     * Validate required input fields for the form
     */
    validateInputs(fromInputId, toInputId, fromLatId, fromLngId, toLatId, toLngId) {
        const from = document.getElementById(fromInputId).value.trim();
        const to = document.getElementById(toInputId).value.trim();
        const fromLat = document.getElementById(fromLatId).value.trim();
        const fromLng = document.getElementById(fromLngId).value.trim();
        const toLat = document.getElementById(toLatId).value.trim();
        const toLng = document.getElementById(toLngId).value.trim();

        if (!from || !to) {
            throw new Error('Please select both departure and destination locations');
        }
        if (!fromLat || !fromLng || !toLat || !toLng) {
            throw new Error('Please select locations from the dropdown suggestions');
        }
        return true;
    }
}

// Create global instance
window.modernPlacesAutocomplete = new ModernPlacesAutocomplete();

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.modernPlacesAutocomplete.init();
}); 
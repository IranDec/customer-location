/**
 * طراح
 * Mohammad Babaei
 * وب‌سایت https://adschi.com
 */

$(document).ready(function() {
    // Check if the specific button for GPS exists. This button should only be added to the DOM for Iran.
    // The related hidden fields gps_latitude and gps_longitude are also assumed to be present.
    const getGpsButton = $('#get-gps-location-btn'); // Assuming this ID for the button
    const latitudeInput = $('#gps_latitude');       // Assuming this ID for the hidden latitude input
    const longitudeInput = $('#gps_longitude');     // Assuming this ID for the hidden longitude input
    const gpsStatusDiv = $('#gps-status-message');  // Assuming a div for status messages

    if (getGpsButton.length && latitudeInput.length && longitudeInput.length) {
        getGpsButton.on('click', function(e) {
            e.preventDefault(); // Prevent form submission if it's a submit button or inside a form

            if (gpsStatusDiv.length) {
                gpsStatusDiv.text('در حال دریافت موقعیت مکانی شما...').removeClass('error success').addClass('info'); // Fetching location...
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;

                        latitudeInput.val(lat.toFixed(8));   // Using .toFixed(8) as per DECIMAL(10,8)
                        longitudeInput.val(lon.toFixed(8)); // Using .toFixed(8) as per DECIMAL(11,8)

                        if (gpsStatusDiv.length) {
                            gpsStatusDiv.text('موقعیت مکانی با موفقیت دریافت شد.').removeClass('info error').addClass('success'); // Location captured successfully.
                        }
                        // Optionally, disable the button after successful capture or change its text
                        // getGpsButton.prop('disabled', true).text('موقعیت دریافت شد');
                    },
                    function(error) {
                        let errorMessage = 'خطا در دریافت موقعیت مکانی: '; // Error fetching location:
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += 'دسترسی به موقعیت مکانی رد شد.'; // User denied the request for Geolocation.
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += 'اطلاعات موقعیت مکانی در دسترس نیست.'; // Location information is unavailable.
                                break;
                            case error.TIMEOUT:
                                errorMessage += 'زمان درخواست برای دریافت موقعیت مکانی به پایان رسید.'; // The request to get user location timed out.
                                break;
                            case error.UNKNOWN_ERROR:
                                errorMessage += 'یک خطای ناشناخته رخ داد.'; // An unknown error occurred.
                                break;
                        }
                        if (gpsStatusDiv.length) {
                            gpsStatusDiv.text(errorMessage).removeClass('info success').addClass('error');
                        }
                        // Clear hidden fields on error to avoid submitting stale/failed data
                        latitudeInput.val('');
                        longitudeInput.val('');
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000, // 10 seconds
                        maximumAge: 0 // Don't use a cached position
                    }
                );
            } else {
                if (gpsStatusDiv.length) {
                    gpsStatusDiv.text('مرورگر شما از موقعیت یابی جغرافیایی پشتیبانی نمی کند.').removeClass('info success').addClass('error'); // Geolocation is not supported by this browser.
                }
            }
        });
    } else {
        // Optional: log if elements are not found, for debugging module setup
        // console.log('PSLocationLocator: GPS button or input fields not found on this page.');
    }

    // Logic to show/hide the button based on country selection (Iran - ISO 'IR')
    // This is best handled when the form is built (PHP/Smarty), but a JS fallback can be added.
    // We need to know the ID of the country selector dropdown.
    // Assuming PrestaShop's default address form country selector has id 'id_country'.
    const countrySelector = $('#id_country'); // Common ID for country selector in PrestaShop forms

    function toggleGpsFeature() {
        // This assumes the button and its container are managed for visibility.
        // Let's assume the button (and hidden fields, status message area) are wrapped in a container.
        // e.g., <div id="psll-gps-feature-container"> ... button, inputs, status ... </div>
        const gpsFeatureContainer = $('#psll-gps-feature-container'); // Wrapper for all GPS elements

        if (countrySelector.length && gpsFeatureContainer.length) {
            if (countrySelector.val() == getIdCountryByIso('IR')) { // getIdCountryByIso('IR') needs to be a JS var from PHP
                gpsFeatureContainer.show();
            } else {
                gpsFeatureContainer.hide();
                // Clear fields if country changes away from IR after getting location
                latitudeInput.val('');
                longitudeInput.val('');
                if (gpsStatusDiv.length) {
                    gpsStatusDiv.text('');
                }
            }
        }
    }

    if (countrySelector.length) {
        // Call it once on page load
        // We need `id_country_iran` to be passed from PHP to JS, e.g. via `Media::addJsDef`
        // For now, let's assume `prestashop.variables.id_country_iran` is available.
        // This part of the JS is more complex as it relies on PHP passing data.
        // The primary requirement is that the button is added for IR.
        // The JS should work if the button IS present.

        // Simpler approach: The PHP hook should ONLY add the button/container if country is IR.
        // If not, gpsFeatureContainer won't exist, and the JS won't do anything.
        // If the form allows changing country and it reloads parts via AJAX, this could get complex.
        // Standard PrestaShop address form reloads on country change.
        // So, if the button is added by PHP based on initial country, this JS is fine.
        // If country changes, page reloads, PHP hook re-evaluates.

        // The initial check `if (getGpsButton.length ...)` is key.
        // The button should only be printed by the hook if country is Iran.
    }
});

// Helper function (if needed, or this logic is in PHP)
// function getIdCountryByIso(isoCode) {
// This would require `countries` variable passed from PHP, containing {iso_code: id_country} map.
// For 'IR', this is typically a fixed ID in a PrestaShop installation, but can vary.
// It's better if the specific ID for Iran is passed from PHP if this dynamic show/hide in JS is used.
// Example: if (prestashop && prestashop.variables && prestashop.variables.id_country_iran) {
// return prestashop.variables.id_country_iran;
// }
// return 0; // Default or error
// }

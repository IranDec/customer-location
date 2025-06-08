/**
 * طراح
 * Mohammad Babaei
 * وب‌سایت https://adschi.com
 */

$(document).ready(function() {
    // Logic to hide original city input if our dropdown is present
    const cityDropdown = $('#psll_city_dropdown');
    if (cityDropdown.length > 0) {
        // Find the original city input field.
        // We target specifically an input element with id="city" and name="city"
        // to avoid conflicting with our select dropdown which also has name="city".
        const originalCityInput = $('input#city[name="city"]');

        if (originalCityInput.length > 0) {
            // Hide its parent form-group.
            originalCityInput.closest('.form-group').hide();
        }
    }

    // Existing GPS button logic (should remain untouched by the above)
    // Note: The IDs in the provided existing JS were generic.
    // Using the specific IDs from the .tpl file for consistency:
    // psll_get_location_btn, psll_gps_latitude, psll_gps_longitude, psll_gps_status
    const getGpsButton = $('#psll_get_location_btn'); // Corrected ID from .tpl
    const latitudeInput = $('#psll_gps_latitude');    // Corrected ID from .tpl
    const longitudeInput = $('#psll_gps_longitude'); // Corrected ID from .tpl
    const gpsStatusDiv = $('#psll_gps_status');       // Corrected ID from .tpl

    if (getGpsButton.length && latitudeInput.length && longitudeInput.length) {
        getGpsButton.on('click', function(e) {
            e.preventDefault();

            if (gpsStatusDiv.length) {
                gpsStatusDiv.text('در حال دریافت موقعیت مکانی شما...').removeClass('error success').addClass('info').show();
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;

                        latitudeInput.val(lat.toFixed(8));
                        longitudeInput.val(lon.toFixed(8));

                        if (gpsStatusDiv.length) {
                            gpsStatusDiv.text('موقعیت مکانی با موفقیت دریافت شد.').removeClass('info error').addClass('success');
                        }
                    },
                    function(error) {
                        let errorMessage = 'خطا در دریافت موقعیت مکانی: ';
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += 'دسترسی به موقعیت مکانی رد شد.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += 'اطلاعات موقعیت مکانی در دسترس نیست.';
                                break;
                            case error.TIMEOUT:
                                errorMessage += 'زمان درخواست برای دریافت موقعیت مکانی به پایان رسید.';
                                break;
                            case error.UNKNOWN_ERROR:
                                errorMessage += 'یک خطای ناشناخته رخ داد.';
                                break;
                        }
                        if (gpsStatusDiv.length) {
                            gpsStatusDiv.text(errorMessage).removeClass('info success').addClass('error');
                        }
                        // Clear inputs on error
                        latitudeInput.val('');
                        longitudeInput.val('');
                    },
                    {
                        enableHighAccuracy: true, // Attempt to get a more precise location
                        timeout: 10000,          // Maximum time (in milliseconds) to wait for a response
                        maximumAge: 0            // Force a fresh location query
                    }
                );
            } else {
                if (gpsStatusDiv.length) {
                    gpsStatusDiv.text('مرورگر شما از موقعیت یابی جغرافیایی پشتیبانی نمی کند.').removeClass('info success').addClass('error');
                }
            }
        });
    }
    // Any other existing JS logic can follow here
});

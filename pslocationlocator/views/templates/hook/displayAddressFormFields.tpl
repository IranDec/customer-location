{*
 * طراح
 * Mohammad Babaei
 * وب‌سایت https://adschi.com
 *}

{* This template adds fields to the address form for GPS location *}
<div id="psll-gps-feature-container" class="form-group">
    <label class="col-md-3 form-control-label"></label> {# Empty label for alignment if needed #}
    <div class="col-md-7"> {# Adjusted col-md-* for typical PrestaShop form structure #}
        <button type="button" id="get-gps-location-btn" class="btn btn-primary">
            <i class="icon-map-marker"></i> {l s='دریافت موقعیت دقیق' mod='pslocationlocator'}
        </button>
        <div id="gps-status-message" style="margin-top: 5px;"></div>
    </div>
</div>

{# Hidden input fields to store GPS coordinates #}
<input type="hidden" name="gps_latitude" id="gps_latitude" value="{$psll_existing_latitude|escape:'htmlall':'UTF-8'}" />
<input type="hidden" name="gps_longitude" id="gps_longitude" value="{$psll_existing_longitude|escape:'htmlall':'UTF-8'}" />

{*
   The JS file (gps_address.js) will handle the button click,
   fetch location, and populate these hidden fields.
   The CSS file (gps_address.css) will style these elements.
   This template assumes Bootstrap 4/5 structure for col-md-*, btn, form-group.
   PrestaShop 1.7 uses Bootstrap.
*}

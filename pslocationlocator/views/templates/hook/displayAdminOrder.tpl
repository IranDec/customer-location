{*
 * طراح
 * Mohammad Babaei
 * وب‌سایت https://adschi.com
 *}

{if isset($psll_gps_latitude) && isset($psll_gps_longitude) && ($psll_gps_latitude != 0 || $psll_gps_longitude != 0)}
<div class="panel" id="psllAdminOrderDetailGpsLocationPanel">
    <div class="panel-heading">
        <i class="icon-map-marker"></i> {l s='Delivery GPS Location' mod='pslocationlocator'}
    </div>
    <div class="form-horizontal">
        <div class="form-group">
            <label class="col-lg-3 control-label">{l s='Latitude:' mod='pslocationlocator'}</label>
            <div class="col-lg-9">
                <p class="form-control-static">{$psll_gps_latitude|escape:'html':'UTF-8'}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{l s='Longitude:' mod='pslocationlocator'}</label>
            <div class="col-lg-9">
                <p class="form-control-static">{$psll_gps_longitude|escape:'html':'UTF-8'}</p>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-9 col-lg-offset-3">
                <a href="https://www.google.com/maps?q={$psll_gps_latitude|escape:'url'},{$psll_gps_longitude|escape:'url'}" target="_blank" class="btn btn-default">
                    <i class="icon-eye"></i> {l s='View on Google Maps' mod='pslocationlocator'}
                </a>
            </div>
        </div>
    </div>
</div>
{/if}

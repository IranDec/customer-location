{*
 * طراح
 * Mohammad Babaei
 * وب‌سایت https://adschi.com
 *}

{if isset($psll_gps_latitude) && isset($psll_gps_longitude) && ($psll_gps_latitude != 0 || $psll_gps_longitude != 0)}
    <div class="box" id="psll-order-detail-gps-location">
        <h4>{l s='Delivery GPS Location' mod='pslocationlocator'}</h4>
        <p>
            {l s='Latitude:' mod='pslocationlocator'} {$psll_gps_latitude|escape:'html':'UTF-8'}<br>
            {l s='Longitude:' mod='pslocationlocator'} {$psll_gps_longitude|escape:'html':'UTF-8'}
        </p>
        <p>
            <a href="https://www.google.com/maps?q={$psll_gps_latitude|escape:'url'},{$psll_gps_longitude|escape:'url'}" target="_blank">
                {l s='View on Google Maps' mod='pslocationlocator'}
            </a>
        </p>
    </div>
{/if}

{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
* @author    Mohammad Babaei <info@adschi.com>
* @copyright 2024 Mohammad Babaei
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

{if $psll_show_city_dropdown}
    <div class="form-group row">
        <label class="col-md-3 form-control-label" for="psll_city_dropdown">{l s='City' mod='pslocationlocator'}</label>
        <div class="col-md-7">
            <select name="city" id="psll_city_dropdown" class="form-control">
                <option value="">{l s='-- Select Your City --' mod='pslocationlocator'}</option>
                {foreach from=$psll_cities_list item=city_name}
                    <option value="{$city_name|escape:'htmlall':'UTF-8'}"
                            {if $psll_current_city == $city_name}selected="selected"{/if}>
                        {$city_name|escape:'htmlall':'UTF-8'}
                    </option>
                {/foreach}
            </select>
            {* The original city text input will be hidden by JavaScript (Step 4) *}
            {* We are providing a new select element with name="city" *}
        </div>
    </div>
{/if}

{if $psll_show_gps_button}
    <div class="form-group row">
        <label class="col-md-3 form-control-label">{l s='GPS Location' mod='pslocationlocator'}</label>
        <div class="col-md-7">
            <button type="button" id="psll_get_location_btn" class="btn btn-primary">{l s='Get My Current Location' mod='pslocationlocator'}</button>
            <small id="psll_gps_status" class="form-text text-muted"></small>
        </div>
    </div>

    {* Hidden fields to store GPS coordinates. These will be populated by JavaScript. *}
    <input type="hidden" name="gps_latitude" id="psll_gps_latitude" value="{$psll_existing_latitude|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="gps_longitude" id="psll_gps_longitude" value="{$psll_existing_longitude|escape:'htmlall':'UTF-8'}">

    {* Optional: Display current coordinates if they exist for debugging or info *}
    {if $psll_existing_latitude && $psll_existing_longitude}
        <div class="form-group row">
            <div class="offset-md-3 col-md-7">
                <small class="form-text text-info">
                    {l s='Current saved GPS:' mod='pslocationlocator'} {$psll_existing_latitude|escape:'htmlall':'UTF-8'}, {$psll_existing_longitude|escape:'htmlall':'UTF-8'}
                </small>
            </div>
        </div>
    {/if}
{/if}

{*
 * طراح
 * Mohammad Babaei
 * وب‌سایت https://adschi.com
 *}

{if isset($psll_qr_code_image_path) && $psll_qr_code_image_path && (!isset($psll_qr_code_error) || !$psll_qr_code_error)}
    <div style="text-align: right; padding-top: 10mm; clear:both; position:relative; page-break-inside: avoid;">
        <p style="margin:0; padding:0; font-size: 8pt;">{l s='Scannable Location QR Code:' mod='pslocationlocator'}</p>
        <img src="{$psll_qr_code_image_path|escape:'html':'UTF-8'}" style="width:25mm; height:25mm; border:0;">
    </div>
{else if isset($psll_qr_code_error) && $psll_qr_code_error}
    {* Optionally display an error message in the PDF, though it might clutter it. *}
    {* <p style="font-size: 7pt; color: red;">{l s='QR Code could not be generated:' mod='pslocationlocator'} {$psll_qr_code_error|escape:'html':'UTF-8'}</p> *}
{/if}

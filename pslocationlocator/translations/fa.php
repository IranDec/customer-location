<?php
/**
 * طراح
 * Mohammad Babaei
 * وب‌سایت https://adschi.com
 */

global $_MODULE;
$_MODULE = array();

// Using English strings as keys
$_MODULE['Location Locator'] = 'تعیین کننده موقعیت مکانی';
$_MODULE['Captures GPS location on address form and enhances order details with location data.'] = 'موقعیت GPS را در فرم آدرس دریافت کرده و جزئیات سفارش را با داده‌های مکانی بهبود می‌بخشد.';
$_MODULE['Are you sure you want to uninstall this module?'] = 'آیا مطمئن هستید که می‌خواهید این ماژول را حذف نصب کنید؟';
$_MODULE['Settings'] = 'تنظیمات';
$_MODULE['Allowed Cities'] = 'شهرهای مجاز';
$_MODULE['Enter city names, separated by commas. Case-sensitive.'] = 'نام شهرها را وارد کنید، با کاما از هم جدا شوند. حساس به حروف بزرگ و کوچک.';
$_MODULE['Allowed Carriers'] = 'حامل‌های مجاز';
$_MODULE['Select carriers for which this location logic applies.'] = 'حامل‌هایی را انتخاب کنید که این منطق مکانی برای آن‌ها اعمال شود.';
$_MODULE['Save'] = 'ذخیره';
$_MODULE['Settings updated for cities.'] = 'تنظیمات برای شهرها به‌روز شد.';
$_MODULE['Settings updated for carriers.'] = 'تنظیمات برای حامل‌ها به‌روز شد.';
$_MODULE['No carriers selected. Allowed carriers list cleared.'] = 'هیچ حاملی انتخاب نشده است. لیست حامل‌های مجاز پاک شد.';
$_MODULE['Shipping method not available for your city.'] = 'روش ارسال برای شهر شما در دسترس نیست.';
$_MODULE['Could not verify shipping address. Please try again.'] = 'خطا در تأیید آدرس حمل و نقل. لطفاً دوباره تلاش کنید.';
$_MODULE['QR Code library not found.'] = 'کتابخانه QR Code یافت نشد.';
$_MODULE['QR Code class not found.'] = 'کلاس QR Code یافت نشد.';
$_MODULE['QR code directory creation failed.'] = 'ایجاد دایرکتوری QR code ناموفق بود.';
$_MODULE['QR code generation failed.'] = 'تولید QR code ناموفق بود.';
$_MODULE['QR code generation error.'] = 'خطا در تولید QR code.';
$_MODULE['Delivery GPS Location'] = 'موقعیت GPS تحویل';
$_MODULE['Latitude:'] = 'عرض جغرافیایی:';
$_MODULE['Longitude:'] = 'طول جغرافیایی:';
$_MODULE['View on Google Maps'] = 'مشاهده در نقشه گوگل';
$_MODULE['دریافت موقعیت دقیق'] = 'دریافت موقعیت دقیق'; // Farsi key for Farsi string in TPL
$_MODULE['Scannable Location QR Code:'] = 'QR Code موقعیت مکانی قابل اسکن:';

// JS specific messages - these would be used if passed via Media::addJsDef
$_MODULE['Fetching location...'] = 'در حال دریافت موقعیت مکانی شما...';
$_MODULE['Location captured successfully.'] = 'موقعیت مکانی با موفقیت دریافت شد.';
$_MODULE['Error fetching location: '] = 'خطا در دریافت موقعیت مکانی: ';
$_MODULE['User denied the request for Geolocation.'] = 'دسترسی به موقعیت مکانی رد شد.';
$_MODULE['Location information is unavailable.'] = 'اطلاعات موقعیت مکانی در دسترس نیست.';
$_MODULE['The request to get user location timed out.'] = 'زمان درخواست برای دریافت موقعیت مکانی به پایان رسید.';
$_MODULE['An unknown error occurred.'] = 'یک خطای ناشناخته رخ داد.';
$_MODULE['Geolocation is not supported by this browser.'] = 'مرورگر شما از موقعیت یابی جغرافیایی پشتیبانی نمی کند.';

// Additional translations from templates if any were missed
$_MODULE['Failed to install database table.'] = 'نصب جدول پایگاه داده ناموفق بود.';

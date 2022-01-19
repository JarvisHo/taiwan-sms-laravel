# Taiwan SMS for Laravel 
## Supports:
1. 簡訊王 kotsms
2. Every8d 企業簡訊平台
3. Infobip

## Steps:
1. $ composer require jarvisho/taiwan-sms-laravel
2. $ php artisan vendor:publish --provider="Jarvisho\TaiwanSmsLaravel\TaiwanSmsServiceProvider"
3. $ vim config/taiwan_sms.php
4. Change your primary SMS service provider
5. $ vim .env
6. Add your SMS service username and password fields

## Example:
```php
$phone = '0988123123';
$text = '測試中文 Test ABC 123';

try {
    TaiwanSms::send($phone, $text);
    
} catch (\Exception $exception) {
    return redirect()->back()->with(['error' => $exception->getMessage()]);
}
```
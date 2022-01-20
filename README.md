# Taiwan SMS for Laravel 台灣簡訊服務的整合套件
### 可自訂備援簡訊服務，當主要服務出狀況可自動切換使發送服務
## 可支援簡訊商 Supports:
1. 三竹簡訊 Mitake 
2. 簡訊王 kotsms
3. Every8d 企業簡訊平台
4. Infobip

## 安裝步驟 Steps:
1. 安裝套件 $ composer require jarvisho/taiwan-sms-laravel
2. 複製設定 $ php artisan vendor:publish --provider="Jarvisho\TaiwanSmsLaravel\TaiwanSmsServiceProvider"
3. 查看設定 $ vim config/taiwan_sms.php
4. 設置您的主要簡訊供應商(需先申請權限)與確認帳號密碼的 ENV key 
5. 把帳號密碼設定到專安環境設定檔 $ vim .env

## 設定檔 Config
`路徑：/configs/taiwan_sms.php`
```php
<?php
return [
    'primary' => 'every8d', // 主要 SMS 服務商
    'failover' => '', // 次要(選填) 備援 SMS 服務商
    'timeout' => 5, // 等待多久判定服務無回應，自動切換服務商或返回狀態
    'every8d' => [
        'url' => env('EVERY8D_URL', 'http://biz3.every8d.com.tw/prepaid/API21/HTTP/sendSMS.ashx?UID=%s&PWD=%s&SB=%s&MSG=%s&DEST=%s'),
        'username' => env('EVERY8D_USERNAME'), // 將 key 複製到專案跟目錄的 .env 裡面，並加上您的帳號，例如：EVERY8D_USERNAME=example2022
        'password' => env('EVERY8D_PASSWORD'), // 將 key 複製到專案跟目錄的 .env 裡面，並加上您的密碼，例如：EVERY8D_USERNAME=password2022
    ],
    'kotsms' => [
        'url' => env('KOTSMS_URL', 'https://api.kotsms.com.tw/kotsmsapi-1.php?username=%s&password=%s&dstaddr=%s&smbody=%s&response='),
        'username' => env('KOTSMS_USERNAME'),
        'password' => env('KOTSMS_PASSWORD'),
    ],
    'infobip' => [
        'url' => env('INFOBIP_URL', 'https://vqlkm.api.infobip.com'),
        'username' => env('INFOBIP_USERNAME'),
        'password' => env('INFOBIP_PASSWORD'),
    ],
    'mitake' => [
        'url' => env('MITAKE_URL', 'https://sms.mitake.com.tw/b2c/mtk/SmSend?CharsetURL=UTF-8'),
        'username' => env('MITAKE_USERNAME'),
        'password' => env('MITAKE_PASSWORD'),
    ]
];
```

## 使用範例 Example:
```php
$phone = '0988123123';
$text = '測試中文 Test ABC 123';

try {
    TaiwanSms::send($phone, $text);
    
} catch (\Exception $exception) {
    return redirect()->back()->with(['error' => $exception->getMessage()]);
}
```
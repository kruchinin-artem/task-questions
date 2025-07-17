# Диагностика и устранение cURL error 28

## 1. Базовая доступность
ping example.com
traceroute example.com
telnet example.com 80
telnet example.com 443

## 1.1 Проверка DNS
nslookup example.com
dig example.com

## 2. Исходящие соединения
nc -zv example.com 80
nc -zv example.com 443

## 3. Увеличенные таймауты
curl --connect-timeout 30 --max-time 60 https://example.com

## 4. Анализ конфигурации
curl -I https://example.com
curl https://example.com/health-check
mtr --report example.com

## 5. Проверка SSL/TLS
openssl s_client -connect example.com:443 -servername example.com

---

## Решения

1. Увеличиваем таймаут соединения до 30 сек и общий таймаут до 120 сек
```php
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
```
2. Запрос whitelist IP у владельца сайта
- Применять при блокировке на уровне фаервола
- Предоставить владельцу ресурса IP вашего сервера

3. Оптимизация запросов:
- Реализовать кеширование ответов (Redis/Memcached)
- Использовать заголовок `If-Modified-Since`
- Запрашивать только необходимые данные (JSON API вместо HTML)

4. Retry-механизм
```php
$retries = 3;
$delay = 1;

while ($retries--) {
    $response = curl_exec($ch);
    if (curl_errno($ch) !== CURLE_OPERATION_TIMEDOUT) break;
    sleep($delay);
    $delay *= 2;
}
```

5. Для проблем с SSL:
```php
// Отключение проверки сертификатов (VERIFYPEER) делает соединение уязвимым!
if (ENVIRONMENT === 'test') {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}

// Принудительный TLS 1.2+:
curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
```

6. Логирование:
```php
error_log(sprintf(
    '[cURL ERROR] %d: %s (%s) | Code: %d',
    curl_errno($ch),
    curl_error($ch),
    curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
    curl_getinfo($ch, CURLINFO_HTTP_CODE)
));
```

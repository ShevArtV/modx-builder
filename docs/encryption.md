# Шифрование пакетов

Package Builder поддерживает шифрование платных пакетов через modstore.pro API (AES-256-CBC).

## Настройка

В `package_builder/packages/<name>/config.php`:

```php
'encrypt' => [
    'enable' => true,
    'login' => 'your-modstore-login',
    'api_key' => 'your-modstore-api-key',
],
```

Если `encrypt.enable = true` — пакет шифруется автоматически при `modxapp build`.

Для сборки без шифрования (например для тестирования):

```bash
modxapp build mypackage --no-encrypt
```

Результат: `mypackage-1.0.0-pl-ne.transport.zip` — постфикс `-ne` отличает незашифрованную версию.

## Как это работает

1. Пакет собирается обычным способом
2. PHP-файлы шифруются с помощью `EncryptedVehicle`
3. Шифрование использует алгоритм AES-256-CBC
4. Ключ дешифровки привязан к домену через modstore.pro API
5. При установке пакет автоматически запрашивает ключ

## Требования

- Аккаунт на modstore.pro
- Логин и API-ключ modstore.pro (указываются в конфиге пакета)
- Пакет должен быть зарегистрирован на modstore.pro

!!! warning "Безопасность"
    Не коммитьте `config.php` с логином и API-ключом в публичные репозитории. Добавьте `package_builder/packages/*/config.php` в `.gitignore` или используйте переменные окружения.

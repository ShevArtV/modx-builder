# extract-settings — Извлечение настроек

Автоматически находит все вызовы `$modx->getOption()` в PHP-коде и генерирует файл системных настроек.

## Использование

```bash
modxapp extract-settings <name>
```

`<name>` — имя пакета, указанное при `modxapp create`. Совпадает с именем папки в `package_builder/packages/` и `core/components/`.

## Пример

```bash
modxapp extract-settings mypackage
```

## Что делает

1. Сканирует все PHP-файлы в `core/components/<name>/` (исключая `vendor/`)
2. Находит вызовы `$modx->getOption('prefix_setting_name')`
3. Автоматически определяет тип настройки
4. Генерирует `core/components/<name>/elements/settings.php`

## Автоопределение типов

Экстрактор анализирует контекст использования настройки:

| Контекст | Тип |
|----------|-----|
| В условии `if/foreach/while` | `boolean` |
| `intval()`, `floatval()`, `number_format()` | `number` |
| `array`, `explode()`, `json_decode()` | `array` |
| Всё остальное | `string` |

## Автокатегоризация

Область (area) определяется из структуры ключа. Формат: `prefix_area_key` — второй сегмент после префикса пакета становится area:

| Ключ | Area |
|------|------|
| `mypkg_main_api_key` | `main` |
| `mypkg_debug_mode` | `debug` |
| `mypkg_email_from` | `email` |
| `mypkg_timeout` | `general` (только два сегмента) |

## Результат

Из кода:

```php
$timeout = $modx->getOption('mypackage_main_timeout');
$debug = $modx->getOption('mypackage_debug_mode');
```

Будет сгенерирован:

```php
<?php
return [
    'mypackage_main_timeout' => [
        'key' => 'mypackage_main_timeout',
        'value' => '',
        'xtype' => 'textfield',
        'namespace' => 'mypackage',
        'area' => 'main',
    ],
    'mypackage_debug_mode' => [
        'key' => 'mypackage_debug_mode',
        'value' => '',
        'xtype' => 'combo-boolean',
        'namespace' => 'mypackage',
        'area' => 'debug',
    ],
];
```

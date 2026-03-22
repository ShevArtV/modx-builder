# Конфигурация

Package Builder использует два типа конфигурации: **конфиг сборщика** и **конфиг пакета**.

## Конфиг сборщика

Определяет режим работы сборщика и данные автора: имя, email, git-логин, путь к шаблонам, выбор инструментов (PHPStan, CS Fixer, ESLint).

Конфиг сборщика бывает трёх видов:

### Дефолтный

Если вы не выполняли `modxapp config` — используются значения по умолчанию (`Your Name`, `your-email@example.com` и т.д.).

### Глобальный (`~/.modxapp/config.json`)

Создаётся командой [`modxapp config`](commands/config.md). Хранится в домашней директории пользователя — **не перезаписывается при обновлении** Package Builder.

- Linux: `~/.modxapp/config.json`
- macOS: `~/.modxapp/config.json`
- Windows: `%USERPROFILE%\.modxapp\config.json`

```json
{
    "author": "Shevchenko Arthur",   // имя автора пакета
    "email": "shev.art.v@ya.ru",     // email автора
    "gitlogin": "shevartv",          // git username (GitHub/GitLab)
    "phpVersion": "8.1",             // минимальная версия PHP
    "repository": "https://github.com/", // URL репозитория
    "template": "",                  // имя шаблона структуры (пусто = default)
    "generateElements": true,        // создавать файлы элементов при create
    "phpCsFixer": false,             // добавлять PHP CS Fixer при create
    "eslint": false,                 // добавлять ESLint при create
    "toolsConfigPath": ""            // путь к кастомным конфигам инструментов
}
```

!!! note "Формат"
    Комментарии показаны для пояснения — в реальном JSON их быть не должно.

### Локальный (`modxapp.json`)

Создаётся командой [`modxapp init`](commands/init.md) или автоматически при первом `modxapp create`. Копирует значения из глобального конфига. Хранится в корне проекта — можно закоммитить в репозиторий, чтобы все участники команды использовали одинаковые настройки.

```json
{
    "author": "Shevchenko Arthur",
    "email": "shev.art.v@ya.ru",
    "gitlogin": "shevartv",
    "phpVersion": "8.1",
    "repository": "https://github.com/",
    "template": "ecommerce",
    "generateElements": true,
    "phpCsFixer": true,
    "eslint": false,
    "toolsConfigPath": ""
}
```

### Приоритет

Локальный (`modxapp.json`) > Глобальный (`~/.modxapp/config.json`) > Дефолтный

---

## Конфиг пакета

Определяет параметры конкретного пакета: версию, элементы, настройки сборки, шифрование. Каждый пакет имеет свой конфиг.

**Путь:** `package_builder/packages/<name>/config.php`

Создаётся автоматически при `modxapp create`.

### Полный пример

```php
<?php
return [
    'name' => 'MyPackage',
    'name_lower' => 'mypackage',
    'name_short' => 'my',
    'version' => '1.0.0',
    'release' => 'alpha',

    'paths' => [
        'core' => 'core/components/mypackage/',
        'assets' => 'assets/components/mypackage/',
    ],

    'schema' => [
        'file' => 'schema/mypackage.mysql.schema.xml',
        'auto_generate_classes' => true,
        'update_tables' => true,
    ],

    'elements' => [
        'category' => 'MyPackage',
        'chunks' => 'elements/chunks.php',
        'snippets' => 'elements/snippets.php',
        'plugins' => 'elements/plugins.php',
        'templates' => 'elements/templates.php',
        'tvs' => 'elements/tvs.php',
        'settings' => 'elements/settings.php',
        'menus' => 'elements/menus.php',
        'events' => 'elements/events.php',
        'policies' => 'elements/policies.php',
        'policyTemplates' => 'elements/policyTemplates.php',
    ],

    'static' => [
        'chunks' => true,
        'snippets' => true,
        'plugins' => true,
        'templates' => true,
    ],

    'tools' => [
        'analyse' => 'vendor/bin/phpstan analyse --no-progress',
        'cs' => '',
        'lint' => '',
        'csMode' => 'fix',
    ],

    'build' => [
        'install' => false,         // устанавливать пакет после сборки
        'update' => [
            'chunks' => true,
            'snippets' => true,
            'settings' => false,
        ],
    ],

    'encrypt' => [
        'enable' => false,
        'login' => '',
        'api_key' => '',
    ],
];
```

### Описание секций

#### Основные поля

| Поле | Описание |
|------|----------|
| `name` | Имя компонента в PascalCase |
| `name_lower` | Имя в lowercase |
| `name_short` | Краткое имя (префикс для лексиконов и настроек) |
| `version` | Семантическая версия (`1.0.0`) |
| `release` | Тип релиза: `alpha`, `beta`, `pl` (production) |

#### paths

Пути к файлам компонента относительно корня проекта:

```php
'paths' => [
    'core' => 'core/components/mypackage/',
    'assets' => 'assets/components/mypackage/',
],
```

#### schema

Настройки работы с XML-схемой БД:

| Поле | Описание | По умолчанию |
|------|----------|-------------|
| `file` | Путь к файлу схемы | `schema/<name>.mysql.schema.xml` |
| `auto_generate_classes` | Генерировать PHP-классы | `true` |
| `update_tables` | Создавать/обновлять таблицы | `false` |

#### elements

Файлы описания элементов (относительно `package_builder/packages/<name>/`):

```php
'elements' => [
    'category' => 'MyPackage',
    'chunks' => 'elements/chunks.php',
    'snippets' => 'elements/snippets.php',
    'plugins' => 'elements/plugins.php',
    'templates' => 'elements/templates.php',
    'tvs' => 'elements/tvs.php',
    'settings' => 'elements/settings.php',
    'menus' => 'elements/menus.php',
    'events' => 'elements/events.php',
    'policies' => 'elements/policies.php',
    'policyTemplates' => 'elements/policyTemplates.php',
],
```

#### static

Какие элементы хранить как статические (MODX читает контент из файла, а не из БД):

```php
'static' => [
    'chunks' => true,
    'snippets' => true,
    'plugins' => true,
    'templates' => true,
],
```

#### tools

Команды инструментов, которые запускаются при `modxapp build`. Пустая строка — инструмент не запускается. Можно заменить на любой аналог:

```php
'tools' => [
    'analyse' => 'vendor/bin/phpstan analyse --no-progress',  // или 'vendor/bin/psalm'
    'cs' => '',                                                // или 'vendor/bin/php-cs-fixer fix', 'vendor/bin/pint'
    'lint' => '',                                              // или 'node_modules/.bin/eslint assets/', 'npx biome check'
    'csMode' => 'fix',                                         // 'fix' — автоисправление, 'check' — только проверка
],
```

#### build

Поведение при обновлении пакета — какие элементы перезаписывать при переустановке:

```php
'build' => [
    'update' => [
        'chunks' => true,       // обновлять чанки
        'snippets' => true,     // обновлять сниппеты
        'plugins' => true,      // обновлять плагины
        'templates' => true,    // обновлять шаблоны
        'tvs' => true,          // обновлять TV
        'menus' => true,        // обновлять меню
        'settings' => false,    // НЕ обновлять настройки
    ],
],
```

!!! warning "update → settings"
    Рекомендуется оставить `settings: false`, чтобы при обновлении пакета не перезатирать настройки, которые пользователь уже изменил на своём сайте.

#### encrypt

Шифрование пакета через modstore.pro API:

```php
'encrypt' => [
    'enable' => false,
    'login' => '',
    'api_key' => '',
],
```

Подробнее: [Шифрование](encryption.md)

!!! warning "Безопасность"
    Не коммитьте `config.php` с логином и API-ключом в публичные репозитории.

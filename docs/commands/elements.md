# elements — Добавление элементов в MODX

Создаёт и обновляет элементы MODX в базе данных из файлов описания. Используется при разработке, чтобы не добавлять элементы вручную через админку.

## Использование

```bash
modxapp elements <name>
```

## Пример

```bash
modxapp elements mypackage
```

## Поддерживаемые типы

Команда сканирует папку `package_builder/packages/<name>/elements/` и обрабатывает файлы:

| Файл | Элемент MODX | Описание |
|------|-------------|----------|
| `chunks.php` | Chunks | Компоненты шаблонов |
| `snippets.php` | Snippets | PHP-сниппеты |
| `plugins.php` | Plugins | Плагины с привязкой к событиям |
| `templates.php` | Templates | Шаблоны страниц |
| `tvs.php` | TV | Дополнительные поля (Template Variables) |
| `settings.php` | Settings | Системные настройки |
| `menus.php` | Menus | Пункты меню админки |
| `events.php` | Events | Кастомные события |
| `policies.php` | Policies | Политики доступа |
| `policyTemplates.php` | Policy Templates | Шаблоны политик |

## Формат файлов элементов

### Чанки

Контент чанка хранится в отдельном файле `core/components/<name>/elements/chunks/`.

```php
<?php
// package_builder/packages/mypackage/elements/chunks.php
return [
    'tpl.mypackage.hello' => [
        'description' => 'Приветственный чанк',
        'content' => 'file:elements/chunks/hello.tpl',
    ],
];
```

```html
<!-- core/components/mypackage/elements/chunks/hello.tpl -->
<p>Привет, [[+name]]!</p>
```

### Сниппеты

Код сниппета в файле `core/components/<name>/elements/snippets/`.

```php
<?php
// package_builder/packages/mypackage/elements/snippets.php
return [
    'mySnippet' => [
        'file' => 'mysnippet.php',
        'description' => 'Мой сниппет',
        'properties' => [
            'limit' => [
                'type' => 'textfield',
                'value' => '10',
                'desc' => 'Количество результатов',
            ],
        ],
    ],
];
```

```php
<?php
// core/components/mypackage/elements/snippets/mysnippet.php
return 'Hello from snippet!';
```

### Плагины

Код плагина в файле `core/components/<name>/elements/plugins/`.

```php
<?php
// package_builder/packages/mypackage/elements/plugins.php
return [
    'myPlugin' => [
        'file' => 'myplugin.php',
        'description' => 'Мой плагин',
        'events' => [
            'OnPageNotFound' => [],
            'OnLoadWebDocument' => ['priority' => 0],
        ],
    ],
];
```

### Шаблоны

Контент шаблона в файле `core/components/<name>/elements/templates/`.

```php
<?php
// package_builder/packages/mypackage/elements/templates.php
return [
    'myTemplate' => [
        'description' => 'Основной шаблон',
        'content' => 'file:elements/templates/mytemplate.tpl',
    ],
];
```

```html
<!-- core/components/mypackage/elements/templates/mytemplate.tpl -->
<!DOCTYPE html>
<html>
<head><title>[[*pagetitle]]</title></head>
<body>[[*content]]</body>
</html>
```

### TV-параметры

```php
<?php
// package_builder/packages/mypackage/elements/tvs.php
return [
    'my_image' => [
        'caption' => 'Изображение',
        'description' => 'Основное изображение',
        'type' => 'image',
        'default' => '',
        'elements' => '',
    ],
    'my_color' => [
        'caption' => 'Цвет',
        'description' => 'Цвет элемента',
        'type' => 'text',
        'default' => '#000000',
    ],
];
```

### Системные настройки

```php
<?php
// package_builder/packages/mypackage/elements/settings.php
return [
    'mypackage_api_key' => [
        'value' => '',
        'xtype' => 'textfield',
        'area' => 'general',
    ],
    'mypackage_debug' => [
        'value' => '0',
        'xtype' => 'combo-boolean',
        'area' => 'system',
    ],
];
```

### Меню

```php
<?php
// package_builder/packages/mypackage/elements/menus.php
return [
    'mypackage' => [
        'description' => 'mypackage_menu_desc',
        'action' => 'home',
        'parent' => 'components',
    ],
];
```

### События

```php
<?php
// package_builder/packages/mypackage/elements/events.php
return [
    'OnMyPackageBeforeSave',
    'OnMyPackageAfterSave',
];
```

### Политики доступа

Политика — набор разрешений, которые можно назначить группе пользователей. Поле `data` содержит разрешения и их значения.

```php
<?php
// package_builder/packages/mypackage/elements/policies.php
return [
    'mypackageManagerPolicy' => [
        'description' => 'Политика для менеджеров mypackage',
        'data' => [
            'mypackage_view' => true,
            'mypackage_edit' => true,
            'mypackage_save' => true,
            'mypackage_delete' => false,
            'mypackage_list' => true,
        ],
    ],
];
```

### Шаблоны политик доступа

Шаблон политики определяет набор доступных разрешений. Политики создаются на основе шаблонов — шаблон описывает *какие* разрешения возможны, а политика — *какие* из них включены.

```php
<?php
// package_builder/packages/mypackage/elements/policyTemplates.php
return [
    'mypackageManagerPolicyTemplate' => [
        'description' => 'Шаблон политики для менеджеров mypackage',
        'permissions' => [
            'mypackage_view' => [
                'description' => 'Просмотр элементов',
                'value' => true,
            ],
            'mypackage_edit' => [
                'description' => 'Редактирование элементов',
                'value' => true,
            ],
            'mypackage_save' => [
                'description' => 'Сохранение элементов',
                'value' => true,
            ],
            'mypackage_delete' => [
                'description' => 'Удаление элементов',
                'value' => true,
            ],
            'mypackage_list' => [
                'description' => 'Вывод списка элементов',
                'value' => true,
            ],
        ],
    ],
];
```

## Статичные элементы

Статичные элементы хранят контент в файлах — MODX читает его напрямую, а не из БД. Это удобно при разработке: редактируете файл в IDE, изменения видны сразу без пересборки.

Настройка в `package_builder/packages/<name>/config.php`:

```php
<?php
'static' => [
    'chunks' => true,      // чанки из файлов
    'snippets' => true,    // сниппеты из файлов
    'plugins' => true,     // плагины из файлов
    'templates' => true,   // шаблоны из файлов
],
```

Для статичных элементов контент указывается через `file:` (чанки, шаблоны) или `file` (сниппеты, плагины) — сборщик сохранит путь к файлу в `static_file`, и MODX будет читать контент из него.

## Логика работы

Для каждого элемента:

1. Ищет существующий элемент по имени/ключу
2. Если не найден — создаёт новый
3. Если найден — обновляет поля

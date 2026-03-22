# build — Сборка пакета

Собирает transport.zip пакет из исходников компонента.

## Использование

```bash
mxbuilder build <name> [options]
```

## Опции

| Опция | Описание |
|-------|----------|
| `--install` | Установить пакет после сборки |
| `--download` | Скачать transport.zip (только web-режим) |
| `--encrypt` | Зашифровать пакет через modstore.pro API |
| `--verbose` | Подробный вывод |

## Примеры

```bash
# Собрать пакет
mxbuilder build mypackage

# Собрать и установить
mxbuilder build mypackage --install

# Собрать с шифрованием
mxbuilder build mypackage --encrypt

# Через web
# http://site.ru/build_web.php?package=mypackage&install&download
```

## Процесс сборки

1. Загружает конфигурацию из `packages/<name>/config.php`
2. Создаёт `modPackageBuilder`
3. Регистрирует namespace компонента
4. Создаёт категорию элементов
5. Обрабатывает элементы (chunks, snippets, plugins, templates, TV, settings, menus, events, policies)
6. Копирует файлы `core/` и `assets/` с фильтрацией по `.packignore`
7. Добавляет резолверы
8. Упаковывает в `core/packages/<name>-<version>-<release>.transport.zip`
9. Устанавливает или предлагает скачать (если указаны флаги)

## Обработка элементов

Элементы описываются в файлах `packages/<name>/elements/`:

```php
// elements/snippets.php
return [
    [
        'name' => 'mySnippet',
        'description' => 'My snippet description',
        'file' => 'elements/snippets/mysnippet.snippet.php',
    ],
];
```

```php
// elements/plugins.php
return [
    [
        'name' => 'myPlugin',
        'description' => 'My plugin',
        'file' => 'elements/plugins/switch.php',
        'events' => [
            'OnPageNotFound' => [],
            'OnHandleRequest' => ['priority' => 0],
        ],
    ],
];
```

## Фильтрация файлов

При копировании файлов в transport-пакет применяется фильтрация через [.packignore](../packignore.md). Технические файлы (PHPStan, ESLint, node_modules и т.д.) исключаются автоматически.

## Web-режим

Для серверов без CLI PHP 8 доступна web-сборка:

```
build_web.php?package=mypackage
build_web.php?package=mypackage&install
build_web.php?package=mypackage&download
build_web.php?package=mypackage&encrypt
```

# Установка

## Системные требования

### Минимальные (headless-режим)

- **PHP** 8.1+
- **Composer**
- **Расширения PHP**: `zip`, `mbstring`

Достаточно для `setup`, `create`, `build`, `extract-*`.

### Для полного функционала

- **PHP** 8.1+
- **Composer**
- **MODX Revolution 3** (установленный)
- **MySQL/MariaDB**
- **Расширения PHP**: `pdo_mysql`, `zip`, `mbstring`

Необходимо для команд `elements`, `export` и `build --install`.

## Глобальная установка (рекомендуется)

```bash
composer global require shevartv/modx-builder
```

Команда `modxapp` будет доступна из любой директории.

!!! note "PATH"
    Убедитесь, что путь к глобальным Composer-пакетам добавлен в `PATH`. Узнать путь:
    ```bash
    composer global config bin-dir --absolute
    ```

    === "Linux"

        Добавьте в `~/.bashrc`:
        ```bash
        export PATH="$HOME/.config/composer/vendor/bin:$PATH"
        ```

    === "macOS"

        Добавьте в `~/.zshrc`:
        ```bash
        export PATH="$HOME/.composer/vendor/bin:$PATH"
        ```

    === "Windows"

        Добавьте в системную переменную `PATH`:
        ```
        %USERPROFILE%\AppData\Roaming\Composer\vendor\bin
        ```

## Локальная установка

```bash
composer require shevartv/modx-builder --dev
```

Команда вызывается как `vendor/bin/modxapp`.

## Структура проекта

После выполнения `modxapp setup` и `modxapp create <name>` в рабочей директории создаётся следующая структура:

```
core/components/<name>/              — исходники компонента
├── bootstrap.php
├── composer.json
├── docs/
├── lexicon/
├── schema/
├── src/
└── elements/                        — файлы элементов (опционально)

assets/components/<name>/            — публичные файлы (JS, CSS)
├── css/
│   ├── mgr/                         — стили для админки
│   └── web/                         — стили для фронтенда
└── js/
    ├── mgr/                         — скрипты для админки
    └── web/                         — скрипты для фронтенда

package_builder/packages/<name>/     — конфигурация сборки
├── config.php
├── elements/                        — описание элементов для сборки
│   ├── chunks.php
│   ├── snippets.php
│   ├── plugins.php
│   └── ...
└── resolvers/                       — скрипты установки/обновления/удаления
```

## Следующие шаги

- [Быстрый старт](quickstart.md) — пошаговое руководство от установки до первого пакета
- [Сценарии работы](workflows.md) — headless, файловая разработка, смешанная работа с админкой

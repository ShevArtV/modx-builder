# Установка

## Системные требования

### Минимальные (headless-режим)

- **PHP** 8.1+
- **Composer**
- **Расширения PHP**: `zip`, `pdo_sqlite`, `openssl`, `mbstring`
- **`allow_url_fopen = On`** — `setup` скачивает ядро MODX с GitHub

Достаточно для `setup`, `create`, `build`, `extract-*`. Если расширения не хватает,
`modxapp` остановится с ошибкой и подскажет, какую строку включить в `php.ini`.

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

## Windows

Работает в PowerShell и `cmd` — WSL не нужен.

1. **PHP 8.1+.** Скачайте zip с [windows.php.net](https://windows.php.net/download/) (или
   поставьте через `winget install PHP.PHP.8.3`) и добавьте каталог PHP в `PATH`.
2. **Расширения.** В дистрибутиве PHP для Windows они выключены. Скопируйте
   `php.ini-development` в `php.ini` и раскомментируйте:
   ```ini
   extension_dir = "ext"
   extension=openssl
   extension=pdo_sqlite
   extension=zip
   extension=mbstring
   ```
   Какой `php.ini` загружен — `php --ini`.
3. **Composer.** [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe) сам
   добавит `composer` в `PATH`; путь к глобальным пакетам — в примечании выше.
4. **Установка и первый пакет** (PowerShell):
   ```powershell
   composer global require shevartv/modx-builder
   modxapp setup
   modxapp create mypackage
   modxapp build mypackage
   ```

Ядро MODX и настройки `modxapp` лежат в `%USERPROFILE%\.modxapp`. Чтобы скачать ядро
заново, удалите этот каталог и запустите `modxapp setup`.

Шаблонные проверки при `build` (PHPUnit, PHPStan, PHP CS Fixer) запускаются из `vendor\bin`
компонента — сначала выполните `composer install` в `core\components\<имя>`.

## Локальная установка

```bash
composer require shevartv/modx-builder --dev
```

Команда вызывается как `vendor/bin/modxapp`.

## Следующие шаги

- [Быстрый старт](quickstart.md) — пошаговое руководство от установки до первого пакета
- [Сценарии работы](workflows.md) — headless, файловая разработка, смешанная работа с админкой

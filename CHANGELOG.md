# Changelog

## 0.4.8

### Windows

- Пути внутри transport.zip всегда с `/` — архив, собранный на Windows, ставится в MODX.
- Домашний каталог берётся из `HOME`, `USERPROFILE` или `HOMEDRIVE`+`HOMEPATH`; ядро
  standalone-режима лежит в `%USERPROFILE%\.modxapp`.
- Внешние команды (Composer, PHPUnit, PHPStan, PHP CS Fixer, ESLint) запускаются без
  shell: `.bat`/`.cmd` из `vendor/bin` и `composer.bat` находятся через `PATH`/`PATHEXT`.
- Поиск `core/config/config.inc.php` больше не зацикливается на корне диска (`C:\`).
- Пути с `\` и апострофами в сгенерированном `config.inc.php` экранируются.

### Все платформы

- Перед `setup` и `build` проверяются расширения `zip`, `pdo_sqlite`, `openssl` и
  `allow_url_fopen`; при нехватке — подсказка, что включить в `php.ini`.
- Повторный `setup` доустанавливает зависимости ядра, если прошлый `composer install` упал.
- Без Composer `setup` и `build --no-dev` завершаются понятной ошибкой.
- `create` при глобальной установке ссылается на `test-utils` из каталога modxapp —
  `composer install` в новом компоненте больше не падает на `modx/test-utils`.
- Имя пакета в шаблонном `composer.json` в нижнем регистре (`vendor/name`) — Composer
  отвергал `vendor/Name`.
- В transport.zip не попадают dev-файлы корня компонента: `tests/`, `phpunit.xml*`,
  кэш PHPUnit, `phpstan-stubs*`, `composer.phar`.
- `composer.json` объявляет `ext-zip`, `ext-json`; `pdo_sqlite` и `openssl` — в `suggest`.

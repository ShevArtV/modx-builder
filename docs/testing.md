# Тестирование

Инструкции для проверки всех возможностей Package Builder в двух режимах.

## Headless-режим (локально, без MODX)

### Подготовка

```bash
# Создать чистую директорию
mkdir ~/test-headless && cd ~/test-headless

# Убедиться что modxapp доступен
modxapp help
```

### 1. Setup — скачивание ядра MODX

```bash
modxapp setup
```

**Ожидаемый результат:**

- Скачано ядро MODX 3 в `core/`
- Создан `core/config/config.inc.php` с SQLite
- Создан `core/modxapp.sqlite`
- Созданы директории `core/cache/` и `core/packages/`

**Проверка:**

```bash
ls core/config/config.inc.php    # файл существует
ls core/modxapp.sqlite           # файл существует
ls core/src/Revolution/modX.php  # ядро скачано
```

### 2. Config — глобальные настройки

```bash
modxapp config
```

Ввести тестовые данные: автор, email, gitlogin. Остальное — Enter (значения по умолчанию).

**Проверка:** настройки сохранены, повторный `modxapp config` показывает введённые значения.

### 3. Init — локальный конфиг

```bash
modxapp init
```

**Ожидаемый результат:** создан `modxapp.json` в текущей директории.

**Проверка:**

```bash
cat modxapp.json    # содержит данные из глобального конфига
```

### 4. Create — создание пакета

#### Через флаги

```bash
modxapp create testpkg --elements --short-name=tst
```

**Проверка:**

```bash
ls core/components/testpkg/          # структура пакета создана
ls core/components/testpkg/src/      # главный класс
ls core/components/testpkg/schema/   # XML-схема
ls core/components/testpkg/lexicon/  # лексиконы
ls package_builder/packages/testpkg/config.php    # конфиг сборки
ls package_builder/packages/testpkg/elements/      # файлы элементов
```

#### Интерактивный режим

```bash
modxapp create testpkg2 --interactive
```

Проверить что вопросы задаются, значения по умолчанию подставляются из `modxapp.json`.

#### Валидация имени

```bash
modxapp create My_Package    # должна быть ошибка
modxapp create 123pkg        # должна быть ошибка
modxapp create my-package    # OK
```

#### С инструментами

```bash
modxapp create testpkg3 --elements --php-cs-fixer --eslint
```

**Проверка:**

```bash
ls core/components/testpkg3/phpstan.neon              # всегда
ls core/components/testpkg3/.php-cs-fixer.dist.php    # включён
ls core/components/testpkg3/eslint.config.js          # включён
ls core/components/testpkg3/package.json              # включён
```

#### Без инструментов

```bash
modxapp create testpkg4
```

**Проверка:**

```bash
ls core/components/testpkg4/phpstan.neon              # есть (всегда)
ls core/components/testpkg4/.php-cs-fixer.dist.php    # НЕТ
ls core/components/testpkg4/eslint.config.js          # НЕТ
```

### 5. Templates — кастомные шаблоны

```bash
# Скопировать оригинальные шаблоны
modxapp templates copy ./my-templates

# Проверить
ls ./my-templates/components/
ls ./my-templates/packages/

# Создать пакет из кастомных шаблонов
modxapp create testpkg5 --template=./my-templates
```

### 6. Schema — генерация классов

Заполнить XML-схему `core/components/testpkg/schema/testpkg.mysql.schema.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<model package="Testpkg\Model" baseClass="xPDO\Om\xPDOObject" platform="mysql" defaultEngine="InnoDB" version="3.0">
    <object class="TestItem" table="testpkg_items" extends="xPDO\Om\xPDOSimpleObject">
        <field key="name" dbtype="varchar" precision="255" phptype="string" null="false" default=""/>
        <field key="active" dbtype="tinyint" precision="1" phptype="boolean" null="false" default="1"/>
    </object>
</model>
```

```bash
modxapp schema testpkg
```

**Ожидаемый результат:** PHP-классы сгенерированы в `core/components/testpkg/src/`.

```bash
modxapp schema testpkg --validate
```

**Ожидаемый результат:** `Schema is valid`.

### 7. Extract-lexicons — извлечение лексиконов

Добавить в `core/components/testpkg/src/Testpkg.php` вызовы лексикона:

```php
$this->modx->lexicon('tst_hello_world');
$this->modx->lexicon('tst_error_not_found');
```

```bash
modxapp extract-lexicons testpkg
```

**Проверка:**

```bash
cat core/components/testpkg/lexicon/en/default.inc.php
# Должны быть ключи tst_hello_world и tst_error_not_found
```

### 8. Extract-settings — извлечение настроек

Добавить в код вызовы настроек:

```php
$this->modx->getOption('tst_api_timeout');
$this->modx->getOption('tst_debug');
```

```bash
modxapp extract-settings testpkg
```

**Проверка:**

```bash
cat core/components/testpkg/elements/settings.php
# Должны быть настройки tst_api_timeout и tst_debug
```

### 9. Build — сборка пакета

```bash
modxapp build testpkg
```

**Ожидаемый результат:** создан `core/packages/testpkg-1.0.0-alpha.transport.zip`.

**Проверка:**

```bash
ls core/packages/testpkg*.transport.zip
```

#### Сборка с пропуском проверок

```bash
modxapp build testpkg --no-check
```

### 10. Недоступные команды в headless

```bash
modxapp elements testpkg    # должна быть ошибка (нет таблиц MODX)
modxapp export testpkg      # должна быть ошибка (нет элементов в БД)
```

---

## Удалённый сервер (полный MODX)

### Подготовка

```bash
# Подключиться к серверу
ssh -p 1024 host1860015@serv21.hostland.ru

# Перейти в корень сайта
cd /home/host1860015/modx3.art-sites.ru/htdocs/www

# Установить Package Builder (если ещё не установлен)
# Используем PHP 8.2
/usr/local/php/php-8.2/bin/php /usr/local/bin/composer global require shevartv/modx-builder:dev-master
```

!!! note "PHP на сервере"
    На этом сервере дефолтный `php` — версия 5.3.29. Всегда используйте `/usr/local/php/php-8.2/bin/php`.

### 1. Config

```bash
modxapp config
```

### 2. Init

```bash
modxapp init
```

### 3. Create

```bash
modxapp create serverpkg --elements --short-name=srv
```

**Проверка:**

```bash
ls core/components/serverpkg/
ls package_builder/packages/serverpkg/
```

### 4. Elements — добавление в MODX

Отредактировать `package_builder/packages/serverpkg/elements/snippets.php`:

```php
<?php
return [
    'serverTest' => [
        'file' => 'connector.php',
        'description' => 'Test snippet from server',
    ],
];
```

```bash
modxapp elements serverpkg
```

**Проверка:** зайти в админку MODX → Элементы → Сниппеты → должен появиться `serverTest`.

### 5. Export — извлечение из БД

```bash
modxapp export serverpkg
```

**Проверка:**

```bash
cat package_builder/packages/serverpkg/elements/snippets.php
# Должен содержать serverTest
```

### 6. Build — сборка и установка

```bash
modxapp build serverpkg --install
```

**Проверка:**

- Файл `core/packages/serverpkg-1.0.0-alpha.transport.zip` создан
- В админке → Пакеты → serverpkg установлен

### 7. Schema — с созданием таблиц

Заполнить `core/components/serverpkg/schema/serverpkg.mysql.schema.xml` и в `config.php` поставить `update_tables => true`.

```bash
modxapp schema serverpkg
```

**Проверка:** таблицы созданы в БД, классы сгенерированы в `src/`.

### 8. Extract-lexicons и extract-settings

```bash
modxapp extract-lexicons serverpkg
modxapp extract-settings serverpkg
```

### 9. Полный цикл пересборки

```bash
# Изменить версию в config.php на 1.0.1
modxapp build serverpkg --install
```

**Проверка:** пакет обновился в админке.

---

## Чек-лист

### Headless

| # | Тест | Результат |
|---|------|-----------|
| 1 | `modxapp setup` — ядро скачано, SQLite создан | |
| 2 | `modxapp config` — глобальный конфиг сохранён | |
| 3 | `modxapp init` — `modxapp.json` создан | |
| 4 | `modxapp create` — структура пакета создана | |
| 5 | `modxapp create` с `--php-cs-fixer --eslint` — конфиги на месте | |
| 6 | `modxapp create` с невалидным именем — ошибка | |
| 7 | `modxapp templates copy` — шаблоны скопированы | |
| 8 | `modxapp create --template=` — кастомные шаблоны работают | |
| 9 | `modxapp schema` — классы сгенерированы | |
| 10 | `modxapp schema --validate` — валидация работает | |
| 11 | `modxapp extract-lexicons` — ключи извлечены | |
| 12 | `modxapp extract-settings` — настройки извлечены | |
| 13 | `modxapp build` — transport.zip создан | |
| 14 | `modxapp build --no-check` — проверки пропущены | |
| 15 | `modxapp elements` — ошибка (headless) | |
| 16 | `modxapp export` — ошибка (headless) | |

### Удалённый сервер

| # | Тест | Результат |
|---|------|-----------|
| 1 | `modxapp create` — структура создана | |
| 2 | `modxapp elements` — элементы в админке | |
| 3 | `modxapp export` — элементы извлечены в файлы | |
| 4 | `modxapp schema` — таблицы созданы | |
| 5 | `modxapp build --install` — пакет установлен | |
| 6 | `modxapp extract-lexicons` — ключи извлечены | |
| 7 | `modxapp extract-settings` — настройки извлечены | |
| 8 | Пересборка с новой версией — пакет обновлён | |

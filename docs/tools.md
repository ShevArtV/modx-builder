# Инструменты разработки

Package Builder автоматически настраивает инструменты контроля качества кода при создании пакета. Каждый инструмент решает свою задачу:

- **Статический анализатор** — находит ошибки в PHP-коде без запуска: неправильные типы аргументов, вызовы несуществующих методов, забытые проверки на null. Ловит баги до того, как код попадёт на сервер
- **Фиксер стиля кода** — автоматически приводит код к единому стандарту (PSR-12): расставляет отступы, сортирует импорты, убирает лишние пробелы. Код всех разработчиков выглядит одинаково
- **Линтер JS** — находит ошибки и проблемы в JavaScript: неиспользуемые переменные, отсутствующие точки с запятой, проблемы совместимости

Все три инструмента запускаются автоматически при `modxapp build` и могут быть заменены на аналоги.

## PHPUnit + test-utils

Unit-тестирование компонентов без реальной базы данных и MODX. При `modxapp create` автоматически генерируются `phpunit.xml`, директория `tests/` с примером теста и подключается библиотека `modx/test-utils`.

### Что даёт test-utils

Библиотека `modx/test-utils` содержит готовые моки и утилиты:

- **ModxTestCase** — базовый TestCase с моком `$this->modx` из коробки
- **MockModxTrait** — трейт для подключения мока modX в любой TestCase
- **ReflectionHelper** — работа с private методами и свойствами в тестах
- **MockQueryBuilder** — fluent-билдер для настройки моков SQL-запросов

Мок modX включает стабы: `log()`, `newQuery()`, `getObject()`, `getCollection()`, `getIterator()`, `getOption()`, а также моки `services`, `lexicon` и `cacheManager`.

### Запуск тестов

```bash
cd core/components/mypackage/
composer install
composer test
# или напрямую
vendor/bin/phpunit
```

### Пример теста

```php
<?php

namespace MyPackage\Tests;

use Modx3TestUtils\ModxTestCase;
use Modx3TestUtils\ReflectionHelper;

class MyServiceTest extends ModxTestCase
{
    use ReflectionHelper;

    public function testGetOptionReturnsValue(): void
    {
        $this->modxOptions['my_setting'] = 'value';
        $this->setUpModxMock();

        $this->assertSame('value', $this->modx->getOption('my_setting'));
    }

    public function testPrivateMethod(): void
    {
        $instance = self::createWithoutConstructor(MyService::class);
        self::setProperty($instance, 'modx', $this->modx);

        $result = self::invokeMethod($instance, 'calculate', [100]);
        $this->assertSame(110, $result);
    }

    public function testWithMockedObject(): void
    {
        $resource = $this->createMock(\MODX\Revolution\modResource::class);
        $resource->method('get')->willReturn('Test Page');
        $this->mockGetObject(\MODX\Revolution\modResource::class, $resource);

        $result = $this->modx->getObject(\MODX\Revolution\modResource::class, 1);
        $this->assertNotNull($result);
    }
}
```

### MockQueryBuilder

Для тестов со сложными SQL-запросами:

```php
use Modx3TestUtils\MockQueryBuilder;

$query = MockQueryBuilder::create($this)
    ->withRows([
        ['id' => 1, 'pagetitle' => 'Home'],
        ['id' => 2, 'pagetitle' => 'About'],
    ])
    ->build();

$this->modx->method('newQuery')->willReturn($query);
```

## PHPStan

Статический анализатор PHP. Включён по умолчанию. Добавляется в `composer.json` как dev-зависимость.

**Аналоги:** [Psalm](https://psalm.dev/), [Phan](https://github.com/phan/phan)

### Конфигурация

Файл `phpstan.neon` создаётся автоматически:

```yaml
parameters:
    level: 5
    paths:
        - src
        - elements
        - tests
    excludePaths:
        analyse:
            - src/Model
            - vendor
```

### Запуск

```bash
cd core/components/mypackage/
composer install
composer analyse
```

### Уровни анализа

PHPStan использует уровень **5** из 9 — строгая проверка типов без излишеств:

- Уровни 0–4: базовые проверки
- **Уровень 5**: проверка типов аргументов и возвращаемых значений
- Уровни 6–9: максимально строгие проверки

## PHP CS Fixer

Фиксер стиля PHP-кода. Автоматически форматирует код по стандарту PSR-12. Опциональный — включается флагом `--php-cs-fixer` при создании.

**Аналоги:** [Laravel Pint](https://laravel.com/docs/pint), [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)

### Конфигурация

Файл `.php-cs-fixer.dist.php`:

```php
<?php
$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/elements')
    ->exclude('Model');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'trailing_comma_in_multiline' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(false);
```

### Запуск

```bash
# Проверить код
composer cs-check

# Автоисправить
composer cs-fix
```

### Правила

| Правило | Описание |
|---------|----------|
| `@PSR12` | Стандарт PSR-12 |
| `array_syntax` | Короткий синтаксис массивов `[]` |
| `no_unused_imports` | Удаление неиспользуемых `use` |
| `ordered_imports` | Сортировка импортов по алфавиту |
| `single_quote` | Одинарные кавычки для строк |
| `trailing_comma_in_multiline` | Запятая после последнего элемента |

## ESLint

Линтер для JavaScript. Находит ошибки, проблемы стиля и потенциальные баги в JS-файлах компонента. Опциональный — включается флагом `--eslint` при создании.

**Аналоги:** [Biome](https://biomejs.dev/), [JSHint](https://jshint.com/)

### Конфигурация

Файл `eslint.config.js` (flat config, ESLint 9+):

```javascript
import js from '@eslint/js';
import globals from 'globals';

export default [
  {
    ignores: ['node_modules/**', 'lib/**'],
  },
  js.configs.recommended,
  {
    files: ['**/*.js'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        ...globals.browser,
      },
    },
    rules: {
      'indent': ['error', 2],
      'quotes': ['error', 'single', { allowTemplateLiterals: true }],
      'semi': ['error', 'always'],
      'no-useless-escape': 'off',
    }
  },
];
```

### Установка и запуск

```bash
npm install
npm run lint          # проверить
npm run lint:fix      # автоисправить
```

## Свои конфиги инструментов

По умолчанию при `create` используются встроенные конфиги. Если вы хотите, чтобы все проекты использовали одинаковые настройки инструментов — создайте папку с вашими конфигами:

```text
~/my-tool-configs/
├── phpstan.neon
├── .php-cs-fixer.dist.php
└── eslint.config.js
```

Укажите путь к папке в глобальном конфиге:

```bash
modxapp config
# → Path to custom tool configs: ~/my-tool-configs
```

Или в `modxapp.json` (локальный конфиг проекта):

```json
{
    "toolsConfigPath": "/home/user/my-tool-configs"
}
```

При `modxapp create` файлы из этой папки заменят встроенные шаблоны. Не обязательно класть все три файла — если в папке есть только `phpstan.neon`, заменён будет только он, остальные останутся встроенными.

## Автоматические проверки при сборке

При `modxapp build` перед сборкой запускаются инструменты, указанные в `package_builder/packages/<name>/config.php`:

```php
'tools' => [
    'analyse' => 'vendor/bin/phpstan analyse --no-progress',
    'cs' => 'vendor/bin/php-cs-fixer fix',
    'lint' => 'node_modules/.bin/eslint assets/',
    'csMode' => 'fix',
],
```

- **`analyse`** — статический анализ (по умолчанию PHPStan)
- **`cs`** — проверка/исправление стиля кода (по умолчанию пусто)
- **`lint`** — линтинг JS (по умолчанию пусто)
- **`csMode`** — `fix` (автоисправление) или `check` (только проверка, сборка прервётся при ошибках)

Если команда пустая — инструмент не запускается. Если инструмент не установлен — пропускается с предупреждением.

### Замена инструментов

Каждый инструмент можно заменить на аналог. Для этого нужно:

1. Установить новый инструмент в пакет
2. Создать его конфиг (если требуется)
3. Указать команду запуска в `package_builder/packages/<name>/config.php`

#### Пример: PHPStan → Psalm

```bash
# 1. Перейти в папку пакета
cd core/components/mypackage/

# 2. Убрать PHPStan, установить Psalm
composer remove --dev phpstan/phpstan
composer require --dev vimeo/psalm

# 3. Создать конфиг Psalm
vendor/bin/psalm --init
```

```php
// 4. В package_builder/packages/mypackage/config.php
'tools' => [
    'analyse' => 'vendor/bin/psalm --no-progress',
    // ...
],
```

Теперь при `modxapp build mypackage` вместо PHPStan запустится Psalm.

#### Пример: PHP CS Fixer → Laravel Pint

```bash
cd core/components/mypackage/
composer require --dev laravel/pint
```

```php
'tools' => [
    'cs' => 'vendor/bin/pint',
    // ...
],
```

Pint не требует конфига — работает из коробки с правилами Laravel (основаны на PSR-12).

#### Пример: ESLint → Biome

```bash
cd core/components/mypackage/
npm remove eslint @eslint/js globals
npm install --save-dev @biomejs/biome
npx biome init
```

```php
'tools' => [
    'lint' => 'npx biome check assets/',
    // ...
],
```

#### Отключение инструмента

Укажите пустую строку — инструмент не будет запускаться:

```php
'tools' => [
    'analyse' => '',    // статический анализ отключён
    'cs' => '',         // фиксер стиля отключён
    'lint' => '',       // линтинг JS отключён
],
```

Или пропустите все проверки разово:

```bash
modxapp build mypackage --no-check
```

!!! note "Зависимости должны быть установлены"
    Инструменты запускаются из папки пакета (`core/components/<name>/`). Перед сборкой выполните `composer install` и `npm install` (если используете JS-инструменты). Если инструмент не установлен — проверка пропускается с предупреждением, сборка продолжится.

## Настройка IDE

Для подсветки ошибок **в процессе разработки** (без ожидания сборки) настройте инструменты в вашем редакторе:

### PhpStorm

- **PHPStan**: Settings → PHP → Quality Tools → PHPStan → указать путь к `vendor/bin/phpstan` и конфиг `phpstan.neon`
- **PHP CS Fixer**: Settings → PHP → Quality Tools → PHP CS Fixer → указать путь к `vendor/bin/php-cs-fixer`
- **ESLint**: Settings → Languages & Frameworks → JavaScript → Code Quality Tools → ESLint → Automatic configuration

### VS Code

Установите расширения:

- [PHPStan](https://marketplace.visualstudio.com/items?itemName=SanderRonde.phpstan-vscode) — подсветка ошибок PHPStan
- [PHP CS Fixer](https://marketplace.visualstudio.com/items?itemName=junstyle.php-cs-fixer) — автоформатирование при сохранении
- [ESLint](https://marketplace.visualstudio.com/items?itemName=dbaeumer.vscode-eslint) — подсветка JS ошибок

После установки расширений ошибки будут подсвечиваться прямо в коде — без необходимости запускать `build`.

## Сборка пакета

Все файлы инструментов (`phpstan.neon`, `phpunit.xml`, `.php-cs-fixer.dist.php`, `eslint.config.js`, `package.json`, `node_modules/`, `tests/`) автоматически исключаются из transport-пакета через [.packignore](packignore.md).

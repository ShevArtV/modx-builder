# templates — Управление шаблонами

Позволяет просматривать, создавать и управлять шаблонами структуры пакетов.

## Использование

```bash
modxapp templates path              # показать путь к шаблону default
modxapp templates list              # список доступных шаблонов
modxapp templates copy <name>       # скопировать default в новый шаблон
```

## Примеры

```bash
# Список доступных шаблонов
modxapp templates list

# Создать кастомный шаблон на основе default
modxapp templates copy ecommerce

# Скопировать во внешнюю папку
modxapp templates copy ./external-templates
```

## Как работают шаблоны

Шаблоны хранятся в `package_builder/templates/`. Каждый шаблон — это папка с именем, повторяющая структуру MODX:

```text
package_builder/templates/
├── default/                              — встроенный шаблон
│   ├── core/components/                  → core/components/<name>/
│   ├── assets/components/                → assets/components/<name>/
│   └── package_builder/packages/         → package_builder/packages/<name>/
├── ecommerce/                            — кастомный шаблон
│   ├── core/components/
│   ├── assets/components/
│   └── package_builder/packages/
└── blog/                                 — ещё один кастомный
    └── ...
```

При `modxapp templates copy <name>`:

- Если `<name>` — простое имя (без `/`) — шаблон создаётся в `package_builder/templates/<name>/`
- Если `<name>` — путь (содержит `/`) — шаблон копируется по указанному пути

## Использование при создании пакета

```bash
# Использовать шаблон по имени
modxapp create my-shop --template=ecommerce

# По умолчанию — шаблон default
modxapp create my-tool
```

Шаблон ищется по приоритету:

1. `package_builder/templates/<name>/` — в проекте
2. Встроенные шаблоны Package Builder
3. Как абсолютный/относительный путь

Путь к шаблону можно задать в `modxapp.json` (через `modxapp init --interactive`) — тогда указывать `--template` при каждом `create` не нужно.

## Workflow

```bash
# 1. Создать кастомный шаблон
modxapp templates copy ecommerce

# 2. Отредактировать
#    package_builder/templates/ecommerce/core/components/   — файлы компонента
#    package_builder/templates/ecommerce/assets/components/  — JS, CSS
#    package_builder/templates/ecommerce/package_builder/packages/ — конфиг

# 3. Создать пакеты с разными шаблонами
modxapp create my-shop --template=ecommerce
modxapp create my-blog --template=blog
modxapp create my-tool                        # → default
```

## Плейсхолдеры в шаблонах

Файлы с расширением `.template` обрабатываются — в них заменяются плейсхолдеры:

| Плейсхолдер | Значение |
|-------------|----------|
| `{{package_name}}` | Имя в lowercase |
| `{{Package_name}}` | Имя в PascalCase |
| `{{short_name}}` | Краткое имя |
| `{{author_name}}` | Имя автора |
| `{{author_email}}` | Email автора |
| `{{php_version}}` | Версия PHP |
| `{{gitlogin}}` | Git username |
| `{{repository}}` | URL репозитория |
| `{{current_year}}` | Текущий год |
| `{{current_date}}` | Текущая дата |

Файлы без `.template` копируются как есть.

# init — Инициализация проекта

Создаёт локальный конфиг `modxapp.json` в текущей директории. По умолчанию копирует значения из глобального конфига без вопросов.

## Использование

```bash
modxapp init                    # создать modxapp.json из глобального конфига
modxapp init --interactive      # с возможностью изменить каждый параметр
```

## Что происходит

**Без `--interactive`:**

1. Копирует глобальный конфиг (`~/.modxapp/config.json`) в `modxapp.json`
2. Готово — можно работать

**С `--interactive`:**

1. Загружает значения из глобального конфига как значения по умолчанию
2. Для каждого параметра задаёт вопрос — можно принять (Enter) или ввести своё
3. Создаёт `modxapp.json` с введёнными значениями

Если `modxapp.json` уже существует, будет запрошено подтверждение на перезапись.

## Примеры

Быстрая инициализация:

```bash
$ modxapp init
SUCCESS: modxapp.json created from global config
```

Интерактивная — если нужно изменить параметры для этого проекта:

```bash
$ modxapp init --interactive

=== Package Builder Project Init ===
Configure settings for this project. Press Enter to use defaults.

Author name [Shevchenko Arthur]:
Author email [shev.art.v@ya.ru]:
Git login [shevartv]:
Minimum PHP version [8.1]:
Repository URL [https://github.com/]:
Templates path (leave empty for default): ./my-templates
Generate elements files? [Y/n]:
Add PHP CS Fixer? [y/N]: y
Add ESLint? [y/N]:

SUCCESS: modxapp.json created
```

## Формат modxapp.json

```json
{
    "author": "Shevchenko Arthur",
    "email": "shev.art.v@ya.ru",
    "gitlogin": "shevartv",
    "phpVersion": "8.1",
    "repository": "https://github.com/",
    "template": "",
    "generateElements": true,
    "phpCsFixer": false,
    "eslint": false
}
```

## Когда нужен init

- Если вы хотите настроить проект с параметрами, отличающимися от глобальных
- Если хотите задать путь к кастомным шаблонам для проекта
- Если работаете в команде — `modxapp.json` можно закоммитить в репозиторий, и все участники будут использовать одинаковые настройки

!!! tip "init не обязателен"
    Если вы не вызывали `init`, первый `modxapp create` автоматически создаст `modxapp.json` из глобального конфига.

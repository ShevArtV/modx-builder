# init — Инициализация проекта

Создаёт локальный конфиг `mxbuilder.json` в текущей директории. Значения по умолчанию берутся из глобального конфига.

## Использование

```bash
mxbuilder init
```

## Что происходит

1. Загружаются значения из глобального конфига (если настроен через `mxbuilder config`)
2. Для каждого параметра задаётся вопрос — можно принять значение по умолчанию (Enter) или ввести своё
3. Создаётся файл `mxbuilder.json` в текущей директории

Если `mxbuilder.json` уже существует, будет запрошено подтверждение на перезапись.

## Пример

```bash
$ mxbuilder init

=== Package Builder Project Init ===
Configure settings for this project. Press Enter to use defaults.

Author name [Shevchenko Arthur]:
Author email [shev.art.v@ya.ru]:
Git login [shevartv]:
Minimum PHP version [8.1]:
Repository URL [https://github.com/]:
Templates path (leave empty for default):
Generate elements files? [Y/n]:
Add PHP CS Fixer? [y/N]:
Add ESLint? [y/N]:

SUCCESS: mxbuilder.json created
```

## Формат mxbuilder.json

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
- Если работаете в команде — `mxbuilder.json` можно закоммитить в репозиторий, и все участники будут использовать одинаковые настройки

!!! tip "init не обязателен"
    Если вы не вызывали `init`, первый `mxbuilder create` автоматически создаст `mxbuilder.json` из глобального конфига.

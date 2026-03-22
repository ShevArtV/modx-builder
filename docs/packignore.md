# .packignore

Файл `.packignore` управляет фильтрацией файлов при сборке transport-пакета. Синтаксис аналогичен `.gitignore`.

## Расположение

```text
packages/<name>/.packignore
```

## Синтаксис

```gitignore
# Комментарий
*.log                    # Все .log файлы
vendor/                  # Директория целиком
src/scss/                # Конкретная директория
**/*.bak                 # На любом уровне вложенности
!dist/app.js             # Исключение из игнора
temp?.txt                # ? — один любой символ
log[0-9].txt             # Классы символов
```

## Паттерны по умолчанию

Эти файлы и папки исключаются **всегда**, даже без `.packignore`:

```text
.git/
.gitignore
.gitattributes
.idea/
.vscode/
.DS_Store
Thumbs.db
phpstan.neon
phpstan.neon.dist
phpstan-baseline.neon
.php-cs-fixer.dist.php
.php-cs-fixer.cache
eslint.config.js
node_modules/
package.json
package-lock.json
```

## Пример .packignore

```gitignore
# Логи
logs/
*.log

# Зависимости
composer.lock
vendor/
node_modules/

# IDE
.idea/
.vscode/
*.swp
*.swo
*~

# OS
.DS_Store
Thumbs.db

# Git
.git/
.gitignore
.gitattributes

# Тесты
tests/
testing/
phpunit.xml

# Сборка фронтенда (исходники)
src/js/
src/scss/
webpack.config.js
package.json
package-lock.json

# Временные файлы
*.bak
*.tmp
*.orig
```

## Логика работы

1. Сначала применяются встроенные паттерны
2. Затем загружается `.packignore` из папки пакета
3. Правила обрабатываются последовательно, последнее совпадение побеждает
4. Паттерн с `!` отменяет предыдущее игнорирование
5. Паттерн с `/` в конце применяется только к директориям

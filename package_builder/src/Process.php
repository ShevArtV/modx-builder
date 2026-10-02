<?php

declare(strict_types=1);

namespace ComponentBuilder;

/**
 * Запуск внешних команд без shell: работает одинаково в bash и в cmd.exe.
 * Нет `cd X &&` (на Windows не меняет диск), `which` и `2>/dev/null` (их нет в cmd).
 */
final class Process
{
    public static function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }

    /**
     * Запускает команду в каталоге $cwd, stderr сливается в stdout.
     *
     * @param string[] $command argv: исполняемый файл и аргументы
     * @return array{0: int, 1: string} код возврата и вывод
     */
    public static function run(array $command, string $cwd): array
    {
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['redirect', 1]], $pipes, $cwd);
        if (!is_resource($process)) {
            return [1, 'Failed to start: ' . implode(' ', $command)];
        }

        fclose($pipes[0]);
        $output = (string)stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        return [proc_close($process), $output];
    }

    /**
     * argv-префикс для запуска $bin или null, если он не найден.
     * Путь с '/' ищется от $cwd (vendor/bin/phpstan), иначе — в PATH.
     * PHP-скрипты (обёртки composer в vendor/bin, composer.phar) запускаются текущим PHP:
     * на Windows их нельзя исполнить напрямую.
     *
     * @return string[]|null
     */
    public static function resolve(string $bin, string $cwd = ''): ?array
    {
        if (str_contains($bin, '/') || str_contains($bin, '\\')) {
            // абсолютный путь: run() запускает из своего cwd, относительный от вызывающего там не найдётся
            $path = self::isAbsolute($bin) ? $bin : rtrim(realpath($cwd) ?: $cwd, '/\\') . '/' . $bin;
            if (is_file($path) && self::isPhpScript($path)) {
                return [PHP_BINARY, $path];
            }
            if (self::isWindows()) {
                foreach (['.bat', '.cmd', '.exe'] as $ext) {
                    if (is_file($path . $ext)) {
                        return [$path . $ext];
                    }
                }
            }
            return is_file($path) ? [$path] : null;
        }

        $path = self::which($bin);
        if ($path === null) {
            return null;
        }

        return self::isPhpScript($path) ? [PHP_BINARY, $path] : [$path];
    }

    /** Полный путь к $bin из PATH (на Windows — с расширениями из PATHEXT) или null. */
    public static function which(string $bin): ?string
    {
        $extensions = [''];
        if (self::isWindows() && pathinfo($bin, PATHINFO_EXTENSION) === '') {
            $extensions = explode(';', strtolower(getenv('PATHEXT') ?: '.com;.exe;.bat;.cmd'));
        }

        foreach (explode(PATH_SEPARATOR, getenv('PATH') ?: '') as $dir) {
            if ($dir === '') {
                continue;
            }
            foreach ($extensions as $ext) {
                $path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $bin . $ext;
                if (is_file($path) && (self::isWindows() || is_executable($path))) {
                    return $path;
                }
            }
        }

        return null;
    }

    /** Команда запуска composer или null. */
    public static function composer(): ?array
    {
        foreach (['composer', 'composer.phar'] as $bin) {
            $command = self::resolve($bin);
            if ($command !== null) {
                return $command;
            }
        }

        $candidates = [getcwd() . '/composer.phar', '/usr/local/bin/composer', '/usr/bin/composer'];
        try {
            array_unshift($candidates, FileSystem::homeDir() . '/bin/composer');
        } catch (\RuntimeException) {
        }
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return [PHP_BINARY, $path];
            }
        }

        return null;
    }

    private static function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/') || preg_match('#^[A-Za-z]:[/\\\\]#', $path) === 1;
    }

    private static function isPhpScript(string $path): bool
    {
        $head = (string)@file_get_contents($path, false, null, 0, 128);

        return str_starts_with($head, '<?php')
            || (str_starts_with($head, '#!') && str_contains(strtok($head, "\n"), 'php'));
    }
}

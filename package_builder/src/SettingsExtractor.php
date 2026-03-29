<?php

namespace ComponentBuilder;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SettingsExtractor
{
    private array $settings = [];

    public function extractFromDirectory(string $directory, string $packagePrefix): void
    {
        if (!is_dir($directory)) {
            echo "Directory not found: {$directory}\n";
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $path = $file->getPathname();
            if (str_contains($path, '/vendor/') || str_contains($path, '\\vendor\\')) {
                continue;
            }
            $this->extractFromFile($path, $packagePrefix);
        }
    }

    private function extractFromFile(string $filePath, string $packagePrefix): void
    {
        $content = file_get_contents($filePath);
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);

        $pattern = '/\$modx->getOption\([\'"](' . preg_quote($packagePrefix, '/') . '[^\'"]+)[\'"]/';

        preg_match_all($pattern, $content, $matches);

        if (empty($matches[1])) {
            return;
        }

        $lastFoundLine = 0;
        foreach ($matches[1] as $index => $key) {
            $lineNumber = $this->findLineNumber($matches[0][$index], $lines, $lastFoundLine);
            if ($lineNumber > 0) {
                $lastFoundLine = $lineNumber;
            }

            $lineOffset = $this->getOffsetByLine($content, $lineNumber);

            $this->settings[$key] = [
                'file' => basename($filePath),
                'line' => $lineNumber,
                'type' => $this->inferSettingType($content, $matches[0][$index], $lineOffset),
                'default' => $this->extractDefaultValue($content, $matches[0][$index], $lineOffset),
            ];
        }
    }

    private function findLineNumber(string $match, array $lines, int $startLine = 0): int
    {
        for ($i = $startLine; $i < count($lines); $i++) {
            if (str_contains($lines[$i], $match)) {
                return $i + 1;
            }
        }
        return 0;
    }

    private function getOffsetByLine(string $content, int $lineNumber): int
    {
        if ($lineNumber <= 1) {
            return 0;
        }

        $pos = 0;
        for ($i = 1; $i < $lineNumber; $i++) {
            $pos = strpos($content, "\n", $pos);
            if ($pos === false) {
                return strlen($content);
            }
            $pos++;
        }
        return $pos;
    }

    private function inferSettingType(string $content, string $match, int $offset = 0): string
    {
        $pos = strpos($content, $match, $offset);
        if ($pos === false) {
            return 'string';
        }
        $context = substr($content, $pos, 200);

        if (preg_match('/\b(if|foreach|while|switch)\b/', $context)) {
            return 'boolean';
        }

        if (preg_match('/\b(intval|floatval|number_format)\b/', $context)) {
            return 'number';
        }

        if (preg_match('/\b(array|explode|json_decode)\b/', $context)) {
            return 'array';
        }

        return 'string';
    }

    private function extractDefaultValue(string $content, string $match, int $offset = 0): string
    {
        $pos = strpos($content, $match, $offset);
        if ($pos === false) {
            return '';
        }
        $context = substr($content, $pos, 100);

        if (preg_match('/\$modx->getOption\([^,]+,\s*[^,]+,\s*[\'"]([^\'"]*)[\'"]/', $context, $matches)) {
            return $matches[1];
        }

        return '';
    }

    public function generateSettingsFile(string $packageName, string $packagePrefix, string $outputPath): void
    {
        $settingsData = [];

        foreach ($this->settings as $key => $info) {
            $settingsData[$key] = [
                'key' => $key,
                'value' => $info['default'],
                'xtype' => $this->getXType($info['type']),
                'namespace' => $packageName,
                'area' => $this->categorizeSetting($key),
            ];
        }

        ksort($settingsData);

        $content = "<?php\n\nreturn " . var_export($settingsData, true) . ";\n";

        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        file_put_contents($outputPath, $content);

        $lexiconPath = str_replace('elements/settings.php', 'lexicon/ru/setting.inc.php', $outputPath);
        $this->generateSettingsLexicon($packagePrefix, $lexiconPath);

        echo "Settings file generated: {$outputPath}\n";
        echo "Lexicon file generated: {$lexiconPath}\n";
        echo "Found " . count($this->settings) . " settings\n";
    }

    private function generateSettingsLexicon(string $packagePrefix, string $lexiconPath): void
    {
        $content = "<?php\n\n";

        foreach ($this->settings as $key => $info) {
            $settingKey = str_replace($packagePrefix, '', $key);
            $content .= "\$_lang['setting_{$settingKey}'] = '" . ucfirst($settingKey) . "';\n";
            $content .= "\$_lang['setting_{$settingKey}_desc'] = '';\n";
        }

        $lexiconDir = dirname($lexiconPath);
        if (!is_dir($lexiconDir)) {
            mkdir($lexiconDir, 0755, true);
        }

        file_put_contents($lexiconPath, $content);
    }

    private function getXType(string $type): string
    {
        return match ($type) {
            'boolean' => 'modx-combo-boolean',
            'number' => 'numberfield',
            'array' => 'textarea',
            default => 'textfield',
        };
    }

    private function categorizeSetting(string $key): string
    {
        $parts = explode('_', $key);

        if (count($parts) > 2) {
            return $parts[1];
        }

        return 'general';
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}

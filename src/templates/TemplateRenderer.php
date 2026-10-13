<?php

declare(strict_types=1);

namespace Gatovel\Mail\templates;

use Gatovel\Mail\themes\DefaultTheme;
use Gatovel\Mail\themes\GatovelTheme;
use InvalidArgumentException;
use RuntimeException;

final class TemplateRenderer
{
    private readonly string $baseDirectory;

    private readonly string $theme;

    public function __construct(
        string $baseDirectory,
        string $theme = 'gatovel'
    ) {
        $directory = realpath($baseDirectory);

        if ($directory === false || !is_dir($directory)) {
            throw new InvalidArgumentException(
                'Template directory does not exist.'
            );
        }

        if (!in_array($theme, ['gatovel', 'default'], true)) {
            throw new InvalidArgumentException(
                'Invalid mail theme.'
            );
        }

        $this->baseDirectory = $directory;
        $this->theme = $theme;
    }

    public function render(
        string $template,
        array $data = []
    ): string {
        $path = realpath(
            $this->baseDirectory . DIRECTORY_SEPARATOR . $template
        );

        if (
            $path === false
            || !is_file($path)
            || !is_readable($path)
            || !str_starts_with(
                $path,
                $this->baseDirectory . DIRECTORY_SEPARATOR
            )
        ) {
            throw new InvalidArgumentException(
                'Template not found or access denied.'
            );
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException(
                'Unable to read mail template.'
            );
        }

        $replacements = [];

        foreach ($data as $key => $value) {
            if (
                !is_string($key)
                || !preg_match(
                    '/^[a-zA-Z_][a-zA-Z0-9_]*$/',
                    $key
                )
            ) {
                throw new InvalidArgumentException(
                    'Invalid template variable name.'
                );
            }

            if (
                !is_scalar($value)
                && $value !== null
            ) {
                throw new InvalidArgumentException(
                    'Template variables must be scalar or null.'
                );
            }

            $replacements['{{ ' . $key . ' }}'] = htmlspecialchars(
                (string) $value,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );
        }

        $content = strtr($content, $replacements);

        $theme = match ($this->theme) {
            'gatovel' => new GatovelTheme(),
            'default' => new DefaultTheme(),
        };

        return $theme->render($content);
    }
}

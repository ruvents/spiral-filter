<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\I18n;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FIXME: Временный класс, пока не примут PR https://github.com/spiral/framework/pull/396.
 * Переводит все элементы массива на текущий язык.
 */
final class ArrayTranslator
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function trans(array $data): array
    {
        array_walk_recursive(
            $data,
            function (string &$text): void {
                $text = $this->translator->trans($text);
            },
        );

        return $data;
    }
}

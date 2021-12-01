<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Domain;

use Ruvents\SpiralFilter\I18n\ArrayTranslator;
use Ruvents\SpiralFilter\Exception\ValidationException;
use Ruvents\SpiralFilter\Filter\FilterExtractor;
use Ruvents\SpiralFilter\Filter\FilterFactory;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ScopeException;
use Spiral\Filters\InputInterface;
use Spiral\Http\Request\InputManager;

class FilterInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private FilterFactory $filterProvider,
        private InputInterface $input,
        private FilterExtractor $annotationExtractor,
        private InputManager $inputManager,
        private ?ArrayTranslator $translator = null,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core)
    {
        try {
            $ignoreEmptyFields = 0 === strcasecmp($this->inputManager->method(), 'PATCH');
        } catch (ScopeException $exception) {
            return $core->callAction($controller, $action, $parameters);
        }

        foreach ($this->annotationExtractor->extract($controller, $action) as $parameter => $filterClass) {
            if (isset($parameters[$parameter])) {
                continue;
            }

            try {
                $filter = $this->filterProvider->make(
                    $filterClass,
                    $this->input,
                    $parameters['@context'] ?? null,
                    $ignoreEmptyFields
                );
            } catch (ValidationException $exception) {
                $errors = array_merge([], ...array_values($exception->getErrors()));

                return [
                    'status' => 400,
                    'data' => $this->translator
                        ? $this->translator->trans($errors)
                        : $errors,
                ];
            }

            $parameters[$parameter] = $filter;
        }

        return $core->callAction($controller, $action, $parameters);
    }
}

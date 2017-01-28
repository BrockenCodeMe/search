<?php

declare(strict_types=1);

/*
 * This file is part of the RollerworksSearch package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search;

use Rollerworks\Component\Search\Exception\UnexpectedTypeException;
use Rollerworks\Component\Search\Value\Compare;
use Rollerworks\Component\Search\Value\PatternMatch;
use Rollerworks\Component\Search\Value\Range;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class GenericResolvedFieldType implements ResolvedFieldType
{
    /**
     * @var FieldType
     */
    private $innerType;

    /**
     * @var FieldTypeExtension[]
     */
    private $typeExtensions;

    /**
     * @var GenericResolvedFieldType
     */
    private $parent;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * Constructor.
     *
     * @param FieldType         $innerType
     * @param array             $typeExtensions
     * @param ResolvedFieldType $parent
     *
     * @throws UnexpectedTypeException When at least one of the given extensions is not an FieldTypeExtension
     */
    public function __construct(FieldType $innerType, array $typeExtensions = [], ResolvedFieldType $parent = null)
    {
        foreach ($typeExtensions as $extension) {
            if (!$extension instanceof FieldTypeExtension) {
                throw new UnexpectedTypeException($extension, FieldTypeExtension::class);
            }
        }

        $this->innerType = $innerType;
        $this->typeExtensions = $typeExtensions;
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getInnerType(): FieldType
    {
        return $this->innerType;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions(): array
    {
        return $this->typeExtensions;
    }

    /**
     * {@inheritdoc}
     */
    public function createField(string $name, array $options = []): FieldConfig
    {
        $options = $this->getOptionsResolver()->resolve($options);

        return $this->newField($name, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function buildType(FieldConfig $config, array $options)
    {
        if (null !== $this->parent) {
            $this->parent->buildType($config, $options);
        }

        $this->innerType->buildType($config, $options);

        foreach ($this->typeExtensions as $extension) {
            $extension->buildType($config, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createFieldView(FieldConfig $config, FieldSetView $view)
    {
        $view = $this->newView($view);
        $view->vars = array_merge($view->vars, [
            'name' => $config->getName(),
            'accept_ranges' => $config->supportValueType(Range::class),
            'accept_compares' => $config->supportValueType(Compare::class),
            'accept_pattern_matchers' => $config->supportValueType(PatternMatch::class),
        ]);

        return $view;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFieldView(SearchFieldView $view, FieldConfig $config, array $options)
    {
        if (null !== $this->parent) {
            $this->parent->buildFieldView($view, $config, $options);
        }

        $this->innerType->buildView($view, $config, $options);

        foreach ($this->typeExtensions as $extension) {
            $extension->buildView($config, $view);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsResolver(): OptionsResolver
    {
        if (null === $this->optionsResolver) {
            if (null !== $this->parent) {
                $this->optionsResolver = clone $this->parent->getOptionsResolver();
            } else {
                $this->optionsResolver = new OptionsResolver();
            }

            $this->innerType->configureOptions($this->optionsResolver);

            foreach ($this->typeExtensions as $extension) {
                $extension->configureOptions($this->optionsResolver);
            }
        }

        return $this->optionsResolver;
    }

    /**
     * Creates a new SearchField instance.
     *
     * Override this method if you want to customize the field class.
     *
     * @param string $name    The name of the field
     * @param array  $options The builder options
     *
     * @return FieldConfig The new field instance
     */
    protected function newField($name, array $options): FieldConfig
    {
        return new SearchField($name, $this, $options);
    }

    /**
     * Creates a new SearchFieldView instance.
     *
     * Override this method if you want to customize the view class.
     *
     * @param FieldSetView $view
     *
     * @return SearchFieldView The new view instance
     */
    protected function newView(FieldSetView $view): SearchFieldView
    {
        return new SearchFieldView($view);
    }
}

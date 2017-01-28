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

namespace Rollerworks\Component\Search\Tests\Extension\Core\Type;

use Rollerworks\Component\Search\Extension\Core\ChoiceList\View\ChoiceView;
use Rollerworks\Component\Search\Extension\Core\Type\CurrencyType;
use Rollerworks\Component\Search\FieldSetView;
use Rollerworks\Component\Search\Test\FieldTransformationAssertion;
use Rollerworks\Component\Search\Test\SearchIntegrationTestCase;
use Symfony\Component\Intl\Util\IntlTestHelper;

final class CurrencyTypeTest extends SearchIntegrationTestCase
{
    protected function setUp()
    {
        IntlTestHelper::requireIntl($this, false);

        parent::setUp();
    }

    public function testCurrenciesAreSelectable()
    {
        $field = $field = $this->getFactory()->createField('choice', CurrencyType::class);
        $field->finalizeConfig();

        // NB. Use the ISO value as view-format for easier input.
        FieldTransformationAssertion::assertThat($field)
            ->withInput('EUR')
            ->successfullyTransformsTo('EUR')
            ->andReverseTransformsTo('EUR');

        $view = $field->createView(new FieldSetView());

        $choices = $view->vars['choices'];

        $this->assertContains(new ChoiceView('EUR', 'EUR', 'Euro'), $choices, '', false, false);
        $this->assertContains(new ChoiceView('USD', 'USD', 'US Dollar'), $choices, '', false, false);
        $this->assertContains(new ChoiceView('SIT', 'SIT', 'Slovenian Tolar'), $choices, '', false, false);
    }
}

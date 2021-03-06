<?php
/**
 * Copyright (c) Enalean, 2020-Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

use Tuleap\Layout\ServiceUrlCollector;
use Tuleap\ProgramManagement\Domain\Program\Backlog\ProgramIncrement\ProgramIncrementArtifactLinkType;
use Tuleap\Test\Builders\ProjectTestBuilder;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace,Squiz.Classes.ValidClassName.NotCamelCaps
final class program_managementPluginTest extends \Tuleap\Test\PHPUnit\TestCase
{
    public function testProvidesArtLinkTypes(): void
    {
        $plugin  = new program_managementPlugin(1);
        $natures = [];
        $params  = ['natures' => &$natures];
        $plugin->getArtifactLinkNatures($params);

        self::assertEquals([new ProgramIncrementArtifactLinkType()], $natures);
    }

    public function testProvidesNaturePresenterWhenTheTypeIsExposedByThePlugin(): void
    {
        $plugin    = new program_managementPlugin(1);
        $presenter = null;
        $params    = ['shortname' => ProgramIncrementArtifactLinkType::ART_LINK_SHORT_NAME, 'presenter' => &$presenter];
        $plugin->getNaturePresenter($params);

        self::assertEquals(new ProgramIncrementArtifactLinkType(), $presenter);
    }

    public function testDoesNotProvideNaturePresenterWhenTheTypeIsNotExposedByThePlugin(): void
    {
        $plugin    = new program_managementPlugin(1);
        $presenter = null;
        $params    = ['shortname' => 'something', 'presenter' => &$presenter];
        $plugin->getNaturePresenter($params);

        self::assertNull($presenter);
    }

    public function testExposesSystemArtifactLinkType(): void
    {
        $plugin  = new program_managementPlugin(1);
        $natures = [];
        $params  = ['natures' => &$natures];
        $plugin->trackerAddSystemNatures($params);

        self::assertEquals([ProgramIncrementArtifactLinkType::ART_LINK_SHORT_NAME], $natures);
    }

    public function testSetsItsServiceURL(): void
    {
        $plugin    = new program_managementPlugin(1);
        $collector = new ServiceUrlCollector(ProjectTestBuilder::aProject()->withUnixName('Foo')->build(), 'plugin_program_management');
        $plugin->serviceUrlCollector($collector);
        self::assertEquals('/program_management/foo', $collector->getUrl());
    }

    public function testDoesNotTouchURLOfOthersServices(): void
    {
        $plugin    = new program_managementPlugin(1);
        $collector = new ServiceUrlCollector(ProjectTestBuilder::aProject()->withUnixName('bar')->build(), 'plugin_doingsomething');
        $plugin->serviceUrlCollector($collector);
        self::assertFalse($collector->hasUrl());
    }
}

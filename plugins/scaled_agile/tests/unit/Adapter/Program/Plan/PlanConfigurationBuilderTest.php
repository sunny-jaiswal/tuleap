<?php
/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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

namespace Tuleap\ScaledAgile\Adapter\Program\Plan;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tuleap\ScaledAgile\Program\Plan\PlanStore;
use Tuleap\ScaledAgile\Program\ProgramStore;
use Tuleap\ScaledAgile\ScaledAgileTracker;
use Tuleap\ScaledAgile\Team\Creation\TeamStore;
use Tuleap\Test\Builders\UserTestBuilder;

final class PlanConfigurationBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var PlanProgramAdapter
     */
    private $adapter;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|\ProjectManager
     */
    private $project_manager;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|\URLVerification
     */
    private $url_verification;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|PlanStore
     */
    private $plan_store;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|ProgramStore
     */
    private $program_store;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|TeamStore
     */
    private $team_store;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|\TrackerFactory
     */
    private $tracker_factory;

    protected function setUp(): void
    {
        $this->tracker_factory = Mockery::mock(\TrackerFactory::class);
        $this->plan_store      = Mockery::mock(PlanStore::class);

        $this->adapter = new PlanProgramIncrementConfigurationBuilder(
            $this->plan_store,
            $this->tracker_factory,
        );
    }

    public function testItThrowAnExceptionIfProgramTrackerIsNotFound(): void
    {
        $user = UserTestBuilder::aUser()->build();
        $this->plan_store->shouldReceive('getProgramIncrementTrackerId')->andReturn(1);

        $this->tracker_factory->shouldReceive('getTrackerById')->with(1)->andReturnNull();

        $this->expectException(ProgramNotFoundException::class);
        $this->adapter->buildProgramIncrementFromProjectId(100, $user);
    }

    public function testItThrowsAnExceptionIFUserCanNotSeeProgramTracker(): void
    {
        $user = UserTestBuilder::aUser()->build();
        $this->plan_store->shouldReceive('getProgramIncrementTrackerId')->andReturn(1);

        $tracker = Mockery::mock(\Tracker::class);
        $tracker->shouldReceive('getId')->andReturn(1);
        $tracker->shouldReceive('userCanView')->andReturnFalse();
        $this->tracker_factory->shouldReceive('getTrackerById')->with(1)->andReturn($tracker);

        $this->expectException(ConfigurationUserCanNotSeeProgramException::class);

        $this->adapter->buildProgramIncrementFromProjectId(100, $user);
    }

    public function testItBuildProgramIncrementTracker(): void
    {
        $user = UserTestBuilder::aUser()->build();
        $this->plan_store->shouldReceive('getProgramIncrementTrackerId')->andReturn(1);

        $tracker = Mockery::mock(\Tracker::class);
        $tracker->shouldReceive('getId')->andReturn(1);
        $tracker->shouldReceive('userCanView')->andReturnTrue();
        $this->tracker_factory->shouldReceive('getTrackerById')->with(1)->andReturn($tracker);

        $program_increment = new ScaledAgileTracker($tracker);

        self::assertEquals($program_increment, $this->adapter->buildProgramIncrementFromProjectId(100, $user));
    }
}
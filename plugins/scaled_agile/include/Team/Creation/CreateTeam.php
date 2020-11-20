<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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

namespace Tuleap\ScaledAgile\Team\Creation;

use Tuleap\ScaledAgile\Adapter\Program\Plan\ProgramAccessException;
use Tuleap\ScaledAgile\Adapter\Program\Plan\ProjectIsNotAProgramException;
use Tuleap\ScaledAgile\Adapter\Team\AtLeastOneTeamShouldBeDefinedException;
use Tuleap\ScaledAgile\Adapter\Team\ProjectIsAProgramException;
use Tuleap\ScaledAgile\Adapter\Team\TeamAccessException;

interface CreateTeam
{
    /**
     * @throws ProgramAccessException
     * @throws ProjectIsNotAProgramException
     * @throws AtLeastOneTeamShouldBeDefinedException
     * @throws ProjectIsAProgramException
     * @throws TeamAccessException
     */
    public function create(\PFUser $user, int $project_id, array $team_ids): void;
}
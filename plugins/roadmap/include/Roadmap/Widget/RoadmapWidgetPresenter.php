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

namespace Tuleap\Roadmap\Widget;

use Tuleap\Tracker\FormElement\Field\ArtifactLink\Nature\NaturePresenter;

/**
 * @psalm-immutable
 */
final class RoadmapWidgetPresenter
{
    /**
     * @var int
     */
    public $roadmap_id;
    /**
     * @var string
     */
    public $visible_natures;

    /**
     * @param NaturePresenter[] $visible_natures
     */
    public function __construct(int $roadmap_id, array $visible_natures)
    {
        $this->roadmap_id      = $roadmap_id;
        $this->visible_natures = \json_encode(array_values($visible_natures), \JSON_THROW_ON_ERROR);
    }
}

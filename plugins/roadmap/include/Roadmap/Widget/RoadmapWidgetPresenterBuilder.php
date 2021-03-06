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

use Tracker_FormElement_Field_ArtifactLink;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\Nature\NaturePresenter;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\Nature\NaturePresenterFactory;

class RoadmapWidgetPresenterBuilder
{
    /**
     * @var NaturePresenterFactory
     */
    private $nature_presenter_factory;

    public function __construct(NaturePresenterFactory $nature_presenter_factory)
    {
        $this->nature_presenter_factory = $nature_presenter_factory;
    }

    public function getPresenter(int $roadmap_id): RoadmapWidgetPresenter
    {
        $visible_natures = array_filter(
            $this->nature_presenter_factory->getOnlyVisibleNatures(),
            static function (NaturePresenter $nature) {
                return $nature->shortname !== Tracker_FormElement_Field_ArtifactLink::NATURE_IS_CHILD;
            }
        );

        return new RoadmapWidgetPresenter($roadmap_id, $visible_natures);
    }
}

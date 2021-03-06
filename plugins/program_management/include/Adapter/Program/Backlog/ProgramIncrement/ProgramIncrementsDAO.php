<?php
/**
 * Copyright (c) Enalean, 2021-Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Adapter\Program\Backlog\ProgramIncrement;

use Tuleap\DB\DataAccessObject;

class ProgramIncrementsDAO extends DataAccessObject
{
    /**
     * @psalm-return array{id:int}[]
     */
    public function searchOpenProgramIncrements(int $program_id): array
    {
        $sql = 'SELECT artifact.id
                FROM tracker_artifact AS artifact
                JOIN tracker_changeset ON (artifact.last_changeset_id = tracker_changeset.id)
                -- get open artifacts
                LEFT JOIN (
                    tracker_semantic_status AS status
                    JOIN tracker_changeset_value AS status_changeset ON (status.field_id = status_changeset.field_id)
                    JOIN tracker_changeset_value_list AS status_value ON (status_changeset.id = status_value.changeset_value_id)
                ) ON (artifact.tracker_id = status.tracker_id AND tracker_changeset.id = status_changeset.changeset_id)
                WHERE (status.open_value_id = status_value.bindvalue_id OR status.field_id IS NULL) AND
                      artifact.tracker_id IN (
                          SELECT program_increment_tracker_id
                          FROM plugin_program_management_plan
                          JOIN tracker ON (tracker.id = plugin_program_management_plan.program_increment_tracker_id)
                          WHERE tracker.group_id = ?
                      )';

        return $this->getDB()->run($sql, $program_id);
    }

    /**
     * @return array{id: int}[]
     */
    public function getProgramIncrementsLinkToFeatureId(int $artifact_id): array
    {
        $sql = "SELECT parent_art.id AS id
                FROM tracker_artifact parent_art
                    INNER JOIN tracker_field                        AS f          ON (f.tracker_id = parent_art.tracker_id AND f.formElement_type = 'art_link' AND use_it = 1)
                    INNER JOIN tracker_changeset_value              AS cv         ON (cv.changeset_id = parent_art.last_changeset_id AND cv.field_id = f.id)
                    INNER JOIN tracker_changeset_value_artifactlink AS artlink    ON (artlink.changeset_value_id = cv.id)
                    INNER JOIN tracker_artifact                     AS linked_art ON (linked_art.id = artlink.artifact_id)
                    INNER JOIN tracker                              AS t          ON (t.id = parent_art.tracker_id)
                    INNER JOIN tracker                              AS t_linked   ON (t_linked.id = linked_art.tracker_id AND t.group_id = t_linked.group_id)
                    INNER JOIN plugin_program_management_plan                     ON (plugin_program_management_plan.program_increment_tracker_id = parent_art.tracker_id)
                WHERE linked_art.id = ?";

        return $this->getDB()->run($sql, $artifact_id);
    }

    public function isProgramIncrementTracker(int $tracker_id): bool
    {
        $sql  = 'SELECT NULL FROM plugin_program_management_plan WHERE program_increment_tracker_id = ?';
        $rows = $this->getDB()->run($sql, $tracker_id);

        return count($rows) > 0;
    }
}

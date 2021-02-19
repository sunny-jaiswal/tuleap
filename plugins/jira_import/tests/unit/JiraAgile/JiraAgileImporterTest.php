<?php
/*
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
 *
 */

declare(strict_types=1);

namespace Tuleap\JiraImport\JiraAgile;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Tracker\Creation\JiraImporter\Import\Structure\FieldAndValueIDGenerator;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertSame;

class JiraAgileImporterTest extends TestCase
{
    public function testItHasNoBoards(): void
    {
        $board_retriever = new class implements JiraBoardsRetriever
        {
            public function getFirstScrumBoardForProject(string $jira_project_key): ?JiraBoard
            {
                assertSame('FOO', $jira_project_key);
                return null;
            }
        };

        $project_board_retriever = new JiraAgileImporter(
            $board_retriever,
            $this->getJiraSprintRetrieverWithoutSprints(),
        );

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project />');

        $project_board_retriever->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );
    }

    public function testItHasASprintTracker(): void
    {
        $project_board_retriever = $this->getJiraAgileImport();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $project_board_retriever->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );

        assertCount(1, $xml->trackers->tracker);
        assertEquals('T1', (string) $xml->trackers->tracker[0]['id']);
        assertEquals('Sprints', (string) $xml->trackers->tracker[0]->name);
        assertEquals('sprint', (string) $xml->trackers->tracker[0]->item_name);
        assertEquals('acid-green', (string) $xml->trackers->tracker[0]->color);
    }

    public function testSprintTrackerHasDetailsFieldset(): void
    {
        $project_board_retriever = $this->getJiraAgileImport();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $project_board_retriever->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );

        assertEquals(\Tracker_FormElementFactory::CONTAINER_FIELDSET_TYPE, (string) $xml->trackers->tracker->formElements->formElement[0]['type']);
        assertEquals(1, (int) $xml->trackers->tracker->formElements->formElement[0]['rank']);
        assertEquals('details', (string) $xml->trackers->tracker->formElements->formElement[0]->name);
        assertEquals('Details', (string) $xml->trackers->tracker->formElements->formElement[0]->label);
    }

    public function testSprintTrackerHasNameStringFieldReferencedInColumnsAndCriteria(): void
    {
        $project_board_retriever = $this->getJiraAgileImport();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $project_board_retriever->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );

        assertEquals(\Tracker_FormElementFactory::CONTAINER_FIELDSET_TYPE, (string) $xml->trackers->tracker->formElements->formElement[0]['type']);

        assertNotNull($xml->trackers->tracker->formElements->formElement[0]->formElements->formElement[0]);
        $string_field = $xml->trackers->tracker->formElements->formElement[0]->formElements->formElement[0];
        assertEquals('Name', (string) $string_field->label);
        assertEquals($string_field['ID'], (string) $xml->trackers->tracker->reports->report[0]->renderers->renderer[0]->columns->field[0]['REF']);
        assertEquals($string_field['ID'], (string) $xml->trackers->tracker->reports->report[0]->criterias->criteria[0]->field['REF']);

        $permissions = $xml->xpath(sprintf('/project/trackers/tracker/permissions/permission[@REF="%s"]', (string) $string_field['ID']));
        assertCount(3, $permissions);
        assertEquals('PLUGIN_TRACKER_FIELD_READ', $permissions[0]['type']);
        assertEquals('PLUGIN_TRACKER_FIELD_SUBMIT', $permissions[1]['type']);
        assertEquals('PLUGIN_TRACKER_FIELD_UPDATE', $permissions[2]['type']);
    }

    public function testSprintTrackerHasNameForTitleSemantic(): void
    {
        $jira_agile_importer = $this->getJiraAgileImport();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );

        $name_field = $xml->xpath('/project/trackers/tracker/formElements//formElement[name="name"]');

        $title_semantic = $xml->xpath('/project/trackers/tracker/semantics/semantic[@type="title"]');
        assertCount(1, $title_semantic);
        assertEquals($name_field[0]['ID'], $title_semantic[0]->field['REF']);
    }

    public function testSprintTrackerHasTimeframeSemanticWithStartAndEndDate(): void
    {
        $jira_agile_importer = $this->getJiraAgileImport();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );

        $start_date_field = $xml->xpath('/project/trackers/tracker/formElements//formElement[name="start_date"]');
        $end_date_field   = $xml->xpath('/project/trackers/tracker/formElements//formElement[name="end_date"]');

        $timeframe_semantic = $xml->xpath('/project/trackers/tracker/semantics/semantic[@type="timeframe"]');
        assertCount(1, $timeframe_semantic);
        assertEquals($start_date_field[0]['ID'], $timeframe_semantic[0]->start_date_field['REF']);
        assertEquals($end_date_field[0]['ID'], $timeframe_semantic[0]->end_date_field['REF']);
    }

    public function testSprintTrackerHasStatusSemantic(): void
    {
        $jira_agile_importer = $this->getJiraAgileImport();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );

        $status_field_id     = $xml->xpath('/project/trackers/tracker/formElements//formElement[name="status"]')[0]['ID'];
        $future_bindvalue_id = $xml->xpath('/project/trackers/tracker/formElements//formElement[name="status"]/bind/items/item[@label="future"]')[0]['ID'];
        $active_bindvalue_id = $xml->xpath('/project/trackers/tracker/formElements//formElement[name="status"]/bind/items/item[@label="active"]')[0]['ID'];

        $status_semantic = $xml->xpath('/project/trackers/tracker/semantics/semantic[@type="status"]');
        assertCount(1, $status_semantic);
        assertEquals($status_field_id, $status_semantic[0]->field['REF']);
        assertCount(2, $status_semantic[0]->open_values->open_value);
        assertEquals($future_bindvalue_id, $status_semantic[0]->open_values->open_value[0]['REF']);
        assertEquals($active_bindvalue_id, $status_semantic[0]->open_values->open_value[1]['REF']);
    }

    public function testSprintTrackerHasStartDateField(): void
    {
        $jira_agile_importer = $this->getJiraAgileImport();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );

        $start_date_field = $xml->xpath('/project/trackers/tracker/formElements//formElement[name="start_date"]');

        assertCount(1, $start_date_field);
        $id = (string) $start_date_field[0]['ID'];
        assertEquals('date', $start_date_field[0]['type']);
        assertEquals('1', $start_date_field[0]->properties['display_time']);

        $start_date_criteria = $xml->xpath(sprintf('/project/trackers/tracker/reports/report/criterias/criteria/field[@REF="%s"]', $id));
        assertCount(1, $start_date_criteria);

        $start_date_column = $xml->xpath(sprintf('/project/trackers/tracker/reports/report/renderers/renderer/columns/field[@REF="%s"]', $id));
        assertCount(1, $start_date_column);

        $permissions = $xml->xpath(sprintf('/project/trackers/tracker/permissions/permission[@REF="%s"]', $id));
        assertCount(3, $permissions);
        assertEquals('PLUGIN_TRACKER_FIELD_READ', $permissions[0]['type']);
        assertEquals('UGROUP_ANONYMOUS', $permissions[0]['ugroup']);
        assertEquals('PLUGIN_TRACKER_FIELD_SUBMIT', $permissions[1]['type']);
        assertEquals('UGROUP_REGISTERED', $permissions[1]['ugroup']);
        assertEquals('PLUGIN_TRACKER_FIELD_UPDATE', $permissions[2]['type']);
        assertEquals('UGROUP_PROJECT_MEMBERS', $permissions[2]['ugroup']);
    }

    public function testSprintTrackerHasEndDateField(): void
    {
        $jira_agile_importer = $this->getJiraAgileImport();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );

        $end_date_field = $xml->xpath('/project/trackers/tracker/formElements//formElement[name="end_date"]');

        assertCount(1, $end_date_field);
        $id = (string) $end_date_field[0]['ID'];
        assertEquals('date', $end_date_field[0]['type']);
        assertEquals('1', $end_date_field[0]->properties['display_time']);

        $end_date_criteria = $xml->xpath(sprintf('/project/trackers/tracker/reports/report/criterias/criteria/field[@REF="%s"]', $id));
        assertCount(1, $end_date_criteria);

        $end_date_column = $xml->xpath(sprintf('/project/trackers/tracker/reports/report/renderers/renderer/columns/field[@REF="%s"]', $id));
        assertCount(1, $end_date_column);

        $permissions = $xml->xpath(sprintf('/project/trackers/tracker/permissions/permission[@REF="%s"]', $id));
        assertCount(3, $permissions);
        assertEquals('PLUGIN_TRACKER_FIELD_READ', $permissions[0]['type']);
        assertEquals('UGROUP_ANONYMOUS', $permissions[0]['ugroup']);
        assertEquals('PLUGIN_TRACKER_FIELD_SUBMIT', $permissions[1]['type']);
        assertEquals('UGROUP_REGISTERED', $permissions[1]['ugroup']);
        assertEquals('PLUGIN_TRACKER_FIELD_UPDATE', $permissions[2]['type']);
        assertEquals('UGROUP_PROJECT_MEMBERS', $permissions[2]['ugroup']);
    }

    public function testSprintTrackerHasCompletedDateField(): void
    {
        $jira_agile_importer = $this->getJiraAgileImport();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );

        $completed_date_field = $xml->xpath('/project/trackers/tracker/formElements//formElement[name="completed_date"]');

        assertCount(1, $completed_date_field);
        $id = (string) $completed_date_field[0]['ID'];
        assertEquals('date', $completed_date_field[0]['type']);
        assertEquals('1', $completed_date_field[0]->properties['display_time']);

        $completed_date_criteria = $xml->xpath(sprintf('/project/trackers/tracker/reports/report/criterias/criteria/field[@REF="%s"]', $id));
        assertCount(1, $completed_date_criteria);

        $completed_date_column = $xml->xpath(sprintf('/project/trackers/tracker/reports/report/renderers/renderer/columns/field[@REF="%s"]', $id));
        assertCount(1, $completed_date_column);

        $permissions = $xml->xpath(sprintf('/project/trackers/tracker/permissions/permission[@REF="%s"]', $id));
        assertCount(3, $permissions);
        assertEquals('PLUGIN_TRACKER_FIELD_READ', $permissions[0]['type']);
        assertEquals('UGROUP_ANONYMOUS', $permissions[0]['ugroup']);
        assertEquals('PLUGIN_TRACKER_FIELD_SUBMIT', $permissions[1]['type']);
        assertEquals('UGROUP_REGISTERED', $permissions[1]['ugroup']);
        assertEquals('PLUGIN_TRACKER_FIELD_UPDATE', $permissions[2]['type']);
        assertEquals('UGROUP_PROJECT_MEMBERS', $permissions[2]['ugroup']);
    }

    public function testSprintTrackerHasStatusField(): void
    {
        $jira_agile_importer = $this->getJiraAgileImport();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );

        $status_field = $xml->xpath('/project/trackers/tracker/formElements//formElement[name="status"]')[0];

        $id = (string) $status_field['ID'];
        assertEquals('sb', $status_field['type']);
        assertEquals('static', $status_field->bind['type']);
        assertCount(3, $status_field->bind->items->item);
        assertEquals('future', $status_field->bind->items->item[0]['label']);
        assertEquals('active', $status_field->bind->items->item[1]['label']);
        assertEquals('closed', $status_field->bind->items->item[2]['label']);
        $active_value_id = $status_field->bind->items->item[1]['ID'];

        $status_criterion_field = $xml->xpath(sprintf('/project/trackers/tracker/reports/report/criterias/criteria/field[@REF="%s"]', $id));
        assertCount(1, $status_criterion_field);
        $selected_criterion_value = $status_criterion_field[0]->xpath('../criteria_value/selected_value');
        assertCount(1, $selected_criterion_value);
        assertEquals($active_value_id, $selected_criterion_value[0]['REF']);

        $status_column = $xml->xpath(sprintf('/project/trackers/tracker/reports/report/renderers/renderer/columns/field[@REF="%s"]', $id));
        assertCount(1, $status_column);

        $permissions = $xml->xpath(sprintf('/project/trackers/tracker/permissions/permission[@REF="%s"]', $id));
        assertCount(3, $permissions);
        assertEquals('PLUGIN_TRACKER_FIELD_READ', $permissions[0]['type']);
        assertEquals('UGROUP_ANONYMOUS', $permissions[0]['ugroup']);
        assertEquals('PLUGIN_TRACKER_FIELD_SUBMIT', $permissions[1]['type']);
        assertEquals('UGROUP_REGISTERED', $permissions[1]['ugroup']);
        assertEquals('PLUGIN_TRACKER_FIELD_UPDATE', $permissions[2]['type']);
        assertEquals('UGROUP_PROJECT_MEMBERS', $permissions[2]['ugroup']);
    }

    public function testItFetchesSprints(): void
    {
        $board                   = new JiraBoard(1, 'https://example.com', 10000, 'FOO');
        $project_board_retriever = new JiraAgileImporter(
            new class ($board) implements JiraBoardsRetriever
            {
                /**
                 * @var JiraBoard
                 */
                private $fetched_board;

                public function __construct(JiraBoard $fetched_board)
                {
                    $this->fetched_board = $fetched_board;
                }

                public function getFirstScrumBoardForProject(string $jira_project_key): ?JiraBoard
                {
                    return $this->fetched_board;
                }
            },
            new class ($board) implements JiraSprintRetriever {
                /**
                 * @var JiraBoard
                 */
                private $fetched_board;

                public function __construct(JiraBoard $fetched_board)
                {
                    $this->fetched_board = $fetched_board;
                }

                public function getAllSprints(JiraBoard $board): array
                {
                    assertSame($this->fetched_board, $board);
                    return [];
                }
            },
        );

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $project_board_retriever->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->build()
        );
    }

    public function testItCreatesOneSprintArtifact(): void
    {
        $jira_agile_importer = new JiraAgileImporter(
            $this->getJiraBoradRetrieverWithOneBoard(),
            $this->getJiraSprintRetrieverWithSprints(
                [JiraSprint::buildActive(1, 'Sprint 1')]
            )
        );

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->withUserName('forge__tracker_importer_user')->build()
        );

        assertCount(1, $xml->trackers->tracker[0]->artifacts->artifact);
        $xml_artifact_node = $xml->trackers->tracker[0]->artifacts->artifact[0];

        assertNotEmpty((string) $xml_artifact_node['id']);
        assertCount(1, $xml_artifact_node->changeset);
        assertEquals('username', $xml_artifact_node->changeset[0]->submitted_by['format']);
        assertEquals('forge__tracker_importer_user', $xml_artifact_node->changeset[0]->submitted_by);
        assertEquals('ISO8601', $xml_artifact_node->changeset[0]->submitted_on['format']);
        assertNotNull($xml_artifact_node->changeset[0]->submitted_on);
        assertNotNull($xml_artifact_node->changeset[0]->comments);
        assertCount(0, $xml_artifact_node->changeset[0]->comments->comment);

        $name_field_change = $xml_artifact_node->xpath('/project/trackers/tracker/artifacts/artifact/changeset/field_change[@field_name="name"]');
        assertCount(1, $name_field_change);
        assertEquals('string', $name_field_change[0]['type']);
        assertEquals('Sprint 1', $name_field_change[0]->value);
    }

    public function testItCreatesOneSprintArtifactWithStartDate(): void
    {
        $jira_agile_importer = new JiraAgileImporter(
            $this->getJiraBoradRetrieverWithOneBoard(),
            $this->getJiraSprintRetrieverWithSprints(
                [
                    JiraSprint::buildActive(1, 'Sprint 1')
                        ->withStartDate(new \DateTimeImmutable('2018-01-25T04:04:09.514Z'))
                ]
            )
        );

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->withUserName('forge__tracker_importer_user')->build()
        );

        $start_date_field_change = $xml->xpath('/project/trackers/tracker/artifacts/artifact/changeset/field_change[@field_name="start_date"]');
        assertCount(1, $start_date_field_change);
        assertEquals('date', $start_date_field_change[0]['type']);
        assertEquals('2018-01-25T04:04:09+00:00', $start_date_field_change[0]->value);
    }

    public function testItCreatesOneSprintArtifactWithEndDate(): void
    {
        $jira_agile_importer = new JiraAgileImporter(
            $this->getJiraBoradRetrieverWithOneBoard(),
            $this->getJiraSprintRetrieverWithSprints(
                [
                    JiraSprint::buildActive(1, 'Sprint 1')
                        ->withEndDate(new \DateTimeImmutable('2018-01-25T04:04:09.514Z'))
                ]
            )
        );

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->withUserName('forge__tracker_importer_user')->build()
        );

        $end_date_field_change = $xml->xpath('/project/trackers/tracker/artifacts/artifact/changeset/field_change[@field_name="end_date"]');
        assertCount(1, $end_date_field_change);
        assertEquals('date', $end_date_field_change[0]['type']);
        assertEquals('2018-01-25T04:04:09+00:00', $end_date_field_change[0]->value);
    }

    public function testItCreatesOneSprintArtifactWithCompletedDate(): void
    {
        $jira_agile_importer = new JiraAgileImporter(
            $this->getJiraBoradRetrieverWithOneBoard(),
            $this->getJiraSprintRetrieverWithSprints(
                [
                    JiraSprint::buildActive(1, 'Sprint 1')
                        ->withCompleteDate(new \DateTimeImmutable('2018-01-25T04:04:09.514Z'))
                ]
            )
        );

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->withUserName('forge__tracker_importer_user')->build()
        );

        $completed_date_field_change = $xml->xpath('/project/trackers/tracker/artifacts/artifact/changeset/field_change[@field_name="completed_date"]');
        assertCount(1, $completed_date_field_change);
        assertEquals('date', $completed_date_field_change[0]['type']);
        assertEquals('2018-01-25T04:04:09+00:00', $completed_date_field_change[0]->value);
    }

    public function testItCreatesSprintsArtifactWithStatus(): void
    {
        $jira_agile_importer = new JiraAgileImporter(
            $this->getJiraBoradRetrieverWithOneBoard(),
            $this->getJiraSprintRetrieverWithSprints(
                [
                    JiraSprint::buildClosed(1, 'Sprint 1'),
                    JiraSprint::buildActive(2, 'Sprint 2'),
                    JiraSprint::buildFuture(3, 'Sprint 3'),
                ]
            )
        );

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project><trackers/></project>');

        $jira_agile_importer->exportScrum(
            new NullLogger(),
            $xml,
            'FOO',
            new FieldAndValueIDGenerator(),
            UserTestBuilder::aUser()->withUserName('forge__tracker_importer_user')->build()
        );

        $future_id = substr((string) $xml->xpath('/project/trackers/tracker/formElements//formElement[name="status"]/bind/items/item[@label="future"]')[0]['ID'], 1);
        $active_id = substr((string) $xml->xpath('/project/trackers/tracker/formElements//formElement[name="status"]/bind/items/item[@label="active"]')[0]['ID'], 1);
        $closed_id = substr((string) $xml->xpath('/project/trackers/tracker/formElements//formElement[name="status"]/bind/items/item[@label="closed"]')[0]['ID'], 1);

        $sprints = $xml->xpath('/project/trackers/tracker/artifacts/artifact/changeset/field_change[@field_name="name"]');

        assertEquals('Sprint 1', $sprints[0]->value);
        $status_value = $sprints[0]->xpath('../field_change[@field_name="status"]')[0];
        assertEquals('list', $status_value['type']);
        assertEquals('static', $status_value['bind']);
        assertEquals($closed_id, (string) $status_value->value[0]);

        assertEquals('Sprint 2', $sprints[1]->value);
        $status_value = $sprints[1]->xpath('../field_change[@field_name="status"]')[0];
        assertEquals('list', $status_value['type']);
        assertEquals('static', $status_value['bind']);
        assertEquals($active_id, (string) $status_value->value[0]);

        assertEquals('Sprint 3', $sprints[2]->value);
        $status_value = $sprints[2]->xpath('../field_change[@field_name="status"]')[0];
        assertEquals('list', $status_value['type']);
        assertEquals('static', $status_value['bind']);
        assertEquals($future_id, (string) $status_value->value[0]);
    }

    private function getJiraAgileImport(): JiraAgileImporter
    {
        return new JiraAgileImporter(
            $this->getJiraBoradRetrieverWithOneBoard(),
            $this->getJiraSprintRetrieverWithoutSprints(),
        );
    }

    private function getJiraBoradRetrieverWithOneBoard(): JiraBoardsRetriever
    {
        return new class implements JiraBoardsRetriever
        {
            public function getFirstScrumBoardForProject(string $jira_project_key): ?JiraBoard
            {
                return new JiraBoard(1, 'https://example.com', 10000, 'FOO');
            }
        };
    }

    private function getJiraSprintRetrieverWithoutSprints(): JiraSprintRetriever
    {
        return $this->getJiraSprintRetrieverWithSprints([]);
    }

    private function getJiraSprintRetrieverWithSprints(array $sprints): JiraSprintRetriever
    {
        return new class ($sprints) implements JiraSprintRetriever
        {
            /**
             * @var array
             */
            private $sprints;

            public function __construct(array $sprints)
            {
                $this->sprints = $sprints;
            }

            public function getAllSprints(JiraBoard $board): array
            {
                return $this->sprints;
            }
        };
    }
}
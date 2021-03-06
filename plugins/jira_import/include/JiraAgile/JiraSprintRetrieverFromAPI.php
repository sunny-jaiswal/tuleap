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

use Psr\Log\LoggerInterface;
use Tuleap\Tracker\Creation\JiraImporter\JiraClient;
use Tuleap\Tracker\Creation\JiraImporter\JiraCollectionBuilder;

final class JiraSprintRetrieverFromAPI implements JiraSprintRetriever
{
    /**
     * @var JiraClient
     */
    private $client;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(JiraClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @return JiraSprint[]
     * @throws \JsonException
     * @throws \Tuleap\Tracker\Creation\JiraImporter\JiraConnectionException
     */
    public function getAllSprints(JiraBoard $board): array
    {
        $sprints  = [];
        $iterator = JiraCollectionBuilder::iterateUntilIsLast(
            $this->client,
            $this->logger,
            $this->getSprintUrl($board),
            'values',
        );
        foreach ($iterator as $sprint) {
            $sprints[] = JiraSprint::buildFromAPI($sprint);
        }
        return $sprints;
    }

    private function getSprintUrl(JiraBoard $board): string
    {
        return parse_url($board->url, PHP_URL_PATH) . '/sprint';
    }
}

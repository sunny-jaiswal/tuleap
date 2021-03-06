<?php
/**
 * Copyright (c) Enalean SAS, 2016 - Present. All Rights Reserved.
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

namespace Tuleap\ReferenceAliasGit;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tuleap\Project\XML\Import\ImportConfig;

include __DIR__ . '/bootstrap.php';

class ReferencesImporterTest extends \Tuleap\Test\PHPUnit\TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|Dao
     */
    private $dao;

    /**
     * @var \Logger|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $logger;

    /**
     * @var ReferencesImporter
     */
    private $importer;

    /**
     * @var \GitRepository|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dao        = \Mockery::spy(\Tuleap\ReferenceAliasGit\Dao::class);
        $this->logger     = \Mockery::spy(\Psr\Log\LoggerInterface::class);
        $this->importer   = new ReferencesImporter($this->dao, $this->logger);
        $this->repository = \Mockery::spy(\GitRepository::class);

        $this->repository->shouldReceive('getId')->andReturns(123);
        $this->repository->shouldReceive('getProjectId')->andReturns(101);
    }

    public function testItShouldAddGitLinks(): void
    {
        $xml        = <<<XML
            <references>
                <reference source="cmmt12" target="le_sha1"/>
            </references>
XML;
        $simple_xml = new \SimpleXMLElement($xml);

        $this->dao->shouldReceive('getRef')->andReturns([]);

        $this->dao->shouldReceive('insertRef')->with('cmmt12', 123, 'le_sha1')->once();

        $this->importer->importCompatRefXML(new ImportConfig(), \Mockery::spy(\Project::class), $simple_xml, $this->repository);
    }

    public function testItShouldNotAddUnknownReferences(): void
    {
        $xml        = <<<XML
            <references>
                <reference source="stuff1234" target="whatever"/>
            </references>
XML;
        $simple_xml = new \SimpleXMLElement($xml);

        $this->dao->shouldReceive('getRef')->andReturns(\TestHelper::arrayToDar([]));

        $this->dao->shouldReceive('insertRef')->never();

        $this->importer->importCompatRefXML(new ImportConfig(), \Mockery::spy(\Project::class), $simple_xml, $this->repository);
    }
}

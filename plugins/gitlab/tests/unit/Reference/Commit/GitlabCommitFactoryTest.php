<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

declare(strict_types=1);

namespace Tuleap\Gitlab\Reference\Commit;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tuleap\Gitlab\Repository\GitlabRepository;
use Tuleap\Gitlab\Repository\Webhook\PostPush\Commits\CommitTuleapReferenceDao;

class GitlabCommitFactoryTest extends \Tuleap\Test\PHPUnit\TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|CommitTuleapReferenceDao
     */
    private $commit_dao;
    /**
     * @var GitlabCommitFactory
     */
    private $factory;

    protected function setUp(): void
    {
        $this->commit_dao = \Mockery::mock(CommitTuleapReferenceDao::class);
        $this->factory    = new GitlabCommitFactory(
            $this->commit_dao
        );
    }

    public function testItReturnsTheGitlabCommitData(): void
    {
        $this->commit_dao->shouldReceive('searchCommitInRepositoryWithSha1')
            ->with(2, '11645a413d7af2995cd92e40bf387e39d06d0e61')
            ->andReturn([
                'gitlab_repository_id' => 2,
                'commit_sha1' => '11645a413d7af2995cd92e40bf387e39d06d0e61',
                'commit_date' => 1608555618,
                'commit_branch' => "master",
                'commit_title' => 'TULEAP-1234 Improve the README',
                'author_name' => 'John Snow',
                'author_email' => 'john-snow@the-wall.com',
            ]);

        $repository  = $this->getMockedGitlabRepository();
        $commit_info = $this->factory->getGitlabCommitInRepositoryWithSha1(
            $repository,
            '11645a413d7af2995cd92e40bf387e39d06d0e61'
        );

        $this->assertNotNull($commit_info);
        $this->assertEquals('11645a413d7af2995cd92e40bf387e39d06d0e61', $commit_info->getCommitSha1());
        $this->assertEquals(1608555618, $commit_info->getCommitDate());
        $this->assertEquals('TULEAP-1234 Improve the README', $commit_info->getCommitTitle());
        $this->assertEquals('master', $commit_info->getCommitBranchName());
        $this->assertEquals('John Snow', $commit_info->getCommitAuthorName());
        $this->assertEquals('john-snow@the-wall.com', $commit_info->getCommitAuthorEmail());
    }

    public function testItReturnsNullWhenThereIsNoMatchingCommit(): void
    {
        $this->commit_dao->shouldReceive('searchCommitInRepositoryWithSha1')
            ->with(2, '11645a413d7af2995cd92e40bf387e39d06d0e61')
            ->andReturn(null);

        $repository  = $this->getMockedGitlabRepository();
        $commit_info = $this->factory->getGitlabCommitInRepositoryWithSha1(
            $repository,
            '11645a413d7af2995cd92e40bf387e39d06d0e61'
        );

        $this->assertNull($commit_info);
    }

    /**
     * @return \Mockery\LegacyMockInterface|\Mockery\MockInterface|GitlabRepository
     */
    private function getMockedGitlabRepository()
    {
        $repository = \Mockery::mock(GitlabRepository::class);
        $repository->shouldReceive('getId')->andReturn(2);
        return $repository;
    }
}

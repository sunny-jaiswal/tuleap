<?php
/**
 * Copyright (c) Enalean, 2020-Present. All Rights Reserved.
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

namespace Tuleap\Gitlab\Repository\Webhook\PostPush\Commits;

use Tuleap\DB\DataAccessObject;

class CommitTuleapReferenceDao extends DataAccessObject
{
    public function saveGitlabCommitInfo(
        int $repository_id,
        string $commit_sha1,
        int $commit_date,
        string $commit_title,
        string $commit_branch_name,
        string $commit_author_name,
        string $commit_author_email
    ): void {
        $sql = '
            INSERT INTO plugin_gitlab_commit_info
                (
                     repository_id,
                     commit_sha1,
                     commit_date,
                     commit_title,
                     commit_branch,
                     author_name,
                     author_email
                 )
            VALUES (?, UNHEX(?), ?, ?, ?, ?, ?)
        ';

        $this->getDB()->run(
            $sql,
            $repository_id,
            $commit_sha1,
            $commit_date,
            $commit_title,
            $commit_branch_name,
            $commit_author_name,
            $commit_author_email
        );
    }

    /**
     * @psalm-return array{commit_sha1: string, commit_date: int, commit_title: string, author_name: string, author_email: string}
     */
    public function searchCommitInRepositoryWithSha1(int $repository_id, string $commit_sha1): ?array
    {
        $sql = "
            SELECT LOWER(HEX(commit_sha1)) as commit_sha1,
                   commit_date,
                   commit_title,
                   commit_branch,
                   author_name,
                   author_email
            FROM plugin_gitlab_commit_info
            WHERE repository_id = ?
                AND commit_sha1 = UNHEX(?)
        ";

        return $this->getDB()->row($sql, $repository_id, $commit_sha1);
    }

    public function deleteCommitsInGitlabRepository(int $repository_id): void
    {
        $this->getDB()->delete(
            'plugin_gitlab_commit_info',
            [
                'repository_id' => $repository_id,
            ]
        );
    }
}

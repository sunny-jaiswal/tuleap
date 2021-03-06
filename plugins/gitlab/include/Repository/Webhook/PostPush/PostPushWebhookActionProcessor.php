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

namespace Tuleap\Gitlab\Repository\Webhook\PostPush;

use CrossReference;
use Project;
use Psr\Log\LoggerInterface;
use ReferenceManager;
use Tuleap\Gitlab\Reference\Commit\GitlabCommitReference;
use Tuleap\Gitlab\Reference\TuleapReferencedArtifactNotFoundException;
use Tuleap\Gitlab\Reference\TuleapReferenceNotFoundException;
use Tuleap\Gitlab\Reference\TuleapReferenceRetriever;
use Tuleap\Gitlab\Repository\GitlabRepository;
use Tuleap\Gitlab\Repository\Project\GitlabRepositoryProjectRetriever;
use Tuleap\Gitlab\Repository\Webhook\PostPush\Commits\CommitTuleapReferenceDao;
use Tuleap\Gitlab\Repository\Webhook\WebhookTuleapReference;
use Tuleap\Gitlab\Repository\Webhook\WebhookTuleapReferencesParser;
use UserNotExistException;

class PostPushWebhookActionProcessor
{
    /**
     * @var WebhookTuleapReferencesParser
     */
    private $commit_tuleap_references_parser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GitlabRepositoryProjectRetriever
     */
    private $gitlab_repository_project_retriever;

    /**
     * @var ReferenceManager
     */
    private $reference_manager;

    /**
     * @var CommitTuleapReferenceDao
     */
    private $commit_tuleap_reference_dao;

    /**
     * @var TuleapReferenceRetriever
     */
    private $tuleap_reference_retriever;
    /**
     * @var PostPushCommitBotCommenter
     */
    private $commenter;
    /**
     * @var PostPushWebhookCloseArtifactHandler
     */
    private $close_artifact_handler;

    public function __construct(
        WebhookTuleapReferencesParser $commit_tuleap_references_parser,
        GitlabRepositoryProjectRetriever $gitlab_repository_project_retriever,
        CommitTuleapReferenceDao $commit_tuleap_reference_dao,
        ReferenceManager $reference_manager,
        TuleapReferenceRetriever $tuleap_reference_retriever,
        LoggerInterface $logger,
        PostPushCommitBotCommenter $commenter,
        PostPushWebhookCloseArtifactHandler $close_artifact_handler
    ) {
        $this->commit_tuleap_references_parser     = $commit_tuleap_references_parser;
        $this->gitlab_repository_project_retriever = $gitlab_repository_project_retriever;
        $this->commit_tuleap_reference_dao         = $commit_tuleap_reference_dao;
        $this->reference_manager                   = $reference_manager;
        $this->tuleap_reference_retriever          = $tuleap_reference_retriever;
        $this->logger                              = $logger;
        $this->commenter                           = $commenter;
        $this->close_artifact_handler              = $close_artifact_handler;
    }

    public function process(GitlabRepository $gitlab_repository, PostPushWebhookData $webhook_data): void
    {
        foreach ($webhook_data->getCommits() as $commit_webhook_data) {
            $this->parseCommitReferences($gitlab_repository, $commit_webhook_data);
        }
    }

    private function parseCommitReferences(
        GitlabRepository $gitlab_repository,
        PostPushCommitWebhookData $commit_webhook_data
    ): void {
        $references_collection = $this->commit_tuleap_references_parser->extractCollectionOfTuleapReferences(
            $commit_webhook_data->getMessage()
        );

        $projects = $this->gitlab_repository_project_retriever->getProjectsGitlabRepositoryIsIntegratedIn(
            $gitlab_repository
        );

        $good_references = [];

        $this->logger->info(count($references_collection->getTuleapReferences()) . " Tuleap references found in commit " . $commit_webhook_data->getSha1());

        foreach ($references_collection->getTuleapReferences() as $tuleap_reference) {
            $this->logger->info("|_ Reference to Tuleap artifact #" . $tuleap_reference->getId() . " found.");

            try {
                $external_reference = $this->tuleap_reference_retriever->retrieveTuleapReference($tuleap_reference->getId());

                assert($external_reference instanceof \Reference);

                $this->logger->info(
                    "|  |_ Tuleap artifact #" . $tuleap_reference->getId() . " found, cross-reference will be added for each project the GitLab repository is integrated in."
                );

                $this->saveReferenceInEachIntegratedProject(
                    $gitlab_repository,
                    $tuleap_reference,
                    $commit_webhook_data,
                    $external_reference,
                    $projects
                );

                $this->close_artifact_handler->handleArtifactClosure(
                    $tuleap_reference,
                    $commit_webhook_data,
                    $gitlab_repository
                );

                $good_references[] = $tuleap_reference;
            } catch (TuleapReferencedArtifactNotFoundException | TuleapReferenceNotFoundException | UserNotExistException $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        if (! empty($good_references)) {
            // Save commit data if there is at least 1 good artifact reference in the commit message
            $this->saveCommitData($gitlab_repository, $commit_webhook_data);
            $this->commenter->addCommentOnCommit($commit_webhook_data, $gitlab_repository, $good_references);
        }
    }

    /**
     * @param Project[] $projects
     */
    private function saveReferenceInEachIntegratedProject(
        GitlabRepository $gitlab_repository,
        WebhookTuleapReference $tuleap_reference,
        PostPushCommitWebhookData $commit_webhook_data,
        \Reference $external_reference,
        array $projects
    ): void {
        foreach ($projects as $project) {
            $cross_reference = new CrossReference(
                $gitlab_repository->getName() . '/' . $commit_webhook_data->getSha1(),
                $project->getID(),
                GitlabCommitReference::NATURE_NAME,
                GitlabCommitReference::REFERENCE_NAME,
                $tuleap_reference->getId(),
                $external_reference->getGroupId(),
                $external_reference->getNature(),
                $external_reference->getKeyword(),
                0
            );

            $this->reference_manager->insertCrossReference($cross_reference);
        }
    }

    private function saveCommitData(GitlabRepository $gitlab_repository, PostPushCommitWebhookData $commit_webhook_data): void
    {
        $commit_sha1 = $commit_webhook_data->getSha1();
        $this->commit_tuleap_reference_dao->saveGitlabCommitInfo(
            $gitlab_repository->getId(),
            $commit_sha1,
            $commit_webhook_data->getCommitDate(),
            $commit_webhook_data->getTitle(),
            $commit_webhook_data->getBranchName(),
            $commit_webhook_data->getAuthorName(),
            $commit_webhook_data->getAuthorEmail()
        );
        $this->logger->info("Commit data for $commit_sha1 saved in database");
    }
}

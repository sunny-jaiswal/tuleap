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
 * along with Tuleap. If not, see http://www.gnu.org/licenses/.
 */

namespace Tuleap\Gitlab\Repository\Webhook\PostPush;

use PHPUnit\Framework\TestCase;
use Mockery;
use Tuleap\Gitlab\Repository\Token\GitlabBotApiTokenRetriever;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tuleap\Gitlab\Repository\GitlabRepository;
use Tuleap\Cryptography\ConcealedString;

class PostPushCommitCredentialsRetrieverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var PostPushCommitCredentialsRetriever
     */
    private $credentials_retriever;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|GitlabBotApiTokenRetriever
     */
    private $token_retriever;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token_retriever = Mockery::mock(GitlabBotApiTokenRetriever::class);

        $this->credentials_retriever = new PostPushCommitCredentialsRetriever(
            $this->token_retriever
        );
    }

    public function testReturnsCredentialsWithTokenAndURL(): void
    {
        $gitlab_repository = Mockery::mock(GitlabRepository::class);
        $gitlab_repository
            ->shouldReceive('getName')
            ->andReturn("MyProject/MyRepo")
            ->once();
        $gitlab_repository
            ->shouldReceive('getFullUrl')
            ->andReturn("https://www.example.com/MyProject/MyRepo")
            ->once();

        $token = new ConcealedString("My_Token123");

        $this->token_retriever->shouldReceive("getBotAPIToken")->with($gitlab_repository)->andReturn($token);

        $credentials = $this->credentials_retriever->getCredentials($gitlab_repository);

        $this->assertEquals($token, $credentials->getBotApiToken());
        $this->assertEquals("https://www.example.com/", $credentials->getGitlabServerUrl());
    }

    public function testReturnsNullIfNoSavedToken(): void
    {
        $gitlab_repository = Mockery::mock(GitlabRepository::class);
        $gitlab_repository
            ->shouldReceive('getName')
            ->never();
        $gitlab_repository
            ->shouldReceive('getFullUrl')
            ->never();

        $this->token_retriever->shouldReceive("getBotAPIToken")->with($gitlab_repository)->andReturnNull();

        $credentials = $this->credentials_retriever->getCredentials($gitlab_repository);

        $this->assertEquals(null, $credentials);
    }
}
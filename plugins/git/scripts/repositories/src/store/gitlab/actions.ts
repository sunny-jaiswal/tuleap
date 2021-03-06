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

import { getProjectId } from "../../repository-list-presenter";
import type {
    GitLabRepositoryCreation,
    GitLabRepositoryUpdate,
} from "../../gitlab/gitlab-api-querier";
import {
    getAsyncGitlabRepositoryList as getAsyncGitlabRepository,
    getGitlabRepositoryList as getGitlabRepository,
    patchGitlabRepository,
    postGitlabRepository,
} from "../../gitlab/gitlab-api-querier";
import { getErrorCode } from "../../support/error-handler";
import type { GitlabState } from "./state";
import type { ActionContext } from "vuex";
import type {
    GitLabCredentials,
    GitLabData,
    GitLabDataWithToken,
    GitLabRepository,
    Repository,
} from "../../type";
import {
    formatUrlToGetAllProject,
    formatUrlToGetProjectFromId,
} from "../../gitlab/gitlab-credentials-helper";

export type GitlabRepositoryCallback = (repositories: Repository[]) => void;

export const showAddGitlabRepositoryModal = (
    context: ActionContext<GitlabState, GitlabState>
): void => {
    if (!context.state.add_gitlab_repository_modal) {
        return;
    }
    context.state.add_gitlab_repository_modal.toggle();
};

export const showDeleteGitlabRepositoryModal = (
    context: ActionContext<GitlabState, GitlabState>,
    repository: GitLabRepository
): void => {
    context.commit("setUnlinkGitlabRepository", repository);
    if (!context.state.unlink_gitlab_repository_modal) {
        return;
    }
    context.state.unlink_gitlab_repository_modal.toggle();
};

export const showEditAccessTokenGitlabRepositoryModal = (
    context: ActionContext<GitlabState, GitlabState>,
    repository: GitLabRepository
): void => {
    context.commit("setEditAccessTokenGitlabRepository", repository);
    if (!context.state.edit_access_token_gitlab_repository_modal) {
        return;
    }
    context.state.edit_access_token_gitlab_repository_modal.toggle();
};

export const showRegenerateGitlabWebhookModal = (
    context: ActionContext<GitlabState, GitlabState>,
    repository: GitLabRepository
): void => {
    context.commit("setRegenerateGitlabWebhookRepository", repository);
    if (!context.state.regenerate_gitlab_webhook_modal) {
        return;
    }
    context.state.regenerate_gitlab_webhook_modal.toggle();
};

export async function getGitlabRepositories(
    context: ActionContext<GitlabState, GitlabState>,
    order_by: string
): Promise<Array<Repository>> {
    const getGitlabRepositories = (
        callback: GitlabRepositoryCallback
    ): Promise<Array<Repository>> => getGitlabRepository(getProjectId(), order_by, callback);

    context.commit("setIsLoadingInitial", true, { root: true });
    context.commit("setIsLoadingNext", true, { root: true });
    try {
        return await getGitlabRepositories((repositories) => {
            context.commit("pushGitlabRepositoriesForCurrentOwner", repositories, { root: true });
            context.commit("setIsLoadingInitial", false, { root: true });
        });
    } catch (e) {
        context.commit("setErrorMessageType", getErrorCode(e));
        throw e;
    } finally {
        context.commit("setIsLoadingNext", false, { root: true });
        context.commit("setIsFirstLoadDone", true, { root: true });
    }
}

export async function getGitlabProjectList(
    context: ActionContext<GitlabState, GitlabState>,
    credentials: GitLabCredentials
): Promise<Array<GitLabRepository>> {
    let pagination = 1;
    const repositories_gitlab: Array<GitLabRepository> = [];
    credentials.server_url = formatUrlToGetAllProject(credentials.server_url);
    const server_url_without_pagination = credentials.server_url;

    const response = await getAsyncGitlabRepository(credentials);

    if (response.status !== 200) {
        throw Error();
    }
    const total_pages = response.headers.get("X-Total-Pages");
    if (!total_pages) {
        throw Error("Missing header X-Total-Pages");
    }
    const total_page = parseInt(total_pages, 10);
    repositories_gitlab.push(...(await response.json()));

    pagination++;

    while (pagination <= total_page) {
        const repositories = await queryAPIGitlab(
            credentials,
            server_url_without_pagination,
            pagination
        );
        repositories_gitlab.push(...repositories);
        pagination++;
    }

    return repositories_gitlab;
}

interface GitLabRepositoryPayload {
    credentials: GitLabCredentials;
    id: number;
}

export async function getGitlabRepositoryFromId(
    context: ActionContext<GitlabState, GitlabState>,
    payload: GitLabRepositoryPayload
): Promise<Response> {
    payload.credentials.server_url = formatUrlToGetProjectFromId(
        payload.credentials.server_url,
        payload.id
    );

    const response = await getAsyncGitlabRepository(payload.credentials);

    if (response.status !== 200) {
        throw Error();
    }

    return response.json();
}

async function queryAPIGitlab(
    credentials: GitLabCredentials,
    server_url_without_pagination: string,
    pagination: number
): Promise<Array<GitLabRepository>> {
    credentials.server_url = server_url_without_pagination + "&page=" + pagination;

    const response = await getAsyncGitlabRepository(credentials);
    if (response.status !== 200) {
        throw Error();
    }

    return response.json();
}

export async function postIntegrationGitlab(
    context: ActionContext<GitlabState, GitlabState>,
    data: GitLabRepositoryCreation
): Promise<Response> {
    const response = await postGitlabRepository(data);

    return response.json();
}

export async function updateBotApiTokenGitlab(
    context: ActionContext<GitlabState, GitlabState>,
    token: GitLabDataWithToken
): Promise<void> {
    const body: GitLabRepositoryUpdate = {
        update_bot_api_token: { ...token },
    };

    await patchGitlabRepository(body);
}

export async function regenerateGitlabWebhook(
    context: ActionContext<GitlabState, GitlabState>,
    gitlab_data: GitLabData
): Promise<void> {
    const body: GitLabRepositoryUpdate = {
        generate_new_secret: {
            ...gitlab_data,
        },
    };

    await patchGitlabRepository(body);
}

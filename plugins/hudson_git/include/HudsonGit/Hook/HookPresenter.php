<?php
/**
 * Copyright (c) Enalean, 2016. All Rights Reserved.
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

namespace Tuleap\HudsonGit\Hook;

use GitRepository;
use CSRFSynchronizerToken;

class HookPresenter
{
    public $jobs;
    public $jenkins_server_url;
    public $has_hooks;
    public $project_id;
    public $repository_id;
    public $save_label;
    public $jenkins_notification_label;
    public $jenkins_notification_desc;
    public $jenkins_documentation_link_label;
    public $label_push_date;
    public $label_triggered;
    public $empty_jobs;
    public $empty_hooks;
    public $jenkins_hook;
    public $only_one;
    public $hooks_desc;
    public $modal_create_jenkins;
    public $modal_edit_jenkins;
    public $remove;
    public $remove_jenkins_desc;
    public $remove_jenkins_confirm;
    public $remove_jenkins_cancel;
    public $csrf_token;

    public function __construct(
        GitRepository $repository,
        $jenkins_server_url,
        array $jobs,
        CSRFSynchronizerToken $csrf
    ) {
        $this->jenkins_server_url = $jenkins_server_url;
        $this->jobs               = $jobs;
        $this->csrf_token         = $csrf->getToken();

        $this->has_a_jenkins_hook = $this->jenkins_server_url;
        $this->has_hooks          = $this->jenkins_server_url;

        $this->project_id    = $repository->getProjectId();
        $this->repository_id = $repository->getId();

        $this->btn_cancel             = $GLOBALS['Language']->getText('global', 'btn_cancel');
        $this->edit_hook              = $GLOBALS['Language']->getText('global', 'btn_edit');
        $this->save_label             = $GLOBALS['Language']->getText('plugin_git', 'admin_save_submit');
        $this->label_push_date        = $GLOBALS['Language']->getText('plugin_hudson_git', 'label_push_date');
        $this->label_triggered        = $GLOBALS['Language']->getText('plugin_hudson_git', 'label_triggered');
        $this->empty_jobs             = $GLOBALS['Language']->getText('plugin_hudson_git', 'empty_jobs');
        $this->empty_hooks            = $GLOBALS['Language']->getText('plugin_hudson_git', 'empty_hooks');
        $this->jenkins_hook           = $GLOBALS['Language']->getText('plugin_hudson_git', 'jenkins_hook');
        $this->only_one               = $GLOBALS['Language']->getText('plugin_hudson_git', 'only_one');
        $this->hooks_desc             = $GLOBALS['Language']->getText('plugin_hudson_git', 'hooks_desc');
        $this->remove                 = $GLOBALS['Language']->getText('plugin_hudson_git', 'remove');
        $this->remove_jenkins_desc    = $GLOBALS['Language']->getText('plugin_hudson_git', 'remove_jenkins_desc');
        $this->remove_jenkins_confirm = $GLOBALS['Language']->getText('plugin_hudson_git', 'remove_jenkins_confirm');
        $this->remove_jenkins_cancel  = $GLOBALS['Language']->getText('plugin_hudson_git', 'remove_jenkins_cancel');

        $this->add_jenkins_hook     = $GLOBALS['Language']->getText('plugin_hudson_git', 'add_jenkins_hook');
        $this->modal_create_jenkins = new ModalCreatePresenter();
        $this->modal_edit_jenkins   = new ModalEditPresenter();

        $this->jenkins_notification_label       = $GLOBALS['Language']->getText('plugin_hudson_git', 'settings_hooks_jenkins_notification_label');
        $this->jenkins_notification_desc        = $GLOBALS['Language']->getText('plugin_hudson_git', 'settings_hooks_jenkins_notification_desc');
        $this->jenkins_documentation_link_label = $GLOBALS['Language']->getText('plugin_hudson_git', 'settings_hooks_jenkins_link_label');
    }
}

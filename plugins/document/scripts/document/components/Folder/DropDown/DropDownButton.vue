<!--
  - Copyright (c) Enalean, 2019-Present. All Rights Reserved.
  -
  - This file is a part of Tuleap.
  -
  - Tuleap is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 2 of the License, or
  - (at your option) any later version.
  -
  - Tuleap is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Tuleap. If not, see http://www.gnu.org/licenses/.
  -
  -->

<template>
    <div class="tlp-dropdown document-dropdown-menu-button">
        <button
            class="tlp-button-primary"
            v-bind:class="{
                'tlp-button-large': isInLargeMode,
                'tlp-button-small tlp-button-outline': isInQuickLookMode,
                'tlp-append tlp-dropdown-split-button-caret': isAppended,
            }"
            ref="dropdownButton"
            type="button"
            data-test="document-drop-down-button"
            v-bind:aria-label="$gettext(`Open dropdown menu`)"
        >
            <i class="fa fa-ellipsis-h" v-if="!isAppended"></i>
            <i class="fa fa-caret-down" v-bind:class="{ 'tlp-button-icon-right': !isAppended }"></i>
        </button>
        <slot></slot>
    </div>
</template>

<script>
import { createDropdown, EVENT_TLP_DROPDOWN_SHOWN, EVENT_TLP_DROPDOWN_HIDDEN } from "tlp";
import { EVENT_TLP_MODAL_SHOWN } from "../../../../../../../src/themes/tlp/src/js/modal";
import EventBus from "../../../helpers/event-bus.js";

export default {
    name: "DropDownButton",
    props: {
        isInLargeMode: Boolean,
        isInQuickLookMode: Boolean,
        isAppended: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return { dropdown: null };
    },
    mounted() {
        this.dropdown = createDropdown(this.$refs.dropdownButton);

        this.dropdown.addEventListener(EVENT_TLP_DROPDOWN_SHOWN, this.showDropdownEvent);
        this.dropdown.addEventListener(EVENT_TLP_DROPDOWN_HIDDEN, this.hideDropdownEvent);
        document.addEventListener(EVENT_TLP_MODAL_SHOWN, this.hideActionMenu);

        EventBus.$on("hide-action-menu", this.hideActionMenu);
    },
    beforeDestroy() {
        this.dropdown.removeEventListener(EVENT_TLP_DROPDOWN_SHOWN);
        this.dropdown.removeEventListener(EVENT_TLP_DROPDOWN_HIDDEN);
        document.removeEventListener(EVENT_TLP_MODAL_SHOWN, this.hideActionMenu);

        EventBus.$off("hide-action-menu", this.hideActionMenu);
    },
    methods: {
        hideActionMenu() {
            if (this.dropdown && this.dropdown.is_shown) {
                this.dropdown.hide();
            }
        },
        showDropdownEvent() {
            EventBus.$emit("set-dropdown-shown", { is_dropdown_shown: true });
        },
        hideDropdownEvent() {
            EventBus.$emit("set-dropdown-shown", { is_dropdown_shown: false });
        },
    },
};
</script>

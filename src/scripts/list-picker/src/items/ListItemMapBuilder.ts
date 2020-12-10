/*
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
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

import { ListPickerItem, ListPickerItemMap, ListPickerOptions } from "../type";
import { html, render, TemplateResult } from "lit-html";

export class ListItemMapBuilder {
    private items_templates_cache: Map<string, TemplateResult>;

    constructor(
        private readonly source_select_box: HTMLSelectElement,
        private readonly options?: ListPickerOptions
    ) {
        this.items_templates_cache = new Map();
    }

    public async buildListPickerItemsMap(): Promise<ListPickerItemMap> {
        const map = new Map();
        const useless_options = [];

        for (const option of this.source_select_box.options) {
            if (option.value === "" || option.value === "?") {
                useless_options.push(option);
                continue;
            }

            let group_id = "";
            if (option.parentElement && option.parentElement.nodeName === "OPTGROUP") {
                const label = option.parentElement.getAttribute("label");

                if (label !== null) {
                    group_id = label.replace(" ", "").toLowerCase();
                }
            }

            const id = this.getItemId(option, group_id);
            const template = await this.getTemplateForItem(option, id);
            const is_disabled = Boolean(option.hasAttribute("disabled"));
            const item: ListPickerItem = {
                id,
                group_id,
                value: option.value,
                template,
                label: this.getOptionsLabel(option),
                is_disabled,
                is_selected: false,
                target_option: option,
                element: this.getRenderedListItem(id, template, is_disabled),
            };
            map.set(id, item);
            option.setAttribute("data-item-id", id);
        }

        useless_options.forEach((option) => this.source_select_box.removeChild(option));
        return map;
    }

    private getRenderedListItem(
        option_id: string,
        template: TemplateResult,
        is_disabled: boolean
    ): Element {
        let class_name = "list-picker-dropdown-option-value";
        if (is_disabled) {
            class_name = "list-picker-dropdown-option-value-disabled";
        }

        const document_fragment = document.createDocumentFragment();
        render(
            html`
                <li
                    role="option"
                    aria-selected="false"
                    data-item-id="${option_id}"
                    class="${class_name}"
                >
                    ${template}
                </li>
            `,
            document_fragment
        );

        const list_item = document_fragment.firstElementChild;
        if (list_item !== null) {
            return list_item;
        }

        throw new Error("Cannot render the list item");
    }

    private getItemId(option: HTMLOptionElement, group_id: string): string {
        let base_id = "list-picker-item-";
        let option_value = option.value.toLowerCase().trim();

        if (option_value === "100" || option_value === "number:100") {
            return base_id + "100";
        }

        if (group_id !== "") {
            base_id += group_id + "-";
        }

        if (option_value.includes(" ")) {
            option_value = option_value.split(" ").join("-");
        }

        return base_id + option_value;
    }

    private async getTemplateForItem(
        option: HTMLOptionElement,
        item_id: string
    ): Promise<TemplateResult> {
        const template = this.items_templates_cache.get(item_id);
        if (template) {
            return template;
        }

        const option_label = this.getOptionsLabel(option);
        const avatar_url = option.dataset.avatarUrl;
        if (avatar_url && avatar_url !== "") {
            return html`
                <span class="list-picker-avatar"><img src="${avatar_url}" loading="lazy" /></span>
                ${option_label}
            `;
        }
        if (this.options && this.options.items_template_formatter) {
            const custom_template = await this.options.items_template_formatter(
                option.value,
                option_label
            );
            this.items_templates_cache.set(item_id, custom_template);

            return custom_template;
        }

        return ListItemMapBuilder.buildDefaultTemplateForItem(option_label);
    }

    public static buildDefaultTemplateForItem(value: string): TemplateResult {
        return html`
            ${value}
        `;
    }

    private getOptionsLabel(option: HTMLOptionElement): string {
        return option.innerText !== "" && option.innerText !== undefined
            ? option.innerText
            : option.label;
    }
}

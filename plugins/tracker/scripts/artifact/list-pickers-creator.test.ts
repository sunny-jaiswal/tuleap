/**
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

import type { ListPickerCreator } from "./list-pickers-creator";
import {
    initListPickersInArtifactCreationView,
    initListPickersPostUpdateErrorView,
    listenToggleEditionEvents,
} from "./list-pickers-creator";

describe("list-pickers-creator", () => {
    let doc: HTMLDocument;
    let createListPicker: ListPickerCreator;

    function createArtifactFormElementFieldInReadModeOfType(
        type: string
    ): {
        button: Element;
        select: HTMLSelectElement;
    } {
        const field = document.createElement("div");
        field.setAttribute("class", `tracker_artifact_field-${type}`);

        const button = document.createElement("button");
        button.setAttribute("class", "tracker_formelement_edit");

        const hidden_edition_field = document.createElement("div");
        hidden_edition_field.setAttribute("class", "tracker_hidden_edition_field");

        const select = document.createElement("select");

        if (type === "msb") {
            select.setAttribute("multiple", "multiple");
        }

        hidden_edition_field.appendChild(select);
        field.appendChild(button);
        field.appendChild(hidden_edition_field);
        doc.body.appendChild(field);

        return {
            button,
            select,
        };
    }

    function createArtifactFormElementFieldInEditionModeOfType(
        type: string,
        is_in_edition_mode = false
    ): HTMLSelectElement {
        const field = document.createElement("div");
        field.setAttribute("class", `tracker_artifact_field-${type}`);

        if (is_in_edition_mode) {
            field.classList.add("in-edition");
        }

        const select = document.createElement("select");

        if (type === "msb") {
            select.setAttribute("multiple", "multiple");
        }

        field.appendChild(select);
        doc.body.appendChild(field);

        return select;
    }

    beforeEach(() => {
        doc = document.implementation.createHTMLDocument();
        createListPicker = jest.fn();
    });

    it("should listen for clicks on fields labels to create a list picker when the <select> is shown", () => {
        ["sb", "msb"].forEach((type) => {
            const { button, select } = createArtifactFormElementFieldInReadModeOfType(type);

            listenToggleEditionEvents(doc, createListPicker);
            button.dispatchEvent(new Event("click"));

            expect(createListPicker).toHaveBeenCalledWith(select, {
                is_filterable: true,
                none_value: null,
            });
        });
    });

    it("should init list-pickers when the artifact view is in creation mode", () => {
        ["sb", "msb"].forEach((type) => {
            const select = createArtifactFormElementFieldInEditionModeOfType(type);
            initListPickersInArtifactCreationView(doc, createListPicker);

            expect(createListPicker).toHaveBeenCalledWith(select, {
                is_filterable: true,
                none_value: null,
            });
        });
    });

    it("should init list-pickers when list fields are in edition mode", () => {
        ["sb", "msb"].forEach((type) => {
            const select = createArtifactFormElementFieldInEditionModeOfType(type, true);
            initListPickersPostUpdateErrorView(doc, createListPicker);

            expect(createListPicker).toHaveBeenCalledWith(select, {
                is_filterable: true,
                none_value: null,
            });
        });
    });

    it("should init list-pickers with none value when a none option exist", () => {
        ["sb", "msb"].forEach((type) => {
            const select = createArtifactFormElementFieldInEditionModeOfType(type, true);
            const option_none = document.createElement("option");
            option_none.value = "100";
            option_none.innerText = "None";

            select.add(option_none);

            initListPickersPostUpdateErrorView(doc, createListPicker);

            expect(createListPicker).toHaveBeenCalledWith(select, {
                is_filterable: true,
                none_value: "100",
            });
        });
    });

    it("when the field has targets, then it should initialize the target fields recursively", () => {
        const {
            button: button_1,
            select: select_1,
        } = createArtifactFormElementFieldInReadModeOfType("sb");
        const { select: select_2 } = createArtifactFormElementFieldInReadModeOfType("sb");
        const { select: select_3 } = createArtifactFormElementFieldInReadModeOfType("msb");

        select_1.id = "tracker_field_5";
        select_2.id = "tracker_field_10";
        select_3.id = "tracker_field_25";

        select_1.setAttribute("data-target-fields-ids", JSON.stringify(["10"]));
        select_2.setAttribute("data-target-fields-ids", JSON.stringify(["25"]));

        listenToggleEditionEvents(doc, createListPicker);
        button_1.dispatchEvent(new Event("click"));

        expect(createListPicker).toHaveBeenCalledWith(select_1, {
            is_filterable: true,
            none_value: null,
        });
        expect(createListPicker).toHaveBeenCalledWith(select_2, {
            is_filterable: true,
            none_value: null,
        });
        expect(createListPicker).toHaveBeenCalledWith(select_3, {
            is_filterable: true,
            none_value: null,
        });
    });
});

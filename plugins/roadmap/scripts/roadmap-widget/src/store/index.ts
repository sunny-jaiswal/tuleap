/**
 * Copyright (c) Enalean, 2021 - present. All Rights Reserved.
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

import type { StoreOptions } from "vuex";
import { Store } from "vuex";
import type { RootState } from "./type";
import { createTaskModule } from "./tasks";

export function createStore(initial_root_state: RootState): Store<RootState> {
    const store_options: StoreOptions<RootState> = {
        state: initial_root_state,
        modules: {
            tasks: createTaskModule(),
        },
    };

    return new Store(store_options);
}

<!--
  - Copyright (c) Enalean, 2021 - present. All Rights Reserved.
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
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<template>
    <div class="roadmap-gantt-today" v-bind:style="style" v-bind:title="title"></div>
</template>

<script lang="ts">
import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";
import { getLeftForDate } from "../../helpers/left-postion";
import type { TimePeriod } from "../../type";
import { State } from "vuex-class";

@Component
export default class TodayIndicator extends Vue {
    @Prop({ required: true })
    readonly time_period!: TimePeriod;

    @Prop({ required: true })
    readonly now!: Date;

    @State
    readonly locale_bcp47!: string;

    get style(): string {
        const left = getLeftForDate(this.now, this.time_period);
        return `left: ${left}px;`;
    }

    get title(): string {
        const f = new Intl.DateTimeFormat(this.locale_bcp47, {
            day: "numeric",
            month: "long",
            year: "numeric",
        });

        return this.$gettextInterpolate(this.$gettext("Today: %{ date }"), {
            date: f.format(this.now),
        });
    }
}
</script>

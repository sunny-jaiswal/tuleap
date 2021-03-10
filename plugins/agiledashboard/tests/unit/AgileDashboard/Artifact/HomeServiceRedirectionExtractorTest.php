<?php
/**
 * Copyright (c) Enalean 2021 - Present. All Rights Reserved.
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

declare(strict_types=1);

namespace Tuleap\AgileDashboard\Artifact;

use Codendi_Request;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

final class HomeServiceRedirectionExtractorTest extends TestCase
{
    public function testItReturnsTrueIfRequestMustRedirectToADHomepage(): void
    {
        $extractor = new HomeServiceRedirectionExtractor();
        $request   = new Codendi_Request([
            'agiledashboard' => [
                'home' => '1'
            ]
        ]);

        assertTrue(
            $extractor->mustRedirectToAgiledashboardHomepage($request)
        );
    }

    public function testItReturnsFalseIfRequestMustNotRedirectToADHomepage(): void
    {
        $extractor = new HomeServiceRedirectionExtractor();

        $request = new Codendi_Request([]);
        assertFalse(
            $extractor->mustRedirectToAgiledashboardHomepage($request)
        );

        $request = new Codendi_Request([
            'agiledashboard'
        ]);
        assertFalse(
            $extractor->mustRedirectToAgiledashboardHomepage($request)
        );

        $request = new Codendi_Request([
            'agiledashboard' => []
        ]);
        assertFalse(
            $extractor->mustRedirectToAgiledashboardHomepage($request)
        );

        $request = new Codendi_Request([
            'agiledashboard' => [
                'home' => 'whatever'
            ]
        ]);
        assertFalse(
            $extractor->mustRedirectToAgiledashboardHomepage($request)
        );

        $request = new Codendi_Request([
            'agiledashboard' => [
                'whatever' => '1'
            ]
        ]);
        assertFalse(
            $extractor->mustRedirectToAgiledashboardHomepage($request)
        );

        $request = new Codendi_Request([
            'whatever' => [
                'home' => '1'
            ]
        ]);
        assertFalse(
            $extractor->mustRedirectToAgiledashboardHomepage($request)
        );
    }
}

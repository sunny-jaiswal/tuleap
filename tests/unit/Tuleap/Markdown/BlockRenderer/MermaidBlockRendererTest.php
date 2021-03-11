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
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\Markdown\BlockRenderer;

use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Renderer\FencedCodeRenderer;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tuleap\Markdown\CodeBlockFeaturesInterface;

class MermaidBlockRendererTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var CommonMarkConverter
     */
    private $converter;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|CodeBlockFeaturesInterface
     */
    private $code_block_features;

    protected function setUp(): void
    {
        $this->code_block_features = Mockery::mock(CodeBlockFeaturesInterface::class);

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addBlockRenderer(
            FencedCode::class,
            new MermaidBlockRenderer($this->code_block_features, new FencedCodeRenderer())
        );
        $this->converter = new CommonMarkConverter([], $environment);
    }

    public function testItDoesNotConvertFencedCodesThatAreNotMermaid(): void
    {
        $this->code_block_features
            ->shouldReceive('needsMermaid')
            ->never();

        $result = $this->converter->convertToHtml(
            <<<MARKDOWN
            See code below:

            ```php
            class Foo {}
            ```

            ```
            graph TD;
                A-->B;
                A-->C;
                B-->D;
                C-->D;
            ```
            MARKDOWN
        );

        self::assertEquals(
            <<<EXPECTED_HTML
            <p>See code below:</p>
            <pre><code class="language-php">class Foo {}
            </code></pre>
            <pre><code>graph TD;
                A--&gt;B;
                A--&gt;C;
                B--&gt;D;
                C--&gt;D;
            </code></pre>\n
            EXPECTED_HTML,
            $result
        );
    }

    public function testItConvertFencedCodeThatIsFlaggedAsMermaid(): void
    {
        $this->code_block_features
            ->shouldReceive('needsMermaid')
            ->once();

        $result = $this->converter->convertToHtml(
            <<<MARKDOWN
            See code below:

            ```php
            class Foo {}
            ```

            ```mermaid
            graph TD;
                A-->B;
                A-->C;
                B-->D;
                C-->D;
            ```
            MARKDOWN
        );

        self::assertEquals(
            <<<EXPECTED_HTML
            <p>See code below:</p>
            <pre><code class="language-php">class Foo {}
            </code></pre>
            <tlp-mermaid-diagram>graph TD;
                A--&gt;B;
                A--&gt;C;
                B--&gt;D;
                C--&gt;D;
            </tlp-mermaid-diagram>\n
            EXPECTED_HTML,
            $result
        );
    }
}
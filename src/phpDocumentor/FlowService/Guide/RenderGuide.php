<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\FlowService\Guide;

use League\Tactician\CommandBus;
use phpDocumentor\Descriptor\DocumentationSetDescriptor;
use phpDocumentor\Descriptor\GuideSetDescriptor;
use phpDocumentor\Descriptor\ProjectDescriptor;
use phpDocumentor\Descriptor\VersionDescriptor;
use phpDocumentor\Dsn;
use phpDocumentor\FlowService\FlowService;
use phpDocumentor\Guides\RenderCommand;
use phpDocumentor\Guides\Renderer;
use phpDocumentor\Transformer\Transformation;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use function sprintf;

/**
 * @experimental Do not use; this stage is meant as a sandbox / playground to experiment with generating guides.
 */
final class RenderGuide implements FlowService, ProjectDescriptor\WithCustomSettings
{
    public const FEATURE_FLAG = 'guides.enabled';

    /** @var LoggerInterface */
    private $logger;

    /** @var CommandBus */
    private $commandBus;

    /** @var Renderer */
    private $renderer;

    public function __construct(Renderer $renderer, LoggerInterface $logger, CommandBus $commandBus)
    {
        $this->logger = $logger;
        $this->commandBus = $commandBus;
        $this->renderer = $renderer;
    }

    public function operate(DocumentationSetDescriptor $documentationSet): void
    {
        $this->logger->warning(
            'Generating guides is experimental, no BC guarantees are given, use at your own risk'
        );

        $this->renderDocumentationSet($documentationSet, $project, $transformation);
    }

    public function getDefaultSettings() : array
    {
        return [self::FEATURE_FLAG => false];
    }

    private function renderDocumentationSet(
        DocumentationSetDescriptor $documentationSet,
        ProjectDescriptor $project,
        Transformation $transformation
    ) : void {
        $dsn = $documentationSet->getSource()->dsn();
        $stopwatch = $this->startRenderingSetMessage($dsn);

        $this->renderer->initialize($project, $documentationSet, $transformation);

        $this->commandBus->handle(
            new RenderCommand()
        );

        $this->completedRenderingSetMessage($stopwatch, $dsn);
    }

    private function startRenderingSetMessage(Dsn $dsn) : Stopwatch
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('guide');
        $this->logger->info('Rendering guide ' . $dsn);

        return $stopwatch;
    }

    private function completedRenderingSetMessage(Stopwatch $stopwatch, Dsn $dsn) : void
    {
        $stopwatchEvent = $stopwatch->stop('guide');
        $this->logger->info(
            sprintf(
                'Completed rendering guide %s in %.2fms using %.2f mb memory',
                (string) $dsn,
                $stopwatchEvent->getDuration(),
                $stopwatchEvent->getMemory() / 1024 / 1024
            )
        );
    }
}

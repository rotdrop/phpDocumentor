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

namespace phpDocumentor\Pipeline\Stage;

use phpDocumentor\Configuration\VersionSpecification;
use phpDocumentor\Descriptor\ApiSetDescriptor;
use phpDocumentor\Descriptor\Collection;
use phpDocumentor\Descriptor\Collection as PartialsCollection;
use phpDocumentor\Descriptor\DocumentationSetDescriptor;
use phpDocumentor\Descriptor\GuideSetDescriptor;
use phpDocumentor\Descriptor\VersionDescriptor;
use phpDocumentor\Guides\Nodes\ProjectNode;

use function md5;

final class InitializeBuilderFromConfig
{
    /** @param PartialsCollection<string> $partials */
    public function __construct(private readonly PartialsCollection $partials)
    {
    }

    public function __invoke(Payload $payload): Payload
    {
        $configuration = $payload->getConfig();

        $builder = $payload->getBuilder();
        $builder->createProjectDescriptor();
        $builder->setName($configuration['phpdocumentor']['title'] ?? '');
        $builder->setPartials($this->partials);
        $builder->setCustomSettings($configuration['phpdocumentor']['settings']);

        foreach ($configuration['phpdocumentor']['versions'] as $version) {
            $builder->addVersion($this->buildVersion($version));
        }

        return $payload;
    }

    private function buildVersion(VersionSpecification $version): VersionDescriptor
    {
        $collection = Collection::fromClassString(DocumentationSetDescriptor::class);
        foreach ($version->getGuides() as $guide) {
            $collection->add(
                new GuideSetDescriptor(
                    md5((string) $guide['output']),
                    $guide['source'],
                    $guide['output'],
                    $guide['format'],
                    projectNode: new ProjectNode(null, $version->getNumber()),
                ),
            );
        }

        foreach ($version->getApi() as $apiSpecification) {
            $collection->add(
                new ApiSetDescriptor(
                    md5((string) $apiSpecification['output']),
                    $apiSpecification['source'],
                    $apiSpecification['output'],
                    $apiSpecification,
                ),
            );
        }

        return new VersionDescriptor(
            $version->getNumber(),
            $collection,
        );
    }
}

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

namespace phpDocumentor\Descriptor;

use phpDocumentor\Configuration\Source;

final class ApiSetDescriptor extends DocumentationSetDescriptor
{
    /** @var Collection<FileDescriptor> */
    private $files;

    /** @var Collection<NamespaceDescriptor> */
    private $namespaces;

    public function __construct(string $name, Source $source, string $output, Collection $files, Collection $namespaces)
    {
        $this->name = $name;
        $this->source = $source;
        $this->output = $output;
        $this->files = $files;
        $this->namespaces = $namespaces;
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function getNamespaces(): Collection
    {
        return $this->namespaces;
    }
}

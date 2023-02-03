<?php

declare(strict_types=1);

/*
 * This file is part of the Contao File Usage extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoFileUsage;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;
use InspiredMinds\ContaoFileUsage\Provider\FileUsageProviderInterface;
use InspiredMinds\ContaoFileUsage\Result\Results;
use InspiredMinds\ContaoFileUsage\Result\ResultsCollection;

class FileUsageFinder implements FileUsageFinderInterface
{
    private $framework;
    private $provider;

    /**
     * @param FileUsageProviderInterface[] $provider
     */
    public function __construct(ContaoFramework $framework, iterable $provider)
    {
        $this->framework = $framework;
        $this->provider = $provider;
    }

    public function find(string $uuid): Results
    {
        $results = new Results($uuid);

        $this->framework->initialize();

        if (!Validator::isStringUuid($uuid)) {
            throw new \InvalidArgumentException('"'.$uuid.'" is not a valid UUID.');
        }

        foreach ($this->provider as $provider) {
            $results->addResults($provider->find($uuid));
        }

        return $results;
    }

    public function findAll(): ResultsCollection
    {
        $collection = new ResultsCollection();

        $this->framework->initialize();

        foreach (FilesModel::findByType('file') ?? [] as $file) {
            $uuid = StringUtil::binToUuid($file->uuid);
            $collection->addResults($uuid, $this->find(StringUtil::binToUuid($file->uuid)));
        }

        return $collection;
    }
}

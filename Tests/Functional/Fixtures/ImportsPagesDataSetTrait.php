<?php

declare(strict_types=1);

namespace Cru\ExternalLinkList\Tests\Functional\Fixtures;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

trait ImportsPagesDataSetTrait
{
    protected function importPagesDataSet(): void
    {
        $isTypo3v14OrNewer = VersionNumberUtility::convertVersionNumberToInteger(
            VersionNumberUtility::getNumericTypo3Version(),
        ) >= 14000000;

        $fixture = $isTypo3v14OrNewer
            ? __DIR__ . '/pages_t3v14.csv'
            : __DIR__ . '/pages_legacy.csv';

        $this->importCSVDataSet($fixture);
    }
}

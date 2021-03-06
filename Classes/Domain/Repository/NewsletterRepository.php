<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class NewsletterRepository
 */
class NewsletterRepository extends AbstractRepository
{
    /**
     * Remove (really remove) all data from all luxletter tables
     *
     * @return void
     */
    public function truncateAll(): void
    {
        $tables = [
            Newsletter::TABLE_NAME,
            Link::TABLE_NAME,
            Log::TABLE_NAME,
            Queue::TABLE_NAME
        ];
        foreach ($tables as $table) {
            DatabaseUtility::getConnectionForTable($table)->truncate($table);
        }
    }

    /**
     * @param Newsletter $newsletter
     * @return void
     * @throws DBALException
     * @throws IllegalObjectTypeException
     */
    public function removeNewsletterAndQueues(Newsletter $newsletter): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Queue::TABLE_NAME);
        $connection->query('delete from ' . Queue::TABLE_NAME . ' where newsletter=' . $newsletter->getUid());
        $this->remove($newsletter);
    }
}

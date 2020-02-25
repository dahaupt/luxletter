<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Model\Dto\Filter;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class UserRepository
 */
class UserRepository extends AbstractRepository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'lastName' => QueryInterface::ORDER_ASCENDING
    ];

    /**
     * @param int $groupIdentifier
     * @param int $limit
     * @return QueryResultInterface
     */
    public function getUsersFromGroup(int $groupIdentifier, int $limit = 0): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('usergroup.uid', $groupIdentifier));
        if ($limit > 0) {
            $query->setLimit($limit);
        }
        return $query->execute();
    }

    /**
     * @param Newsletter $newsletter
     * @return array|QueryResultInterface
     */
    public function getUsersFromGroupNotInNewsletterQueue(Newsletter $newsletter)
    {
        $query = $this->createQuery();
        $query->statement(
            "SELECT * FROM fe_users
            WHERE fe_users.usergroup = ? AND fe_users.deleted = 0 AND fe_users.disable = 0 
              AND fe_users.uid NOT IN( SELECT `user` FROM `tx_luxletter_domain_model_queue` WHERE newsletter = ? )",
            [$newsletter->getReceiver()->getUid(), $newsletter->getUid()]
        );
        return $query->execute();
    }

    /**
     * @param int $groupIdentifier
     * @return int
     * @throws DBALException
     */
    public function getUserAmountFromGroup(int $groupIdentifier): int
    {
        $connection = DatabaseUtility::getConnectionForTable(User::TABLE_NAME);
        $query = 'select count(uid) from ' . User::TABLE_NAME . ' ';
        $query .= 'where find_in_set(' . (int)$groupIdentifier . ',usergroup)';
        return (int)$connection->executeQuery($query)->fetchColumn(0);
    }

    /**
     * Get all luxletter receiver users
     *
     * @param Filter $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function getUsersByFilter(Filter $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $this->buildQueryForFilter($filter, $query);
        return $query->execute();
    }

    /**
     * @param Filter $filter
     * @param QueryInterface $query
     * @return void
     * @throws InvalidQueryException
     */
    protected function buildQueryForFilter(Filter $filter, QueryInterface $query): void
    {
        $and = [
            $query->equals('usergroup.luxletterReceiver', true)
        ];
        if ($filter->getSearchterms() !== []) {
            foreach ($filter->getSearchterms() as $searchterm) {
                $or = [
                    $query->like('username', '%' . $searchterm . '%'),
                    $query->like('email', '%' . $searchterm . '%'),
                    $query->like('name', '%' . $searchterm . '%'),
                    $query->like('firstName', '%' . $searchterm . '%'),
                    $query->like('middleName', '%' . $searchterm . '%'),
                    $query->like('lastName', '%' . $searchterm . '%'),
                    $query->like('address', '%' . $searchterm . '%'),
                    $query->like('title', '%' . $searchterm . '%'),
                    $query->like('company', '%' . $searchterm . '%'),
                ];
                $and[] = $query->logicalOr($or);
            }
        }
        if ($filter->getUsergroup() !== null) {
            $and[] = $query->contains('usergroup', $filter->getUsergroup());
        }
        $constraint = $query->logicalAnd($and);
        $query->matching($constraint);
    }
}

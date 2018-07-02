<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      https://syspass.org
 * @copyright 2012-2018, Rubén Domínguez nuxsmin@$syspass.org
 *
 * This file is part of sysPass.
 *
 * sysPass is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * sysPass is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *  along with sysPass.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SP\Repositories\Account;

use SP\Account\AccountRequest;
use SP\DataModel\ItemData;
use SP\Repositories\Repository;
use SP\Repositories\RepositoryItemTrait;
use SP\Storage\Database\QueryData;

/**
 * Class AccountToUserGroupRepository
 *
 * @package SP\Repositories\Account
 */
class AccountToUserGroupRepository extends Repository
{
    use RepositoryItemTrait;

    /**
     * Obtiene el listado con el nombre de los grupos de una cuenta.
     *
     * @param int $id con el Id de la cuenta
     *
     * @return \SP\Storage\Database\QueryResult
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function getUserGroupsByAccountId($id)
    {
        $query = /** @lang SQL */
            'SELECT G.id, G.name, AUG.isEdit
            FROM AccountToUserGroup AUG
            INNER JOIN UserGroup G ON AUG.userGroupId = G.id
            WHERE AUG.accountId = ?
            ORDER BY G.name';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($id);
        $queryData->setMapClassName(ItemData::class);

        return $this->db->doSelect($queryData);
    }

    /**
     * Obtiene el listado con el nombre de los grupos de una cuenta.
     *
     * @param $id
     *
     * @return \SP\Storage\Database\QueryResult
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function getUserGroupsByUserGroupId($id)
    {
        $query = /** @lang SQL */
            'SELECT G.id, G.name, AUG.isEdit
            FROM AccountToUserGroup AUG
            INNER JOIN UserGroup G ON AUG.userGroupId = G.id
            WHERE AUG.userGroupId = ?
            ORDER BY G.name';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($id);
        $queryData->setMapClassName(ItemData::class);

        return $this->db->doSelect($queryData);
    }

    /**
     * @param $id int
     *
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function deleteByUserGroupId($id)
    {
        $queryData = new QueryData();
        $queryData->setQuery('DELETE FROM AccountToUserGroup WHERE userGroupId = ?');
        $queryData->addParam($id);
        $queryData->setOnErrorMessage(__u('Error al eliminar grupos asociados a la cuenta'));

        return $this->db->doQuery($queryData)->getAffectedNumRows();
    }

    /**
     * @param AccountRequest $accountRequest
     *
     * @return bool
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function update(AccountRequest $accountRequest)
    {
        $this->deleteByAccountId($accountRequest->id);

        return $this->add($accountRequest);
    }

    /**
     * @param $id int
     *
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function deleteByAccountId($id)
    {
        $queryData = new QueryData();
        $queryData->setQuery('DELETE FROM AccountToUserGroup WHERE accountId = ?');
        $queryData->addParam($id);
        $queryData->setOnErrorMessage(__u('Error al eliminar grupos asociados a la cuenta'));

        return $this->db->doQuery($queryData)->getAffectedNumRows();
    }

    /**
     * @param AccountRequest $accountRequest
     *
     * @return int Last ID inserted
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function add(AccountRequest $accountRequest)
    {
        $query = /** @lang SQL */
            'INSERT INTO AccountToUserGroup (accountId, userGroupId, isEdit) 
              VALUES ' . $this->getParamsFromArray($accountRequest->userGroupsView, '(?,?,0)') . '
              ON DUPLICATE KEY UPDATE isEdit = 0';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->setOnErrorMessage(__u('Error al actualizar los grupos secundarios'));

        foreach ($accountRequest->userGroupsView as $userGroup) {
            $queryData->addParam($accountRequest->id);
            $queryData->addParam($userGroup);
        }

        return $this->db->doQuery($queryData)->getAffectedNumRows();
    }

    /**
     * @param AccountRequest $accountRequest
     *
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function updateEdit(AccountRequest $accountRequest)
    {
        $this->deleteEditByAccountId($accountRequest->id);

        return $this->addEdit($accountRequest);
    }

    /**
     * @param $id int
     *
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function deleteEditByAccountId($id)
    {
        $queryData = new QueryData();
        $queryData->setQuery('DELETE FROM AccountToUserGroup WHERE accountId = ? AND isEdit = 1');
        $queryData->addParam($id);
        $queryData->setOnErrorMessage(__u('Error al eliminar grupos asociados a la cuenta'));

        return $this->db->doQuery($queryData)->getAffectedNumRows();
    }

    /**
     * @param AccountRequest $accountRequest
     *
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function addEdit(AccountRequest $accountRequest)
    {
        $query = /** @lang SQL */
            'INSERT INTO AccountToUserGroup (accountId, userGroupId, isEdit) 
              VALUES ' . $this->getParamsFromArray($accountRequest->userGroupsEdit, '(?,?,1)') . '
              ON DUPLICATE KEY UPDATE isEdit = 1';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->setOnErrorMessage(__u('Error al actualizar los grupos secundarios'));

        foreach ($accountRequest->userGroupsEdit as $userGroup) {
            $queryData->addParam($accountRequest->id);
            $queryData->addParam($userGroup);
        }

        return $this->db->doQuery($queryData)->getAffectedNumRows();
    }
}
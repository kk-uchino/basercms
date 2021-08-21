<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) baserCMS Permission Community <https://basercms.net/community/>
 *
 * @copyright     Copyright (c) baserCMS Permission Community
 * @link          https://basercms.net baserCMS Project
 * @since         5.0.0
 * @license       http://basercms.net/license/index.html MIT License
 */

namespace BaserCore\Service;

use BaserCore\Model\Entity\Permission;
use BaserCore\Model\Table\PermissionsTable;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Http\ServerRequest;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Datasource\EntityInterface;
use BaserCore\Utility\BcUtil;
use BaserCore\Annotation\UnitTest;
use BaserCore\Annotation\NoTodo;
use BaserCore\Annotation\Checked;

/**
 * Class PermissionService
 * @package BaserCore\Service
 * @property PermissionsTable $Permissions
 */
class PermissionService implements PermissionServiceInterface
{

    /**
     * Permissions Table
     * @var \Cake\ORM\Table
     */
    public $Permissions;

    /**
     * PermissionService constructor.
     */
    public function __construct()
    {
        $this->Permissions = TableRegistry::getTableLocator()->get('BaserCore.Permissions');
    }

    /**
     * パーミッションの新規データ用の初期値を含んだエンティティを取得する
     * @param int $userGroupId
     * @return Permission
     * @noTodo
     */
    public function getNew($userGroupId): EntityInterface
    {
        return $this->Permissions->newEntity([
                'auth' => true,
                'user_group_id' => $userGroupId,
                'status' => 1,
            ],
            [
                'validate' => 'plain'
            ]
        );
    }

    /**
     * パーミッションを取得する
     * @param int $id
     * @return EntityInterface
     * @noTodo
     * @unitTest
     */
    public function get($id): EntityInterface
    {
        return $this->Permissions->get($id, [
            'contain' => ['UserGroups'],
        ]);
    }

    /**
     * パーミッション管理の一覧用のデータを取得
     * @param array $queryParams
     * @return Query
     * @noTodo
     * @unitTest
     */
    public function getIndex(array $queryParams): Query
    {
        $options = [];
        if (!empty($queryParams['num'])) {
            $options = ['limit' => $queryParams['num']];
        }
        if (!empty($queryParams['user_group_id'])) {
            $options = ['conditions' => ['Permissions.user_group_id' => $queryParams['user_group_id']]];
        }
        $query = $this->Permissions->find('all', $options)->order('sort', 'ASC');
        return $query;
    }

    /**
     * パーミッション登録
     * @param ServerRequest $request
     * @return \Cake\Datasource\EntityInterface|false
     * @noTodo
     * @unitTest
     */
    public function create(array $postData)
    {
        $postData = $this->autoFillRecord($postData);
        $permission = $this->Permissions->newEmptyEntity();
        $permission = $this->Permissions->patchEntity($permission, $postData, ['validate' => 'default']);

        if ($this->Permissions->save($permission)) {
            return true;
        }
        return $permission;
    }

    /**
     * パーミッション情報を更新する
     * @param EntityInterface $target
     * @param array $data
     * @return EntityInterface|false
     * @noTodo
     * @unitTest
     */
    public function update(EntityInterface $target, array $data)
    {
        $Permission = $this->Permissions->patchEntity($target, $data);
        return $this->Permissions->save($Permission);
    }

    /**
     * パーミッション情報を削除する
     * 最後のシステム管理者でなければ削除
     * @param int $id
     * @return bool
     * @noTodo
     * @unitTest
     */
    public function delete($id)
    {
        $Permission = $this->get($id);
        if($Permission->user_group_id === Configure::read('BcApp.adminGroupId')) {
            $count = $this->Permissions
                ->find('all')
                ->where(['Permissions.user_group_id' => Configure::read('BcApp.adminGroupId')])
                ->count();
            if ($count === 1) {
                throw new Exception(__d('baser', '最後のシステム管理者は削除できません'));
            }
        }
        return $this->Permissions->delete($Permission);
    }

    /**
     * 許可・拒否を指定するメソッドのリストを取得
     *
     * @return array
     * @noTodo
     * @unitTest
     * @checked
     */
    public function getMethodList() : array
    {
        return $this->Permissions::METHOD_LIST;
    }
    /**
     *  レコード作成に必要なデータを代入する
     * @param array $data
     * @return array $data
     * @checked
     */
    protected function autoFillRecord($data): array
    {
        // TODO: default値の設定後ほど正確な値に変更する
        if(empty($data['no']) || empty($data['sort'])) {
            $data['no'] = $this->Permissions->getMax('no') + 1;
            $data['sort'] = $this->Permissions->getMax('sort') + 1;
        }
        if (empty($data['auth'])) {
            $data['auth'] = true;
        }
        if (empty($data['method'])) {
            $data['method'] = $this->getMethodList()['*'];
        }
        if (empty($data['status'])) {
            $data['status'] = true;
        }
        return $data;
    }
}

<?php
namespace test\Controller;

use test\Model\UserModel;

class UserController
{
    public function getList()
    {
        $builder = UserModel::Builder()
            ->field('id,name,tel')
            ->field(['email'])
            ->page(3,10);
//            ->Where('id in (?,?,?)', [60,61,62,63])
//            ->Where('account = ?', ['wenbao'])
//            ->GetSelectSql();
//            ->Count();

        $data = $builder->select();
        $count = $builder->count();

        return ['count'=>$count,'list'=>$data];
    }

    public function getJoinList()
    {
        $builder = UserModel::Builder()
            ->field('u.id,u.name,u.tel')
            ->field(['m.school_no','m.class_id'])
            ->field(['c.class_name'])
            ->table('user u')
            ->join('member_info m', 'm.user_id = u.id')
            ->join('class_info c', 'c.id = m.class_id')
            ->page(1,10)
            ->groupBy('u.id')
            ->orderBy('u.id asc')
            ->orderBy('c.class_name desc')
            ->where('u.id in (?,?,?)', [60,61,62,63])
            ->where('u.account = ?', ['wenbao']);
//            ->GetSelectSql();
//            ->Count();

        echo $builder->getSelectSql();
        $data = $builder->select();
        $count = $builder->count();

        return ['count'=>$count,'list'=>$data];
    }

    public function getSqlErr()
    {
        $builder = UserModel::Builder()
            ->table('user u')
            ->where('lalalala in (?,?,?)', [60,61,62,63]);

        $builder->select();
        var_dump( $builder->error->getErrorCode() );
        echo $builder->error->getErrorMessage();
    }

    public function getDetail()
    {
        $data = UserModel::Builder()
            ->field('id,name,tel')
            ->field(['email'])
            ->where('id in (?,?,?)', [60,61,62,63])
            ->where('account = ?', ['wenbao'])
            ->findAsObj();

        return $data;
    }

    public function doInsert()
    {
        $params = [
            'name'=>'pdoTest',
            'tel'=>111,
            'email'=>'eeeeee'
        ];

        echo UserModel::Builder()->getInsertSql($params);
        $data = UserModel::Builder()->insert($params);

        return $data;
    }

    public function doUpdate()
    {
        $params = [
            'name'=>'lalalla',
            'tel'=>2222,
            'email'=>'hahha'
        ];
        echo UserModel::Builder()->Where('id = 88')->getInsertSql($params);
        $data = UserModel::Builder()->Where('id = 88')->getUpdateSql($params);

        return $data;
    }

    public function doTransaction()
    {
//        $db = UserModel::Builder()->begin();
//        $params = [
//            'name'=>'shiwushiwuRollBack1111',
//            'tel'=>2222,
//            'email'=>'hahha'
//        ];
//        $data = $db->Insert($params);
//        $db->RollBack();
//
//        $ata = $db->field('id,name,tel')
//            ->field(['email'])
//            ->where('id in (?,?,?)', [60,61,62,63])
//            ->where('account = ?', ['wenbao'])
//            ->find();
//
//        p($data, 0);
//
//        $db->Begin();
//        $params = [
//            'name'=>'B2B2B2Rollback2222D',
//            'tel'=>2222,
//            'email'=>'hahha'
//        ];
//        $db2 = UserModel::Builder();
//        $data = $db2->Where('id = 88')->update($params);
//        $db2->RollBack();


        UserModel::Builder()->begin();
        $params = [
            'name'=>'shiwushiwuRollBack33333',
            'tel'=>2222,
            'email'=>'hahha'
        ];
        $data = UserModel::Builder()->insert($params);
        UserModel::Builder()->rollBack();


        UserModel::Builder()->begin();
        $params = [
            'name'=>'shiwushiwuCommit66666',
            'tel'=>2222,
            'email'=>'hahha'
        ];
        $data = UserModel::Builder()->insert($params);
        UserModel::Builder()->commit();


        return $data;
    }
}
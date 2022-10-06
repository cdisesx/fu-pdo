<?php

namespace test\Controller;

use test\Model\UserModel;

class UserController
{
    public function getList()
    {
        $builder = UserModel::Builder()
            ->Field('id,name,tel')
            ->Field(['email'])
            ->Page(3,10);
//            ->Where('id in (?,?,?)', [60,61,62,63])
//            ->Where('account = ?', ['wenbao'])
//            ->GetSelectSql();
//            ->Count();

        $data = $builder->Select();
        $count = $builder->Count();

        return ['count'=>$count,'list'=>$data];
    }

    public function getJoinList()
    {
        $builder = UserModel::Builder()
            ->Field('u.id,u.name,u.tel')
            ->Field(['m.school_no','m.class_id'])
            ->Field(['c.class_name'])
            ->Table('user u')
            ->LeftJoin('member_info m', 'm.user_id = u.id')
            ->LeftJoin('class_info c', 'c.id = m.class_id')
            ->Page(1,10)
            ->GroupBy('u.id')
            ->OrderBy('u.id asc')
            ->OrderBy('c.class_name desc')
            ->Where('u.id in (?,?,?)', [60,61,62,63])
            ->Where('u.account = ?', ['wenbao']);
//            ->GetSelectSql();
//            ->Count();

        echo $builder->getSelectSql();
        $data = $builder->Select();
        $count = $builder->Count();

        return ['count'=>$count,'list'=>$data];
    }

    public function getSqlErr()
    {
        $builder = UserModel::Builder()
            ->Table('user u')
            ->Where('lalalala in (?,?,?)', [60,61,62,63])
            >Select();

        $builder->Select();
        var_dump( $builder->getErrorCode() );
        echo $builder->getErrorMessage();
    }

    public function getDetail()
    {
        $data = UserModel::Builder()
            ->Field('id,name,tel')
            ->Field(['email'])
            ->Where('id in (?,?,?)', [60,61,62,63])
            ->Where('account = ?', ['wenbao'])
            ->FindAsObj();

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
        $data = UserModel::Builder()->Insert($params);

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
        $data = UserModel::Builder()->Where('id = 88')->GetUpdateSql($params);

        return $data;
    }

    public function doTransaction()
    {
//        $db = UserModel::Builder()->Begin();
//        $params = [
//            'name'=>'shiwushiwuRollBack1111',
//            'tel'=>2222,
//            'email'=>'hahha'
//        ];
//        $data = $db->Insert($params);
//        $db->RollBack();
//
//        $ata = $db->Field('id,name,tel')
//            ->Field(['email'])
//            ->Where('id in (?,?,?)', [60,61,62,63])
//            ->Where('account = ?', ['wenbao'])
//            ->Find();
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
//        $data = $db2->Where('id = 88')->Update($params);
//        $db2->RollBack();


        UserModel::Builder()->Begin();
        $params = [
            'name'=>'shiwushiwuRollBack33333',
            'tel'=>2222,
            'email'=>'hahha'
        ];
        $data = UserModel::Builder()->Insert($params);
        UserModel::Builder()->RollBack();


        UserModel::Builder()->Begin();
        $params = [
            'name'=>'shiwushiwuCommit66666',
            'tel'=>2222,
            'email'=>'hahha'
        ];
        $data = UserModel::Builder()->Insert($params);
        UserModel::Builder()->Commit();


        return $data;
    }
}
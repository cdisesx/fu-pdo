<?php
/**
 * Created by PhpStorm.
 * User: xiaofu
 * Date: 2018/5/20
 * Time: 02:10
 */

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

        echo $count;
        p($data);

        return $data;
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
        $data = UserModel::Builder()->Insert($params);

        return $data;
    }
}
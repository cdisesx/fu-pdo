# Fu-PDO
Package PHP-PDO 
-  Table、Field、Where、LeftJoin、RightJoin、InnerJoin、
    Page、Count、Group、Order
-  Select、Find、One
-  Insert、Update
-  Begin、RollBack、Commit
-  GetSelectSql、GetUpdateSql、GetInsertSql


DB Config  
---
```shell
$config = [
    "db_1"=>[
        "dbType"=>"Mysql",
        "dbOptions"=>[
            'attrInitCommand'=>'SET NAMES UTF8'
        ],
        "write"=>[
            "host"=>"127.0.0.1",
            "port"=>"3306",
            "user"=>"root",
            "password"=>"",
            "dbname"=>"club"
        ],
        "read"=>[
            "host"=>"127.0.0.1",
            "port"=>"3306",
            "user"=>"root",
            "password"=>"",
            "dbname"=>"club"
        ]
    ],
    "db_2"=>[...]
];
```
-  You can only set writeDB 

```shell
$config = [
    "db_1"=>[
        "dbType"=>"Mysql",
        "dbOptions"=>[
            'attrInitCommand'=>'SET NAMES UTF8'
        ],
        "write"=>[
            "host"=>"127.0.0.1",
            "port"=>"3306",
            "user"=>"root",
            "password"=>"",
            "dbname"=>"club"
        ]
    ]
];
```

Extend Model 
---

```shell
use FuPdo\mysql\Model;
class UserModel extends Model
{
    public static $TABLE = 'user';
    public static $DB = 'club';
}
```

 Field Where Select Page Count
---

```shell
    public function getList()
    {
        $builder = UserModel::Builder()
            ->Field('id,name,tel')
            ->Field(['email'])
            ->Where('id in (?,?,?,?)', [60,61,62,63])
            ->Where('account = ?', ['wenbao'])
            ->Page(1,10);
            
        return [
            'count'=>$builder->Count(),
            'list'=>$builder->Select()
          ];
    }
```

 Join Table OrderBy GroupBy
---

```shell
    public function getJoinList()
    {
        $builder = UserModel::Builder()
            ->Field('u.id,u.name,u.tel')
            ->Field(['m.school_no','m.class_id'])
            ->Field(['c.class_name'])
            ->Table('user u')
            ->LeftJoin('member_info m', 'm.user_id = u.id')
            ->LeftJoin('class_info c', 'c.id = m.class_id')
            ->GroupBy('u.id')
            ->OrderBy('u.id asc')
            ->OrderBy('c.class_name desc');
            ->Page(1,10);
        echo $builder->GetSelectSql();
        return [
            'count'=>$builder->Count(),
            'list'=>$builder->Select()
          ];
    }
```

Insert GetInsertSql
---
```shell
    public function doInsert()
    {
        $params = [
            'name'=>'pdoTest',
            'tel'=>111,
            'email'=>'eeeeee'
        ];
        echo UserModel::Builder()->GetInsertSql($params);
        return UserModel::Builder()->Insert($params);
    }
```

Update GetInsertSql
---

```shell
    public function doUpdate()
    {
        $params = [
            'name'=>'lalalla',
            'tel'=>2222,
            'email'=>'hahha'
        ];
        
        echo UserModel::Builder()->Where('id = 88')->GetInsertSql($params);
        return UserModel::Builder()->Where('id = 88')->GetUpdateSql($params);
    }
```

Transaction Begin RollBack Commit
---

```shell
    public function doTransaction()
    {
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
```

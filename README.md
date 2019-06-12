# php simple crud helper
___
Simple mysql database crud helper

### insert
Insert data into table
```
$tool                = new dbaseTools();
$data["id"]          = "USER01";
$data["username"]    = "xander";
$data["pass"]        = "nothing";
$tool->insert("user_table", $data);
```

### update by PK
Update table by primary key 
```
$tool                = new dbaseTools();
$id                  = 'USER01';
$data["username"]    = "xander";
$data["pass"]        = "new_pas";
$tool->updateByPK("user_table", $data, $id);
```

### delete by PK
Delete row by primary key 
```
$tool    = new dbaseTools();
$id      = 'user01';
$tool->deleteByPK("user_table", $id);
```

### query
regular mysql query with where clause
```
$tool    = new dbaseTools();
$query   = "select * from user_table where id = :id and status = :status";
$param   = array("id"=>2, "status"=>0);
$tool->query($query, $param);
```

### generate php classes from table
generate classes from all existing tables
```
$tools = new dbaseTools();
$tools->generateClassFromAllTable("models");
```

generate classes from single table
```
$tools = new dbaseTools();
$tools->generateClassFromTableName("t_user_login", "models");
```

generate classes from table in array
```
$tools = new dbaseTools();
$listTables = ["t_user_login", "t_user_history"];
$tools->generateClassFromTables($listTables, "models");
```


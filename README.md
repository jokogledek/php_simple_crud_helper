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


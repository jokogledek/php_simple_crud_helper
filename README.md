# php simple crud helper
___
Simple mysql database crud helper

###insert
Insert data into table
```
$tool                = new dbaseTools();
$data["id"]          = "USER01";
$data["username"]    = "xander";
$data["pass"]        = "nothing";
$tool->insert("user_table", $data);
```

###update


###update by PK
Update table by primary key ID
```
$tool                = new dbaseTools();
$id                  = 'USER01';
$data["username"]    = "xander";
$data["pass"]        = "new_pas";
$tool->updateByPK("user_table", $data, $id);
```


###delete by PK


###query



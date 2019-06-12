<?php
include 'classes/dbaseTools.php';

$tools = new dbaseTools();
$listTables = ["t_table_user", "t_table_history"];
$tools->generateClassFromTables($listTables, "models");
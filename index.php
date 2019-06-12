<?php
/**
 * Created by PhpStorm.
 * User: m.azwarnurrosat
 * Date: 12/06/19
 * Time: 14.43
 */

include 'classes/dbaseTools.php';

$tools = new dbaseTools();
$query = "select * from table limit 0, 10";
$res = $tools->query($query);
print_r($res);
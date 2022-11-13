<?
use App\EventHandler;
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/delete.php"))
{
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/delete.php");
}
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/classes/EventHandler.php"))
{
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/classes/EventHandler.php");
}
EventHandler::init();

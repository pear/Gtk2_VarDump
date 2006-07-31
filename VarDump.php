<?php
require_once 'Gtk2/VarDump/Pane.php';

/**
*   Simple class for viewing PHP variables a var_dump() way
*    in PHP-Gtk - reloaded for PHP-Gtk2.
*
*   It displays arrays and objects in a tree with all their
*    children and subchildren and subsubchildren and ...
*
*   The class is memory-saving as it loads only the children
*    which are currently visible. If the user expands a row,
*    the next children will be loaded.
*
*   The tree has a small convenience feature: Left-click a row,
*    and it will be expanded. Right-click it, and it collapses.
*   Double-click or middle-click it, and all rows below the
*    current one will be expanded. They will be expanded "all" only
*    if they have been expanded before, as loading them recursively
*    is very dangerous (if there are loops).
*
*   Note that Gtk2_VarDump::display() opens its own Gtk::main()-Loop,
*    so your own program will stop executing until the VarDump window
*    is closed.
*
*   Usage:
*   require_once('Gtk2/VarDump.php');
*   $ar = new array(1, 2, 3, 4, 'key' => array('this','is','cool');
*   Gtk2_VarDump::display($ar);
*
*   Layout:
*   +--[Window title]------------------------------------------------+
*   |+--------------------------+/+---------------------------------+|
*   || Node      | Type         ^\| Key    | Type      | Value      ^|
*   ||                          |/|                                 ||
*   || Left tree with objects   |\|  Right list with simple values  ||
*   ||   and arrays             |/|     (int,float,string,...)      ||
*   ||                          v\|                                 v|
*   |+--------------------------+/+---------------------------------+|
*   |                             [OK]                               |
*   +----------------------------------------------------------------+
*
*   @author Christian Weiske <cweiske@php.net>
*/
class Gtk2_VarDump extends GtkWindow
{
    /**
    *   The tree on the left side of the window.
    *   @var GtkTreeView
    */
    protected $trTree    = null;

    /**
    *   List on the right side of the window.
    *   @var GtkTreeView
    */
    protected $trValues  = null;

    /**
    *   Model (data store) for the tree on the left.
    *   @var GtkTreeStore
    */
    protected $modTree   = null;

    /**
    *   Model (data store) for the list on the right.
    *   @var GtkListStore
    */
    protected $modValues = null;



    /**
    *   Create a new Gtk2_VarDump window.
    *   When the window is closed, a main loop is stopped.
    *
    *   @param mixed    $variable   The variable to inspect
    *   @param string   $title      The title for the window and the variable
    */
    public function __construct($variable, $title = 'Gtk2_VarDump')
    {
        parent::__construct();
        $this->buildDialog($title);
        $this->hpane->setVariable($variable, $title);
    }//public function __construct($variable, $title = 'Gtk2_VarDump')



    /**
    *   Create a new Gtk2_VarDump window and keep it displayed
    *   in its own Gtk::main()-loop.
    *   This main loop is stopped as soon the window is closed
    *
    *   @param mixed    $variable   The variable to inspect
    *   @param string   $title      The title for the window and the variable
    */
    public static function display($variable, $title = 'Gtk2_VarDump')
    {
        $vd = new Gtk2_VarDump($variable, $title);
        $vd->show_all();
        Gtk::main();
    }//public static function display($variable, $title = 'Gtk2_VarDump')



    /**
    *   Creates the dialog content, loads the tree models and so
    *
    *   @param  string  $title  The title for the window
    */
    protected function buildDialog($title)
    {
        $this->set_title($title);
        $this ->connect_simple('destroy', array($this, 'close'));

        $btnOk = GtkButton::new_from_stock(Gtk::STOCK_OK);
        $btnOk->connect_simple('clicked', array($this, 'close'));

        $vboxMain    = new GtkVBox();
        $this->hpane = new Gtk2_VarDump_Pane();

        $vboxMain->pack_start($this->hpane, true , true , 0);
        $vboxMain->pack_end(  $btnOk      , false, false, 0);

        $this->add($vboxMain);

        $btnOk->set_flags($btnOk->flags() + Gtk::CAN_DEFAULT);
        $this->set_default($btnOk);
        $this->set_default_size(600, 400);
    }//protected function buildDialog($title)



    /**
    *   Called when the user clicks "OK" or tries to close the window.
    *   This function quits the main loop opened in the constructor.
    */
    public function close()
    {
        //quit our own main loop
        $this->destroy();
        Gtk::main_quit();
    }//public function close()

}//class Gtk2_VarDump extends GtkWindow
?>
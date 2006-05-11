<?php
require_once 'Gtk2/VarDump/Tree.php';
require_once 'Gtk2/VarDump/List.php';

/**
*   Pane that holds both tree and list views.
*
*   @author Christian Weiske <cweiske@php.net>
*/
class Gtk2_VarDump_Pane extends GtkHPaned
{
    public function __construct()
    {
        parent::__construct();
        $this->build();
    }//public function __construct()



    /**
    *   Create the GUI and set up all the things
    */
    protected function build()
    {
        $this->trTree = new Gtk2_VarDump_Tree();
        $this->trList = new Gtk2_VarDump_List();

        $this->trTree->setList($this->trList);

        $this->set_position(250);

        $scrwndTree = new GtkScrolledWindow();
        $scrwndList = new GtkScrolledWindow();
        $scrwndTree->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        $scrwndList->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        $scrwndTree->add($this->trTree);
        $scrwndList->add($this->trList);

        $this->add1($scrwndTree);
        $this->add2($scrwndList);
    }//protected function build()



    /**
    *   Set the variable (and their name) to display.
    *
    *   @param mixed  $variable   Variable to display
    *   @param string $name       Name of the variable
    */
    public function setVariable($variable, $name = '')
    {
        $this->trTree->setVariable($variable, $name);
        if (gettype($variable) !== 'array' && gettype($variable) !== 'object') {
            $this->set_position(0);
        }
    }//public function setVariable($variable, $name = '')

}//class Gtk2_VarDump_Pane extends GtkHPaned
?>
<?php
require_once 'Gtk2/VarDump/ColTreeView.php';

/**
*   Listview for Gtk2_VarDump
*
*   @author Christian Weiske <cweiske@php.net>
*/
class Gtk2_VarDump_List extends Gtk2_VarDump_ColTreeView
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
        //Keyname, Type (+ size), Value
        $this->model = new GtkListStore(Gtk::TYPE_STRING, Gtk::TYPE_STRING, Gtk::TYPE_STRING);

        $this->set_model($this->model);
        $this->createColumns(array('Key', 'Type', 'Value'));
    }//protected function build()



    /**
    *   Set the variable (and their name) to display.
    *
    *   @param mixed  $variable   Variable to display
    *   @param string $name       Name of the variable
    */
    public function setVariable($variable, $name = '')
    {
        $this->buildValues($variable, $name);
    }//public function setVariable($variable, $name = '')



    /**
    *   Adds all the children of the given $variable to the list 
    *   on the right side.
    *   Arrays and objects are not added, as they appear on the
    *   tree on the left.
    *
    *   @param mixed    $variable   The variable whose children values shall be shown
    */
    protected function buildValues($variable, $name)
    {
        $this->model->clear();
        switch (gettype($variable))
        {
            case 'object':
                if (class_exists(get_class($variable))) {
                    $arKeys = array_keys(get_object_vars($variable));
                } else {
                    $arKeys = array('error');
                    $variable = null;
                    $variable->error = 'Class not available in PHP';
                }
                foreach ($arKeys as $key) {
                    $value = $variable->$key;
                    $this->appendValue($key, $value);
                }
                break;

            case 'array':
                $arKeys = array_keys($variable);
                foreach ($arKeys as $key) {
                    $value = $variable[$key];
                    $this->appendValue($key, $value);
                }
                break;

            default:
                //simple type
                $this->appendValue($name, $variable);
                break;
        }
    }//protected function buildValues($variable)



    /**
    *   Appends one value to the list on the right.
    *   Arrays and objects will not be displayed, as they already
    *   appear on the tree on the left side.
    *
    *   @param mixed    $key    The title for the node
    *   @param mixed    $value  The value to display
    */
    protected function appendValue($key, $value)
    {
        switch (gettype($value)) {
            case 'object':
            case 'array':
                //Don't display arrays and objects in the values list
                continue;
            case 'string':
                $this->model->append(
                    array(
                        $key,
                        'string[' . strlen($value) . ']',
                        $value
                    )
                );
                break;
            default:
                $this->model->append(
                    array(
                        $key,
                        gettype($value),
                        $value
                    )
                );
                break;
        }
    }//protected function appendValues($variable, $arKeys)

}//class Gtk2_VarDump_List extends Gtk2_VarDump_ColTreeView
?>
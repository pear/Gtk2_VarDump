<?php
require_once 'Gtk2/VarDump/ColTreeView.php';

/**
*   Treeview for Gtk2_VarDump
*
*   @author Christian Weiske <cweiske@php.net>
*/
class Gtk2_VarDump_Tree extends Gtk2_VarDump_ColTreeView
{
    /**
    *   List view for plain values
    *   @var Gtk2_VarDump_List
    */
    protected $list = null;



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
        //Node, Type, original variable, if the children have been checked for subchildren
        $this->model = new GtkTreeStore(Gtk::TYPE_STRING, Gtk::TYPE_STRING, Gtk::TYPE_PHP_VALUE, Gtk::TYPE_BOOLEAN);
        $this->set_model($this->model);
        $selection = $this->get_selection();
        $selection->connect ('changed'              , array($this, 'selectTreeRow'));
        $this->connect      ('row-expanded'         , array($this, 'expandTree'));
        $this->connect_after('event'                , array($this, 'clickedTree'));
        $this->set_events   (Gdk::_2BUTTON_PRESS | Gdk::BUTTON_RELEASE);

        $this->createColumns(array('Node', 'Type'));
    }//protected function build()



    /**
    *   Set the list that displays the simple values
    *
    *   @param Gtk2_VarDump_List $list  List object
    */
    public function setList($list)
    {
        $this->list = $list;
    }//public function setList($list)



    /**
    *   Set the variable (and their name) to display.
    *
    *   @param mixed  $variable   Variable to display
    *   @param string $name       Name of the variable
    */
    public function setVariable($variable, $name = '')
    {
        $this->buildTree($variable, $name);
        $this->expand_row('0', false);//expand first row
    }//public function setVariable($variable, $name = '')



    /**
    *   Appends the given $variable to the tree on the right.
    *   $name is used as title for the node, $parent is the parent node
    *   to which the new node will be appended.
    *
    *   @param mixed        $variable   The variable to append
    *   @param string       $name       The title for the variable (e.g. array key)
    *   @param GtkTreeIter  $parent     The parent node to which the new node shall be appended
    *   @param int          $nStop      After how many levels appending shall be stopped
    */
    protected function buildTree($variable, $name, $parent = null, $nStop = 1)
    {
        $type = gettype($variable);

        if ($type == 'array') {
            $type .= '[' . count($variable) . ']';
        } else if ($type == 'object') {
            $type = trim(get_class($variable));
        } else {
            //not an array and not an object
            if ($this->list !== null) {
                $this->list->setVariable($variable, $name);
            }
            return;
        }

        $node = $this->model->append($parent, array($name, $type, $variable, false));

        if ($nStop > 0) {
            $this->appendChildren($variable, $node, $nStop--);
        }

        if ($parent === null) {
            $this->get_selection()->select_path('0');
        }
    }//protected function buildTree($variable, $name, $parent = null, $nStop = 1)



    /**
    *   Appends all the children of the given variable to $node
    *
    *   @param mixed        $variable   The variable, whose children shall be appended
    *   @param GtkTreeIter  $node       The parent node to which the new ones shall be appended
    *   @param int          $nStop      After how many levels appending shall be stopped
    */
    protected function appendChildren($variable, $node, $nStop = 1)
    {
        $type = gettype($variable);
        switch ($type) {
            case 'object':
                if (class_exists(get_class($variable))) {
                    $arKeys = array_keys(get_object_vars($variable));
                } else {
                    //Class not available in PHP -> no keys
                    $arKeys = array();
                }
                break;
            case 'array':
                $arKeys = array_keys($variable);
                break;
            default:
                return;
        }

        foreach ($arKeys as $key) {
            $value = ($type == 'array') ? $variable[$key] : $variable->$key;
            switch (gettype($value)) {
                case 'object':
                case 'array':
                    $this->buildTree($value, $key, $node, $nStop - 1);
                    break;
                default:
                    //other types aren't displayed in the tree
                    break;
            }
        }
    }//protected function appendChildren($variable, $node, $nStop = 1)



    /**
    *   Called whenever a tree row is expanded.
    *   It is used to load the children of the node's children
    *   if they haven't been loaded yet.
    *
    *   @param  GtkTreeView $tree       The tree on which the signal has been emitted 
    *   @param  GtkTreeIter $iterator   The node which has been expanded
    */
    public function expandTree($tree, $iterator)
    {
        if ($this->model->get_value($iterator, 3)) {
            //already checked
            return;
        }

        //check if children have subchildren and load them
        $child = $this->model->iter_children($iterator);
        while ($child !== null) {
            $this->appendChildren(
                $this->model->get_value($child, 2),
                $child
            );
            //get the next child
            $child = $this->model->iter_next($child);
        }
        $this->model->set($iterator, 3, true);
    }//public function expandTree($tree, $iterator)



    /**
    *   Called whenever a row on the left tree has been selected.
    *   It is used to show the children of the selected variable
    *   on the right list.
    *
    *   @param array    $selection  Array consisting of the model and the currently selected node (GtkTreeIter)
    */
    public function selectTreeRow($selection)
    {
        list($model, $iter) = $selection->get_selected();
        if ($iter === null) {
            return;
        }
        $variable = $model->get_value($iter, 2);
        if ($this->list !== null) {
            $this->list->setVariable($variable);
        }
    }//public function selectTreeRow($selection)



    /**
    *   The tree has been clicked, and the currently selected row
    *   will be expanded or collapsed, depending which mouse button has
    *   been clicked.
    *   The left mouse button will expand the node,
    *   the right mouse button will collapse it.
    *   Middle mouse button and a double-clicked left button will
    *    expand all children but *only* if they have been expanded 
    *    before - it would be too dangerous to expand all children to
    *    any depth recursively if there are loops.
    *
    *   @param GtkTreeView  $tree   The tree which has been clicked
    *   @param GdkEvent     $event  The event data for the click event
    */
    public function clickedTree($tree, $event)
    {
        if ($event->type !== Gdk::_2BUTTON_PRESS && $event->type !== Gdk::BUTTON_RELEASE) {
            return;
        }

        list($model, $arSelected) = $tree->get_selection()->get_selected_rows();
        if ($arSelected === null) {
            return;
        }

        $path = implode(':', $arSelected[0]);

        if ($event->button == 1) {
            //left mouse button
            //If double-click: expand all rows down
            $tree->expand_row($path, $event->type == Gdk::_2BUTTON_PRESS);
        } else if ($event->button == 2) {
            //middle mouse button - expand all
            $tree->expand_row($path, true);
        } else if ($event->button == 3) {
            //right mouse button
            $tree->collapse_row($path);
        }
    }//public function clickedTree($tree, $event)

}//class Gtk2_VarDump_Tree extends Gtk2_VarDump_ColTreeView
?>
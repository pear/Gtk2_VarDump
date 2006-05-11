<?php
/**
*   Treeview with some extra methods
*
*   @author Christian Weiske <cweiske@php.net>
*/
class Gtk2_VarDump_ColTreeView extends GtkTreeView
{
    public function __construct()
    {
        parent::__construct();
    }//public function __construct()



    /**
    *   Creates GtkTreeView columns out of an string array and
    *   appends them to the tree view.
    *   The columns will be resizable and sortable.
    *
    *   @param array        $arColumns  Array of strings which are the titles for the columns
    */
    protected function createColumns($arColumns)
    {
        $cell_renderer = new GtkCellRendererText();
        foreach ($arColumns as $nId => $strTitle) {
            $column = new GtkTreeViewColumn($strTitle, $cell_renderer, "text", $nId);
            $column->set_resizable(true);
            $column->set_sort_column_id($nId);
            $this->append_column($column);
        }
    }//protected function createColumns($arColumns)

}//class Gtk2_VarDump_ColTreeView extends GtkTreeView
?>
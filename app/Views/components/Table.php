<?php

namespace App\Views\Components;

/**
 * HTML Table class. This is used as a template to build standard table that would look the same across the whole site. 
 * Easier maintainance and reusability.
 */
class Table
{
    // === RENDERED PARTS ===
    public $thead;   // Final <thead> HTML for the current table instance
    public $tbody;   // Final <tbody> HTML for the current table instance

    // === DATA / CONFIG ===
    public $url;           // Base URL used by the table (reloading/filtering etc..)
    public $rows;          // 2D array of cell values (strings/HTML). one array per row
    private $headers;      // List of column headers (strings)
    private $archiveBtns;  // Two-state toggle labels and status (for instance ['Active' => 1, 'Archived' => 0])
    private $searchQuery;  // Current search string (if any) for the search bar

    // === PRESENTATION ===
    public $title;       // Optional title shown above the table
    public $totalRows;   // Cached total rows count (used for display/pagination if needed)
    
    /*
    ** Constructor
    ** Initializes core properties. $archiveBtns is expected to be an associative array
    ** with two labels (e.g., ['Active' => 1, 'Archived' => 0]) to render the toggle.
    */
    function __construct($url, $headers, $rows, $archiveBtns, $searchQuery = null)
    {
        $this->headers = $headers;
        $this->rows = $rows;
        $this->url = $url;
        
        $this->archiveBtns = $archiveBtns;
        $this->searchQuery = $searchQuery;

        // Placeholders; filled on first render
        $this->thead = "";
        $this->tbody = "";

        $this->title = '';

        // Useful for UI snippets like "x of y"
        $this->totalRows = count($rows);
    }

    /*
    ** Build <thead> markup based on $this->headers.
    ** Adds extra empty THs if a data row has more cells than there are headers.
    */
    public function tableHeaders()
    {
        $html = "<thead>";
        
        for ($i = 0; $i < count($this->headers); $i++) {

            $header = $this->headers[$i];

            $html.= "<th>
                <div class='th-container'>
                    <span class='th-text'>".$header.'</span>';
            // NOTE: Sorting buttons/icons can be appended here if needed in future
            $html.= "</div></th>";
        }

        // If first row has more columns than $headers, pad with empty <th> to keep alignment
        if($this->rows != [] && count($this->rows[0]) > count($this->headers)){
            for ($i = 0; $i < (count($this->rows[0]) - count($this->headers)); $i++) {    
                $html.= "<th></th>";
            }
        }
        
        $html.= "</thead>";

        $this->thead = $html;

        return $html;
    }

    /*
    ** Build <tbody>.
    ** $id is used to set a unique tbody id for external JS to target (filters, reloads, etc.).
    */
    public function tableContent($id)
    {
        $html = "<tbody id='table_body_$id'>";
        
        for($i = 0; $i < count($this->rows); $i++){
            $row = $this->rows[$i];
            
            $html.= "<tr>";

            // Each cell is output as is (already sanitized HTML)
            foreach ($row as $key => $cell) {
                $html.= "<td data-column='$key'>$cell</td>";
            }

            $html.= "</tr>";
        }
        
        $html.= "</tbody>";

        $this->tbody = $html;

        return $html;
    }

    /*
    ** Compose the main table (title + table element with thead/tbody).
    */
    public function mainTable($id){
        $html =
            "<div id='main_table_$id' class='scrollable-table'>";

            // Optional section title above the table
            if($this->title != ''){
                $html.= "<h4>". $this->title . "</h4>";
            }
            $html.= "<table id='real_table_$id' class='my-tables'>";

            // Header/content are generated once then reused if already built
            $html.= $this->thead == "" ? $this->tableHeaders() : $this->thead;

            $html.= $this->tbody == "" ? $this->tableContent($id) : $this->tbody;

            $html.= 
                "</table>";

        $html.= "</div>";
        
        return $html;
    }

    // Render a table with optional archive toggle (no search bar)
    public function tableHtml($id)
    {
        $url = $this->url;
        $html = "<div id=$id class='table-with-nav' data-url='$url'>";

        // Show archive toggle only if two labels were provided
        if(count($this->archiveBtns) == 2){
            $html.= $this->seeArchivedOption($id);
        }
            
        $html.= $this->mainTable($id);
        
        $html.= "</div>";

        return $html;
    }

    // Echo wrapper
    public function tableEcho($id)
    {
        echo $this->tableHtml($id);
    }

    /*
    ** Render a table with search bar + optional archive toggle.
    */
    public function tableWithSearchDisplay($id)
    {
        $url = $this->url;
        $html = "<div id=$id class='table-with-nav' data-url='$url'>";

        $html.= "<div class='table_search_display_tools'>";

        // Inline search control
        $html.= $this->searchBar($id);

        $html.= "</div>";

        // Archive toggle if configured with two labels
        if(count($this->archiveBtns) == 2){
            $html.= $this->seeArchivedOption($id);
        }

        // Main table
        $html.= $this->mainTable($id);
        
        $html.= "</div>";

        return $html;
    }

    // Echo wrapper for table with search controls
    public function tableWithSearchDisplayEcho($id)
    {
        echo $this->tableWithSearchDisplay($id);
    }

    /*
    ** Search bar UI.
    ** input[type=text] bound to $this->searchQuery and two buttons:
    **  - GO (passes $tableId via data for the handler)
    **  - CLEAR (link back to $this->url to remove filters)
    */
    public function searchBar($tableId)
    {
        $searchId = 'search_query_'. $tableId;
        $goId = 'go_button_'.$tableId;

        return 
        "<div class='search_form'>
            <label for='search_query'>Search:</label>
            <input type='text' id='$searchId' class='search_query' name='search_query' value='$this->searchQuery' > ".
            \App\Views\components\Button::generateButton($goId, "search_input_go", SEARCH_ICON, [$tableId]) .
            "<a href=". $this->url . " >" . \App\Views\components\Button::generateButton("", "remove_filters", CLOSE_BUTTON, []) . "</a>" .
        "</div>";
    }

    // Archive toggle control (two buttons). Highlights the active state.
    public function seeArchivedOption($id){
        $activeClasses = 'see_archive_btns see_active_btn';
        $archiveClasses = 'see_archive_btns see_archive_btn';

        // Use the first/last keys of $archiveBtns as button labels
        $activeText = array_key_first($this->archiveBtns);
        $archiveText = array_key_last($this->archiveBtns);

        // Add highlight to whichever label is marked active in $archiveBtns
        if($this->archiveBtns[$activeText]){
            $activeClasses.= " highlighted_btn";
        } else {
            $archiveClasses.= " highlighted_btn";
        }

        // Buttons with $id so external handlers know which table to affect
        $activeButton = \App\Views\components\Button::generateButton("active_btn_$id", $activeClasses, $activeText, [$id]);
        $archiveButton = \App\Views\components\Button::generateButton("archive_btn_$id", $archiveClasses, $archiveText, [$id]);

        return "<div class='see_archive_options'>" . $activeButton. $archiveButton ."</div>";
    }
}
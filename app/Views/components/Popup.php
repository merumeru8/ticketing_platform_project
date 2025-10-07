<?php
namespace App\Views\Components;

/**
 * Popup class
 * 
 * Html Div modal box dispatcher
 */
class Popup
{
    /**
     * Genrate a simple modal view container with specified attributes
     *
     * @param string  $id                Element id to be displayed
     * @param string  $content           Html content to be innserted in the view
     * @param string  $additionalClasses One or more class attributes used for customization
     * @param boolean $overlayer         Overlay pop-up turn-on flag TRUE|FALSE
     * @return string
     */
    public static function generatePopup($id, $content, $additionalClasses, $overlayer = false): string {
        
        // Default adds the 'my-popup' clas 
        $additionalClasses = "my-popup $additionalClasses";

        // Core popup:
        // - Root container is initially hidden via inline style
        // - Close button lives in a header wrapper ('.close-popup-wrap')
        // - content goes into a div whose id is "$id-content"
        $html =  
            "<div id=$id class='$additionalClasses' style='display:none;'>
                <div class='close-popup-wrap'><button id='close-popup-button' class='red_tool'>".CLOSE_BUTTON."</button></div>
                <div id='$id-content'>
                {$content}
                </div>
            </div>";
            
        // Optional overlayer element:
        // When $overlayer is true, render a full-page overlay (also hidden by default)
        if ($overlayer) {
            $html .= "<div id='overlay_popup' style='display:none;'></div>";
        }
        return $html;
    }
}
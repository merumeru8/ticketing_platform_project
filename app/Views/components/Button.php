<?php

namespace App\Views\Components;

/**
 * Button class
 * 
 * Html Button element dispatcher
 */
class Button
{
    /**
     * Generate DOM html button element based on provided attributes.
     *
     * @param string $id       Element Id
     * @param string $classes  Element classes (one or more)
     * @param string $text     Element name/value
     * @param array  $jsParam Parameters for the js method
     * @param array  $disabled  Possible data-options
     * @return string
     */
    static public function generateButton(string $id, string $classes, string $text, array $jsParam, array $disabled = [false, '']): string
    {
        // Build the button's base attributes (class is always present)
        $identification = "class='$classes'";

        // Optional HTML id attribute (added only if provided)
        if($id != ""){
            $identification.= " id='$id'";
        }

        // Adds the native disabled attribute when requested
        if($disabled[0]){
            $identification.= " disabled";
        }


        // Encode JS params safely into a data attribute for client-side handlers
        $params = json_encode($jsParam, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

        // Core button UI (includes data-jsparams payload)
        $btn = "<button $identification data-jsparams='$params'>$text</button>";


        // If a non-empty tooltip message is provided in $disabled[1], wrap the button
        // with a tooltip container (visual hint even when disabled)
        if($disabled[1] != ''){
            return "<div class='btn_tool_tooltip $classes'> 
                {$btn}
                <div class='tooltip'>
                    <span class='tooltiptext'> {$disabled[1]} </span>
                </div>
             </div>";
        } else {
            // No tooltip
            return $btn;
        }
    }

    /**
     * Generate DOM html button element with tooltip feature
     * based on provided attributes.
     *
     * @param string $id        Element Id
     * @param string $classes   Element classes (one or more)
     * @param string $text      Element name/value
     * @param string $action    Action event based on the elt type
     * @param array  $info      Possible data-options
     * @return string
     */
    static public function generateToolButton(string $id, string $classes, string $text, array $jsParam, array $info = ["disabled" => false, "tooltip" => '']): string
    {
        // Tooltip text (no tooltip if empty string)
        $tooltip = $info['tooltip'];

        // Tool btns always have the tool-button class
        $toolClasses = $classes. " tool-button";

        // If tooltip text exists, wrap the core button within a tooltip container
        if($tooltip != ''){
            return "<div class='btn_tool_tooltip'>". 
                self::generateButton($id, $toolClasses, $text, $jsParam, [$info['disabled'], '']).
                "<div class='tooltip'>
                    <span class='tooltiptext tooltiptext_tools'> $tooltip </span>
                </div>
             </div>";
        } else {
            // No tooltip
            return self::generateButton($id, $toolClasses, $text, $jsParam, [$info['disabled'], '']);
        }
    }

    /**
     * Generate a tools box container with multiple actionable buttons 
     *
     * @param string $boxClasses     Element class attribute(s) 
     * @param array $buttonsClasses  Button element class attribute(s)
     * @param array $texts           Element name/value
     * @param array $jsMethods       Names of js methods
     * @param array $jsParams        Parameters for the js methods
     * @param array $tooltips        Element tool-tip attribute
     * @return string
     */
    public static function toolsBox($boxClasses, $buttonsClasses, $texts, $jsParams, $tooltips, $disabled):string
    {
        // Container for a vertical set of action buttons (tool buttons)
        $html= "<div class='$boxClasses tool-buttons'>";

        for($i = 0; $i < count($texts); $i++){
            $html.=  \App\Views\components\Button::generateToolButton("", $buttonsClasses[$i], $texts[$i], $jsParams[$i], ["disabled" => $disabled[$i], "tooltip" => $tooltips[$i]]);
        }

        $html.= "</div>";

        return $html;
    }
}

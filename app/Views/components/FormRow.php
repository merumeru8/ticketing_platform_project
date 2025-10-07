<?php

namespace App\Views\components;

/**
 * FormRow class
 * 
 * Form input element builder class
 * 
 */
class FormRow
{

     /**
      * Generate a main html container as parent element
      * To contain any additional form input element
      *
      * @param string $additionalClasses  Element Div class attributes
      * @param string $labelClasses       Element Label class attribute
      * @param string $labelText          Element Label name
      * @param string $inputId            Element label for attribute value
      * @param string $field              Inner html element
      * @param string $display            Element display option flag
      * @return string
      */
    public static function formRowRawContent(string $additionalClasses, string $labelClasses, string $labelText, string $inputId, string $field, string $display){
        // Build a labeled row wrapper:
        // - Always includes `.form_row_container` + any extra classes
        // - If $display is false, hides the whole row via inline style
        $html = "<div class='$additionalClasses form_row_container' ". 
                ($display ? "" : "style='display:none;' ").">
                    <label for='$inputId' class='$labelClasses form_row_part form_row_label'>$labelText: </label>
                    <div class='form_row_part form_row_input'>
                        $field
                    </div>
                </div>";

        return $html;
    }


    /**
     * Generate different input element types based on parsed key
     *
     * @param string $additionalClasses    Parent Element Div class attribute values
     * @param string $labelClasses         Parent Element label class attribute
     * @param string $labelText            Parent Element label name
     * @param string $inputClasses         Element input class attribute
     * @param string $inputId              Element input id attr
     * @param string $inputName            ELement input name
     * @param string $inputType            Element input type option
     * @param string $display              Display flag
     * @param string|array $additionalInfo       Optional Additional attributes to add
     * @return string
     */
    public static function formRowHtml(string $additionalClasses, string $labelClasses, string $labelText, string $inputClasses, string $inputId, string $inputName, string $inputType, string $display, string|array $additionalInfo = "value=''"){

        // Switch on $inputType. For each case we assemble the field HTML
        switch($inputType){
            case 'text':
                // Basic single-line text input with styling classes
                $input = "<input type='text' name='$inputName' class='$inputClasses form_row_input_text' id='$inputId' $additionalInfo>";
                break;
            case 'textArea':
                // $additionalInfo is split by "%-%-%":
                // [0] => textarea content, [1] => extra HTML attributes (optional)
                $split = explode("%-%-%",$additionalInfo);
                $text = $split[0];
                
                $more = count($split) > 1 ? $split[1] : '';
                $input = "<textarea name='$inputName' id='$inputId' class='$inputClasses form_row_input_area' cols='55' rows='3' $more>$text</textarea>";
                break;
            case 'date': case'datetime': case 'datetime-local': 
                // Date family: lock keyboard input (onkeydown prevent), add a reset icon next to field
                $input ="<div class='flex_row_center'>";
                    $input.= "<input type='$inputType' name='$inputName' class='$inputClasses form_row_input_date custom-date' onkeydown='event.preventDefault()' id='$inputId' $additionalInfo>";
                    $input.= "<span class='reset_date' data-inputdateid='$inputId'>". CLOSE_BUTTON ."</span>";
                $input.= "</div>";
                break;
            case 'number':
                // Numeric input with standard listeners/styling classes
                $input = "<input type='number' name='$inputName' class='$inputClasses form_row_input_number' id='$inputId' $additionalInfo>";
                break;
            case 'file':
                // Two modes:
                // 1) If $additionalInfo contains "%%", split into:
                //    [0] => attributes for <input type='file'> (e.g., accept=...)
                //    [1] => label text for styled upload button
                //    [2] => optional extra HTML (e.g., preview markup)
                // 2) Otherwise treat $additionalInfo as raw attributes for the <input>
                if(str_contains($additionalInfo, "%%")){
                    $additionalInfo = explode("%%", $additionalInfo);
                    $input = "<input type='file' name='$inputName' class='$inputClasses my_file_listeners form_row_input_file' id='$inputId' $additionalInfo[0]>";
                    $input.= "<label id='label_$inputId' for='$inputId' class='file_label_form_row ast-button'>$additionalInfo[1]</label>";
                    $input.= $additionalInfo[2];
                    
                } else {
                    $input = "<input type='file' name='$inputName' class='$inputClasses my_file_listeners form_row_input_file' id='$inputId' $additionalInfo>";
                }
                break;
            case 'checkbox':
                // Checkbox with optional disabled + reason tooltip:
                // - When disabled, a small `.normal_tooltip` is appended with the 'why' message
                $input = "<span class='with_tooltip'><input type='checkbox' id='$inputId' class='$inputClasses form_row_input_check' name='$inputId' ". ($additionalInfo['checked'] ? "checked ":"") . ($additionalInfo['disabled'] ? "disabled ":"") ." >";
                if($additionalInfo['disabled']){
                    $input.= "<span class='normal_tooltip'>" . $additionalInfo['why'] . "</span></span>";
                } else {
                    $input.= "</span>";
                }
                
                break;
            default:
                // Fallback generic input; caller passes attributes via $additionalInfo
                $input = "<input name='$inputName' class='$inputClasses' id='$inputId' $additionalInfo>";
                break;
        }

        // Wrap the concrete input field into the labeled row container
        return self::formRowRawContent($additionalClasses, $labelClasses, $labelText, $inputId, $input, $display);
    }

}
?>
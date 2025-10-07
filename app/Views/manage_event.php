<?php require_once "layouts/header.php"; ?>

<!-- Page: Manage Event | Create/Edit single event -->
<main id="main" class="site-main">
    <!-- Section title (dynamic) -->
    <h3 class='inner_section_title'> <?php echo $page_title; ?></h3>
    <!-- Ticket form fields -->
    <div class='new_event_form'>
        <!-- Title -->
        <?php echo \App\Views\components\FormRow::formRowHtml('assign_form_row', '', "Ticket Title<span class='disclaimer disclaimer_red_no_border'>*</span>", '', 'new_event_name', 'event_name', 'text', true, "value='$title'")?>
        
        <!-- Start date -->
        <?php echo \App\Views\components\FormRow::formRowHtml('assign_form_row', '', "Sales start<span class='disclaimer disclaimer_red_no_border'>*</span>", '', 'new_event_start', 'event_start', 'date', true, "value='$start'")?>
        
        <!-- End date -->
        <?php echo \App\Views\components\FormRow::formRowHtml('assign_form_row', '', "Sales end<span class='disclaimer disclaimer_red_no_border'>*</span>", '', 'new_event_end', 'event_end', 'date', true, "value='$end'")?>

        <!-- Max -->
        <?php echo \App\Views\components\FormRow::formRowHtml('assign_form_row', '', "Max quantity<span class='disclaimer disclaimer_red_no_border'>*</span>", '', 'new_event_max', 'event_max', 'number', true, "min='$buyers' value='$max' step='1'")?>

        <!-- Price -->
        <?php echo \App\Views\components\FormRow::formRowHtml('assign_form_row', '', "Price<span class='disclaimer disclaimer_red_no_border'>*</span>", '', 'new_event_price', 'event_price', 'number', true, "value='$price' step='0.01'")?>

        <!-- Visibility -->
        <?php echo \App\Views\components\FormRow::formRowHtml('assign_form_row', '', "Public", '', 'new_event_vis', 'event_vis', 'checkbox', true, ['checked' => $visibility, "disabled" => $no_private, "why" => "Tickets already sold"])?>
        
        <?php 
            $info = "accept='.png,.jpg,.jpeg'";
            
            if($image){
                $info.= "%%Change File%%". showPreview($image);
            }else{
                $info.= "%%Select File%%";
            }

            echo \App\Views\components\FormRow::formRowHtml("", "", "Upload a image (optional)", "", "ticket_image", "ticket_image", "file", true, $info);
        ?>
    </div>

    <!-- Primary actions: Save / Exit -->
    <div class='btn_wrap_margin'>

        <?php echo \App\Views\components\Button::generateButton('save_event', "assign_btns", "Save", [$ticket_id]);?>
        <a href='/organizer'><?php echo \App\Views\components\Button::generateButton('exit_event', "assign_btns", "Discard changes and exit", []); ?></a>
    </div>

</main>

<!-- Specific js for this page -->
<script>
    $(document).ready(function(){
        // set date input to empty
        $('.reset_date').on("click", function(){
            $(this).siblings("input").val("");
        });

        // preview selected logo
        $("#ticket_image").change(function(){
            let file = this.files[0];
            if(! file || ! file.type.startsWith('image/')) return;


            $("#label_ticket_image").text("Change File");

            let $logoPreview = $("#ticket_logo_preview");
            let blobUrl = URL.createObjectURL(file);

            if($logoPreview.length){
                $logoPreview.attr('src', blobUrl);
            } else {
                let $img = $('<img>', {
                    src: blobUrl,
                    alt: 'Preview',
                    class: 'img_preview',
                    id: 'ticket_logo_preview'
                });

                $(this).closest('.form_row_input').append($img);
            }
        });

        // Collect form values and submit via fetchAPIUpload
        $("#save_event").on("click", function(){
            let info = {};

            let parameters = $(this).data("jsparams");

            info['title'] = $('#new_event_name').val();
            info['start'] = $('#new_event_start').val();
            info['end'] = $('#new_event_end').val(); 
            info['price'] = $('#new_event_price').val();
            info['visibility'] = $('#new_event_vis').is(":checked") ? 1 : 0;
            info['max'] = $('#new_event_max').val();
            info['ticketId'] = parameters[0];

            // Handle optional image upload
            let inputImgFiles = document.getElementById("ticket_image").files;

            let inputImg = inputImgFiles.length > 0 ? inputImgFiles[0] : null;
            
            // Send payload + file to server
            let promise = fetchAPIUpload("/organizer/save_event", info, inputImg);

            promise.then((data) => {
                if(data){
                    
                    if(! data.error){
                        localStorage.setItem("message", data.message);
                        location.href = "/organizer";
                    } else {
                        popupMessage(data.message);
                    }
                }
            })
        })
    })
</script>
<?php require_once "layouts/footer.php"; ?>
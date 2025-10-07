/*** --------------------- CONSTANTS ---------------------------------- */
const OVERLAY_ID = "#overlay_popup";
const CLOSE_BTN_ID = "#close-popup-button";
const POPUP_CONTAINER_ID = "#popup_id";
const POPUP_CONTENT_ID = "#popup_id-content";

/*** --------------------- POPUP FUNCTIONS ---------------------------- */

/**
 * Generate and display a popup window.
 * 
 * @param {string} id - ID of the popup element (without #)
 * @param {boolean} closable - If true, popup can be closed by user
 */
function triggerPopup(id, closable = true) {
  const $popup = $("#" + id);
  const $closeButton = $popup.find(CLOSE_BTN_ID);
  const $overlay = $(OVERLAY_ID);

  if (closable) {
    
    $closeButton.on("click", closePopups);
    $overlay.on("click", closePopups);
  } else {
    
    $closeButton.off("click");
    $overlay.off("click");
  }

  $overlay.show();
  $popup.show();
}

/**
 * Close any currently displayed popup
 */
function closePopups() {  
  $(".my-popup, .courses-popup").hide();

  $(OVERLAY_ID).hide();
}

/**
 * Display a standard popup with error text and button
 * 
 * @param {string} text - Message to display
 */
function popupMessage(text) {
  const $popup = $(POPUP_CONTENT_ID);
  $popup.empty(); // Clear old content

  // Build popup content
  const $div = $("<div>", { class: "flex_column" });
  const $textSpan = $("<span>").html(text);
  const $button = $("<button>", {
    class: "right_btns",
    text: "Ok",
  }).on("click", closePopups);

  $div.append($textSpan, $button);
  $popup.append($div);

  // Trigger popup display
  triggerPopup(POPUP_CONTAINER_ID.substring(1)); // remove '#' for id
}
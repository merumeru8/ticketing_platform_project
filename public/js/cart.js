/*** --------------------- Cart Object ---------------------------------------- */
class Cart {

  /**
   * Initialize Cart object
   * @param {string} storageKey - Key to store/retrieve cart in localStorage
   */
  constructor(storageKey = "cart") {
    this.storageKey = storageKey;

    // Load from localStorage if available, otherwise start empty
    let saved = localStorage.getItem(this.storageKey);
    this.items = saved ? JSON.parse(saved) : {};
  }

  // Save current cart to localStorage
  saveCart() {
    localStorage.setItem(this.storageKey, JSON.stringify(this.items));
  }

  /**
   * Add an item to the cart
   * @param {string} ticketId - Unique ticket identifier
   * @param {number} quantity - Quantity to add
   * @param {number} pricePerUnit - Price of one unit
   * @param {string} eventName - Name of the event
   */
  addItem(ticketId, quantity, pricePerUnit, eventName) {
    quantity = parseInt(quantity, 10);
    pricePerUnit = parseFloat(pricePerUnit);

    if (this.items[ticketId]) {
      this.items[ticketId].quantity += quantity;
    } else {
      this.items[ticketId] = { quantity: quantity, pricePerUnit: pricePerUnit, name: eventName };
    }
    this.saveCart();
  }

  /**
   * Update an item's quantity, delete if <= 0
   * @param {string} ticketId - Unique ticket identifier
   * @param {number} newQuantity - New quantity to set
   */
  updateItem(ticketId, newQuantity) {
    newQuantity = parseInt(newQuantity, 10);
  
    if (this.items[ticketId]) {
      this.items[ticketId].quantity = newQuantity;
      if (newQuantity <= 0) delete this.items[ticketId];
      this.saveCart();
    }
  }

  /**
   * Remove an item completely
   * @param {string} ticketId - Unique ticket identifier
   */
  removeItem(ticketId) {
    if (this.items[ticketId]) {
      delete this.items[ticketId];
      this.saveCart();
    }
  }

  // Empty the cart
  clearCart() {
    this.items = {};
    localStorage.removeItem(this.storageKey);
  }

  /**
   * Get total price of cart
   * @returns {number} Total amount
   */
  getTotal() {
    return Object.values(this.items).reduce((sum, item) => sum + item.quantity * item.pricePerUnit, 0);
  }

  /**
   * Get total quantity of items
   * @returns {number} Total quantity
   */
  getTotalItems() {
    return Object.values(this.items).reduce((sum, item) => sum + item.quantity, 0);
  }

  /**
   * Get all cart items
   * @returns {Object} Copy of items in cart
   */
  getItems() {
    return { ...this.items };
  }

  /**
   * Get one item from cart
   * @param {string} ticketId - Unique ticket identifier
   * @returns {Object} Item data (quantity, pricePerUnit, name)
   */
  getItem(ticketId) {
    return { ...this.items[ticketId] };
  }
}

// Global cart instance
const cart = new Cart();

/*** --------------------- CONSTANTS SECTION ---------------------------------- */
// CSS selectors and ID prefixes for DOM manipulation
const CART_ITEMS_CLASS = ".cart_items";
const CART_REVIEW_ITEMS_CLASS = ".cart_review_items";
const CART_ITEMS_CONTAINER_CLASS = ".cart_items_container";
const CART_ITEMS_REVIEW_CONTAINER_CLASS = ".cart_items_review_container";
const CART_TOTAL_CLASS = ".cart_preview_total";
const CART_COUNT_CLASS = ".cart_preview_items";
const CART_TOTAL_AMOUNT_CLASS = ".total_amount";
const CART_TOTAL_ITEM_NUMBER_CLASS = ".total_cart_item_number";
const CART_QTY_INPUT_PREFIX = "#cart_quantity_value_";
const CART_ITEM_PREFIX = "#cart_item-";
const CART_REVIEW_ITEM_PREFIX = "#cart_review_item-";

/*** --------------------- CART & CHECKOUT FUNCTIONS -------------------------- */

/**
 * Switch to a phase in cart UI
 * @param {string} id - Phase button ID
 */
function activatePhase(id){
    $(".phase_nav_btn").removeClass("active_menu");
    $(".phase_section").removeClass("active_phase");

    let $btn = $("#" + id).addClass("active_menu");

    let sectionId = "phase_section_" + $btn.data("id");
    $("#" + sectionId).addClass("active_phase");
}

/**
 * Add a new item row to the cart container
 * @param {string} ticketId - Ticket ID
 * @param {number} quantity - Quantity to display
 * @param {number} pricePerUnit - Price per unit
 * @param {string} eventName - Event name
 */
function addNewRowToCart(ticketId, quantity, pricePerUnit, eventName){
  // Icons for buttons
  let PLUS_ICON = "<i class='fas fa-plus'></i>";
  let MINUS_ICON = "<i class='fas fa-minus'></i>";
  let CLOSE_BUTTON = "<i class='fas fa-times'></i>";

  // Create main cart item container
  let $cartItem = $("<div>", {
    id: 'cart_item-'+ticketId,
    class: "cart_items",
    "data-id": ticketId,
    "data-price": pricePerUnit
  });

  // Inner info section
  let $infoDiv = $("<div>", { class: "item-dynamic-info" }).appendTo($cartItem);

  // Quantity controls
  let $qtyControl = $("<span>", { class: "cart-quantity_control", css: { "margin-right": "1em" } }).appendTo($infoDiv);

  // + button
  $("<button>", {
    class: "cart_qty_btn plus",
    "data-jsparams": JSON.stringify([ticketId]),
    html: PLUS_ICON
  }).appendTo($qtyControl);

  // Qty input
  $("<input>", {
    type: "number",
    name: "quantity",
    class: "cart_qty_input",
    id: 'cart_quantity_value_'+ticketId,
    "data-ticketid": ticketId,
    value: quantity,
    min: 0
  }).appendTo($qtyControl);

  // - button
  $("<button>", {
    class: "cart_qty_btn minus",
    "data-jsparams": JSON.stringify([ticketId]),
    html: MINUS_ICON
  }).appendTo($qtyControl);

  $infoDiv.append(" x ");

  // Ticket name
  $("<span>", { class: "event_name", text: eventName , css: { "margin-right": "1em", "margin-left": "1em" } }).appendTo($infoDiv);

  $infoDiv.append(" = ");

  // Line price
  $("<span>", { 
    class: "event_price", 
    text: (quantity*pricePerUnit).toFixed(2), 
    css: { "margin-left": "1em" } 
  }).appendTo($infoDiv);

  // Remove button
  $("<button>", {
    id: 'remove_item_'+ticketId,
    class: "red_tool remove_items",
    "data-jsparams": JSON.stringify([ticketId]),
    html: CLOSE_BUTTON
  }).appendTo($cartItem);

  // Append item to cart container
  $(CART_ITEMS_CONTAINER_CLASS).append($cartItem);
}

/**
 * Add a new static row to the review section
 * @param {string} ticketId - Ticket ID
 * @param {number} quantity - Quantity to display
 * @param {number} pricePerUnit - Price per unit
 * @param {string} eventName - Event name
 */
function addNewRowToReview(ticketId, quantity, pricePerUnit, eventName){
  let totalPrice = (quantity * pricePerUnit).toFixed(2);

  let $cartReviewItem = $("<div>", {
    id: 'cart_review_item-'+ticketId,
    class: "cart_review_items",
    "data-id": ticketId,
    "data-price": pricePerUnit
  });

  let $itemInfo = $("<div>", { class: "item-info" }).appendTo($cartReviewItem);

  $("<span>", {
    class: "quantity",
    text: quantity,
    css: { "margin-right": "1em" }
  }).appendTo($itemInfo);

  $itemInfo.append(" x ");

  $("<span>", {
    class: "event_name",
    text: eventName,
    css: { "margin-left": "1em", "margin-right": "1em" }
  }).appendTo($itemInfo);

  $itemInfo.append(" = ");

  $("<span>", {
    class: "event_price",
    text: totalPrice,
    css: { "margin-left": "1em" }
  }).appendTo($itemInfo);

  $(CART_ITEMS_REVIEW_CONTAINER_CLASS).append($cartReviewItem);
}

/**
 * Update quantity of an item in the cart and UI
 * @param {string} tId - Ticket ID
 * @param {number} variation - +/- amount to apply
 */
function updateQuantityFromCart(tId, variation){
  let newValue = (parseInt($(CART_QTY_INPUT_PREFIX+tId).val(), 10) || 0) + variation;

  let item = cart.items[tId];
  if(item){
    cart.updateItem(tId, newValue, item.pricePerUnit, item.eventName);  
    updateItemInCart(tId, newValue, item.pricePerUnit);
  }
  
  setTotalCart();
  toggleBtnsProceed();
}

/**
 * Update item in cart and/or review section
 * @param {string} ticketId - Ticket ID
 * @param {number} quantity - New quantity
 * @param {number} pricePerUnit - Unit price
 * @param {string} eventName - Event name
 */
function updateItemInCart(ticketId, quantity, pricePerUnit, eventName){
    if ($(CART_QTY_INPUT_PREFIX+ticketId).length) {
        if(quantity == 0){
            $(CART_ITEM_PREFIX+ticketId).remove()
        } else {
            $(CART_QTY_INPUT_PREFIX+ticketId).val(quantity);
            let newTotal = parseInt(quantity) * parseFloat(pricePerUnit);
            $(CART_ITEM_PREFIX+ticketId).find(".event_price").text(newTotal.toFixed(2));
        }
    } else if(quantity > 0){
        addNewRowToCart(ticketId, quantity, pricePerUnit, eventName)
    }

    if ($(CART_REVIEW_ITEM_PREFIX+ticketId).length) {
        if(quantity == 0){
            $(CART_REVIEW_ITEM_PREFIX+ticketId).remove()
        } else {
            let $item = $(CART_REVIEW_ITEM_PREFIX+ticketId);
            $item.find('.quantity').text(quantity)
            let newTotal = parseInt(quantity) * parseFloat(pricePerUnit);
            $item.find(".event_price").text(newTotal.toFixed(2));
        }
    } else if(quantity > 0){
        addNewRowToReview(ticketId, quantity, pricePerUnit, eventName)
    }
}

/**
 * Remove a single item row from cart UI
 * @param {JQuery<HTMLElement>} $e - jQuery element clicked
 */
function removeItemUI($e){
  let parameters = $e.data("jsparams");
  let tId = parameters[0];

  cart.removeItem(tId);

  $(CART_ITEM_PREFIX + tId).remove()
  $(CART_REVIEW_ITEM_PREFIX + tId).remove()

  setTotalCart();
}

/**
 * Clear all cart and review rows
 */
function removeAllItemsInCart(){
  cart.clearCart();
  $(CART_ITEMS_CLASS).remove();
  $(CART_REVIEW_ITEMS_CLASS).remove();
}

/**
 * Update total price and item count everywhere
 */
function setTotalCart(){
  count = cart.getTotalItems();
  totalPrice = cart.getTotal();
  
  $(CART_COUNT_CLASS).text(count);
  $(CART_TOTAL_CLASS).text(totalPrice.toFixed(2));
  $(CART_TOTAL_AMOUNT_CLASS).text(totalPrice.toFixed(2));
  $(CART_TOTAL_ITEM_NUMBER_CLASS).text(count);
}

/**
 * Attach listeners for cart item interactions
 * @param {string} elemId - ID of the cart popup container
 */
function addCartListeners(elemId){
    // Proceed to review
    $("#"+elemId).on("click", "#cart_btn_proceed, #cart_review_review", function(){
      activatePhase("cart_review_review")
    });

    // Cancel
    $("#"+elemId).on("click", "#cart_btn_cancel", function(){
      closePopups();
    })

    // Checkout
    $("#"+elemId).on("click", "#review_btn_checkout", function(){
      let info = {};
      info['cart'] = JSON.parse(localStorage.getItem('cart'));
      let promise = fetchAPIJSON("/customer/checkout", info, "POST");

      promise.then((data) => {
          if(data){

              if("error" in data && data.error){
                closePopups();
                popupMessage(data.message);
                return;
              }

              //the redirect value means that the user is not logged in. 
              //Redirecting the user to login page and leaving a trail in the localStorage
              if("redirect" in data && data.redirect){
                localStorage.setItem("from_checkout", 1);
                location.href = "/login";
                return
              }

              //User logged in, emptying the stored and UI cart before reporting to the user
              removeAllItemsInCart();
              setTotalCart();
              $(".qty_input").val(0);
              closePopups();
              
              if("message" in data){
                localStorage.setItem("message", data.message);
              }
              window.location.pathname === "/" ? location.reload() : location.href = "/";
          }
      })
    })

    // Back
    $("#"+elemId).on("click", "#review_btn_back, #cart_review_cart", function(){
      activatePhase("cart_review_cart")
    })

    // +/- buttons
    $("#"+elemId).on("click", ".cart_qty_btn.plus, .cart_qty_btn.minus", function(){
      let parameters = $(this).data("jsparams");
      updateQuantityFromCart(parameters[0], $(this).hasClass("minus") ? -1 : 1);
    });
    
    // Manual qty input change
    $("#"+elemId).on("change", ".cart_qty_input", function(){
      updateQuantityFromCart($(this).data("ticketid"), 0);
    })

    // Remove item button
    $("#"+elemId).on("click", ".remove_items", function() {
      removeItemUI($(this))
    });
}

/**
 * Enable/disable proceed/checkout buttons
 */
function toggleBtnsProceed(){
  if(cart.getTotalItems() > 0){
    $("#cart_btn_proceed").prop("disabled", false);
    $("#review_btn_checkout").prop("disabled", false);
  } else {
    $("#cart_btn_proceed").prop("disabled", true);
    $("#review_btn_checkout").prop("disabled", true);
  }
}

/**
 * Populate UI from saved cart in localStorage
 */
function populateCart(){
  Object.entries(cart.getItems()).forEach(([ticketId, item]) => {
    addNewRowToCart(ticketId, item.quantity, item.pricePerUnit, item.name);
    addNewRowToReview(ticketId, item.quantity, item.pricePerUnit, item.name);
  })

  setTotalCart();
  toggleBtnsProceed();
}

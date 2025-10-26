const stripePublicKey    = document.querySelector("#stripe-public-key").value;
const homeUrl            = document.querySelector("#home-url").value;
const currentUrl         = document.querySelector("#current-url").value;
const choosedFields      = document.querySelector("#choosed_fields").value;
const formLang           = document.querySelector("#espad-form-lang").value;
const fixAmount          = document.querySelector("#fix_amount").value;

let choosedCurrency      = document.querySelector("#currency").value;
let choosedColor         = document.querySelector("#color").value;
let choosedPaymentLayout = document.querySelector("#espad-payment-layout").value;

let fixAmountNew;
 
// Fallback to USD
if ( !choosedCurrency.trim() ) {
  choosedCurrency = 'USD';
}

if ( !choosedPaymentLayout.trim() ) {
    choosedPaymentLayout = 'auto';
}

// Fallback auf Standardfarbe, wenn leer oder nur Leerzeichen
if ( !choosedColor.trim() ) {
  choosedColor = '#0d8889';
}

if ( fixAmount ) {
    fixAmountNew = fixAmount * 100;    
}

const stripe = Stripe(stripePublicKey);

// Select all radio buttons
const amountRadios = document.querySelectorAll('#prices_box input[name="amount"]');

let firstValue = null;
let items;

// Ensure that a radio button exists
if ( amountRadios.length > 0 ) {
    
    // Falls ein Radio ausgewählt ist, nehme dessen Wert, sonst erstes Radio
    const checkedRadio = document.querySelector('#prices_box input[name="amount"]:checked');
    firstValue = checkedRadio ? checkedRadio.value : amountRadios[0].value;
    
}

// Set items based on firstValue, fixAmountNew, or default to 10000
if ( firstValue ) {
    
    items = [{ amount: parseInt(firstValue, 10) }];
    
} else if (typeof fixAmountNew !== 'undefined' && fixAmountNew) {
    
    items = [{ amount: fixAmountNew }];
    
} else {
    
    items = [{ amount: 10000 }];
    
}

// Call initialize with the correct value (items[0].amount is guaranteed to be set)
initialize(items[0].amount);

// Loop through all radio buttons and attach listeners
amountRadios.forEach((radio) => {
  radio.addEventListener('change', async function(event) {
      
    const selectedAmount = event.target.value;
    console.log('Neuer Betrag ausgewaehlt:', selectedAmount);
    
    // Replace the amount on Change  
    items[0].amount = selectedAmount;  

    // Hier musst du Stripe neu initialisieren oder Checkout neu laden
    await initialize(selectedAmount);
      
  });
});

const amountInput = document.getElementById('amountInput');
let debounceTimeout;

if ( amountInput ) {

    amountInput.addEventListener('input', function (event) {
        
        const valueString = event.target.value;
        
        // Block commas and periods
        if (/[,.]/.test(valueString)) {
            event.target.value = '';
            alert('Please enter a valid number without commas or other characters');
            return;
        }

        // Limit input to a maximum of 5 digits
        if (valueString.length > 5) {
            event.target.value = valueString.slice(0, 5); 
            return;
        }        

      clearTimeout(debounceTimeout);

      debounceTimeout = setTimeout(async () => {
        
        const value = valueString.trim();
        const parsed = parseFloat(value);

        if ( !isNaN(parsed) && parsed > 0 ) {

          // Disable other radio buttons    
          const checkedInput = document.querySelector('#espad_page .btn-check:checked');

          if ( checkedInput ) {

            checkedInput.checked = false;

          }

          // Round to two decimal places and convert to cents
          const enteredAmount = Math.round(parsed * 100);
          console.log('New amount (in cent) for Stripe:', enteredAmount);

          const supElement = document.querySelector('#submit #button-text sup');
          // Divide enteredAmount by 100 and round to two decimal places
          let formattedAmount = enteredAmount / 100;    
          supElement.innerHTML = formattedAmount + ' ' + choosedCurrency;        

          // Replace the amount on Change  
          items[0].amount = enteredAmount;

          // Reinitialize Stripe Checkout
          await initialize(enteredAmount);   

        } else {

          alert('Please enter a valid number');

        }
      }, 500);

    });
    
}

document
  .querySelector("#payment-form")
  .addEventListener("submit", handleSubmit);
 
// Fetches a payment intent and captures the client secret
async function initialize(amount) {
    
  const createCheckoutUrl = document.querySelector("#create-checkout-url").value; 
    
  const { clientSecret, dpmCheckerLink } = await fetch(createCheckoutUrl, {    
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ items }),
  }).then((r) => r.json());
    
  const appearance = {
      theme: 'stripe',

      variables: {
        colorPrimary: choosedColor,
        colorBackground: '#ffffff',
        colorText: '#30313d',
        colorDanger: '#df1b41',
      }
  };
   
  // Pass the appearance object to the Elements instance
  elements = stripe.elements({
      clientSecret, 
      appearance,
      locale: formLang,
  });      

  const paymentElementOptions = {
      layout: choosedPaymentLayout,
  };

  const paymentElement = elements.create("payment", paymentElementOptions);
  paymentElement.mount("#payment-element");

}

async function handleSubmit(e) {
    
  e.preventDefault();
  setLoading(true);
    
    // Get the address information
    const name         = document.querySelector("#name").value;
    const email        = document.querySelector("#email").value; 
    
    let street, postalCode, city, phoneNumber;

    switch ( choosedFields ) {

        case 'name_email_address':
        case 'name_email_address_required_fields':
        street     = document.querySelector("#street").value;
        postalCode = document.querySelector("#postal_code").value;
        city       = document.querySelector("#city").value;
        break;

        case 'name_email_address_telephone':
        case 'name_email_address_telephone_required_fields':
        street      = document.querySelector("#street").value;
        postalCode  = document.querySelector("#postal_code").value;
        city        = document.querySelector("#city").value;            
        phoneNumber = document.querySelector("#phone_number").value;
        break;

        default:
        street      = '';
        postalCode  = '';
        city        = '';
        phoneNumber = '';    
        break;
            
    }
    
    const espadFormId = document.querySelector("#espad-form-id").value;
    const successUrl  = document.querySelector("#success-url").value;
    const cancelUrl   = document.querySelector("#cancel-url").value;
    
    const stripeMetadataCampaign = document.querySelector("#stripe-metadata-campaign").value;
    const stripeMetadataProject  = document.querySelector("#stripe-metadata-project").value;
    const stripeMetadataProduct  = document.querySelector("#stripe-metadata-product").value;
    const espadPaymentToken      = document.querySelector("#espad_payment_token").value;
    
    // Cleanup: remove previous Stripe/form-related params from currentUrl
    const url = new URL(currentUrl);
    const params = url.searchParams;

    // List of params you always want to remove
    const keysToRemove = [
      'espad_form_id',
      'success_url',
      'cancel_url',
      'stripe_metadata_campaign',
      'stripe_metadata_project',
      'stripe_metadata_product',
      'espad_payment_token',    
      'payment_intent',
      'payment_intent_client_secret',
      'redirect_status'
    ];
 
    // Remove them from the search params
    keysToRemove.forEach(key => params.delete(key));

    // Rebuild the clean URL
    url.search = params.toString();  

    // Decide on separator based on remaining query string
    const separator = url.search ? '&' : '?';

    // Rebuild return_url with cleaned base + fresh params
    let espadReturnUrl =
      url.toString() + separator +
      "espad_form_id=" + encodeURIComponent(espadFormId) + "&" +
      "success_url=" + encodeURIComponent(successUrl) + "&" +
      "cancel_url=" + encodeURIComponent(cancelUrl) + "&" +
      "stripe_metadata_campaign=" + encodeURIComponent(stripeMetadataCampaign) + "&" +
      "stripe_metadata_project=" + encodeURIComponent(stripeMetadataProject) + "&" +
      "stripe_metadata_product=" + encodeURIComponent(stripeMetadataProduct) + "&" +
      "espad_payment_token=" + encodeURIComponent(espadPaymentToken);
    
    espadReturnUrl += '#payment-form';    
    
    const { error } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        payment_method_data: {
          billing_details: {
            name: name,
            email: email,
            phone: phoneNumber,
            address: {
              line1: street,
              postal_code: postalCode,
              city: city
            }
          }
        },
        return_url: espadReturnUrl
      }
    });
    
  // This point will only be reached if there is an immediate error when
  // confirming the payment. Otherwise, your customer will be redirected to
  // your `return_url`. For some payment methods like iDEAL, your customer will
  // be redirected to an intermediate site first to authorize the payment, then
  // redirected to the `return_url`.
  if (error.type === "card_error" || error.type === "validation_error") {
    showMessage(error.message);
  } else {
    showMessage("An unexpected error occurred.");
  }

  setLoading(false);
}

// ------- UI helpers -------

function showMessage(messageText) {
  const messageContainer = document.querySelector("#payment-message");

  messageContainer.classList.remove("hidden");
  messageContainer.textContent = messageText;

  setTimeout(function () {
    messageContainer.classList.add("hidden");
    messageContainer.textContent = "";
  }, 4000);
}

// Show a spinner on payment submission
function setLoading(isLoading) {
    
  if (isLoading) {
    // Disable the button and show a spinner
    document.querySelector("#submit").disabled = true;
    document.querySelector("#spinner").classList.remove("hidden");
    document.querySelector("#button-text").classList.add("hidden");
  } else {
    document.querySelector("#submit").disabled = false;
    document.querySelector("#spinner").classList.add("hidden");
    document.querySelector("#button-text").classList.remove("hidden");
  }
}
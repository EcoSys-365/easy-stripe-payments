let baseCheckoutMode  = document.querySelector("#espad_checkout_mode").value;
const stripePublicKey = document.querySelector("#stripe-public-key").value;
const stripe          = Stripe(stripePublicKey);
 
let elements = null;
let paymentElement = null;
let currentMode = null;
let currentItems = null;
let paymentFormHandlerBound = false;
let checkoutRenderId = 0;

const oneTimeModes = ["Standard", "Campaign"];

if ( oneTimeModes.includes(baseCheckoutMode) ) {
    baseCheckoutMode = "OneTime";
}
  
/* ---------------------------------- */
/* Common helpers */
/* ---------------------------------- */
function getCreateCheckoutUrl(mode) {
    if (mode === "Subscription") {
        return document.querySelector("#create-checkout-url-subscription")?.value || "";
    }

    return document.querySelector("#create-checkout-url-one-time")?.value || "";
}
        
function updatePricesBoxVisibility(mode) {
    if (baseCheckoutMode !== "Advanced") return;

    const pricesBox = document.querySelector("#prices_box");
    if (!pricesBox) return;

    pricesBox.style.display = mode === "Subscription" ? "none" : "block";
}          
         
function getEffectiveCheckoutMode() {
    if (baseCheckoutMode === "Advanced") {
        const selectedMode = document.querySelector('input[name="advanced_checkout_mode"]:checked')?.value;

        if (selectedMode === "subscription") {
            updatePricesBoxVisibility("Subscription");
            return "Subscription";
        }

        updatePricesBoxVisibility("OneTime");
        return "OneTime";
    }

    return baseCheckoutMode;
}              
        
function destroyCheckout() {
    if (paymentElement) {
        paymentElement.destroy();
        paymentElement = null;
    }

    elements = null;

    const paymentElementContainer = document.querySelector("#payment-element");
    if (paymentElementContainer) {
        paymentElementContainer.innerHTML = "";
        paymentElementContainer.classList.remove("hidden");
    }
}        

function showMessage(messageText) {
    const messageContainer = document.querySelector("#payment-message");
    if (!messageContainer) return;

    messageContainer.classList.remove("hidden");
    messageContainer.textContent = messageText;

    setTimeout(() => {
        messageContainer.classList.add("hidden");
        messageContainer.textContent = "";
    }, 4000);
} 
        
function setLoading(isLoading) {
    const submitButton = document.querySelector("#submit");
    const spinner = document.querySelector("#spinner");
    const buttonText = document.querySelector("#button-text");

    if (!submitButton || !spinner || !buttonText) return;

    submitButton.disabled = isLoading;

    if (isLoading) {
        spinner.classList.remove("hidden");
        buttonText.classList.add("hidden");

        submitButton.style.opacity = "0.4";
        submitButton.style.cursor = "not-allowed";
        submitButton.style.pointerEvents = "none";
    } else {
        spinner.classList.add("hidden");
        buttonText.classList.remove("hidden");
 
        submitButton.style.opacity = "1";
        submitButton.style.cursor = "pointer";
        submitButton.style.pointerEvents = "auto";
    }
} 
        
function setSubmitButtonState(isDisabled) {
    const submitButton = document.querySelector("#submit");
    if (!submitButton) return;

    submitButton.disabled = isDisabled;

    if (isDisabled) {
        submitButton.style.opacity = "0.4";
        submitButton.style.cursor = "not-allowed";
        submitButton.style.filter = "grayscale(35%)";
    } else {
        submitButton.style.opacity = "1";
        submitButton.style.cursor = "pointer";
        submitButton.style.filter = "none";
    }
}          
        
function setElementLoading(isLoading) {
    const loadingEl = document.querySelector("#payment-element-loading");
    const paymentElementContainer = document.querySelector("#payment-element");

    setSubmitButtonState(isLoading);

    if (!loadingEl || !paymentElementContainer) return;

    if (isLoading) {
        loadingEl.classList.remove("hidden");
        paymentElementContainer.classList.add("hidden");
    } else {
        loadingEl.classList.add("hidden");
        paymentElementContainer.classList.remove("hidden");
    }
}        

function getCommonAppearance(choosedColor) {
    return {
        theme: "stripe",
        variables: {
            colorPrimary: choosedColor,
            colorBackground: "#ffffff",
            colorText: "#30313d",
            colorDanger: "#df1b41",
        }
    };
}

function getCommonCheckoutConfig() {
    let choosedCurrency      = document.querySelector("#currency")?.value || "";
    let choosedColor         = document.querySelector("#color")?.value || "";
    let choosedPaymentLayout = document.querySelector("#espad-payment-layout")?.value || "";
    let formLang             = document.querySelector("#espad-form-lang")?.value || "en";

    if (!choosedCurrency.trim()) {
        choosedCurrency = "USD";
    }

    if (!choosedPaymentLayout.trim()) {
        choosedPaymentLayout = "auto";
    }

    if (!choosedColor.trim()) {
        choosedColor = "#0d8889";
    }

    return {
        choosedCurrency,
        choosedColor,
        choosedPaymentLayout,
        formLang
    };
}

function getOneTimeItems() {
    const fixAmount = document.querySelector("#fix_amount")?.value || "";
    const amountRadios = document.querySelectorAll('#prices_box input[name="amount"]');

    let fixAmountNew;
    let firstValue = null;

    if (fixAmount) {
        fixAmountNew = fixAmount * 100;
    }

    if (amountRadios.length > 0) {
        const checkedRadio = document.querySelector('#prices_box input[name="amount"]:checked');
        firstValue = checkedRadio ? checkedRadio.value : amountRadios[0].value;
    }

    if (firstValue) {
        return [{ amount: parseInt(firstValue, 10) }];
    }

    if (typeof fixAmountNew !== "undefined" && fixAmountNew) {
        return [{ amount: fixAmountNew }];
    }

    return [{ amount: 10000 }];
}
    
function updateSubmitButtonContent(mode) {
    const labelElement = document.querySelector("#submit-button-label");
    const amountElement = document.querySelector("#submit-button-amount");

    if (!labelElement || !amountElement) return;

    if (mode === "Subscription") {
        const label = document.querySelector("#advanced-subscription-button-label")?.value || "Subscribe Now";
        const amount = document.querySelector("#advanced-subscription-amount")?.value || "";
        const currency = document.querySelector("#advanced-subscription-currency")?.value || "";

        labelElement.textContent = label;

        if (amount && currency) {
            amountElement.textContent = amount + " " + currency.toUpperCase();
        } else {
            amountElement.textContent = "";
        }
 
        return;
    }

    const label = document.querySelector("#advanced-one-time-button-label")?.value || "Pay";
    const currency = document.querySelector("#currency")?.value || "USD";
    const amount = currentItems?.[0]?.amount || "";

    labelElement.textContent = label;

    if (amount) {
        amountElement.textContent = (parseInt(amount, 10) / 100) + " " + currency.toUpperCase();
    } else {
        amountElement.textContent = "";
    }
}    

/* ---------------------------------- */
/* Mount functions */
/* ---------------------------------- */
async function initSubscriptionCheckout(renderId) {
    
    const { choosedColor, choosedPaymentLayout, formLang } = getCommonCheckoutConfig();
    const createCheckoutUrl = getCreateCheckoutUrl("Subscription");
    
    if (!createCheckoutUrl) {
        showMessage("Subscription checkout URL is missing.");
        if (renderId === checkoutRenderId) {
            setElementLoading(false);
        }
        return;
    }

    if (renderId === checkoutRenderId) {
        setElementLoading(true);
    }

    try {
        const response = await fetch(createCheckoutUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json" }
        });

        const data = await response.json();

        // Falls inzwischen schon ein neuer Render gestartet wurde: abbrechen
        if (renderId !== checkoutRenderId) {
            return;
        }

        if (!response.ok || !data.clientSecret) {
            showMessage(data.error || "Could not load client secret.");
            setElementLoading(false);
            return;
        }

        elements = stripe.elements({
            clientSecret: data.clientSecret,
            appearance: getCommonAppearance(choosedColor),
            locale: formLang,
        });

        paymentElement = elements.create("payment", {
            layout: choosedPaymentLayout,
        });

        paymentElement.on("ready", () => {
            if (renderId !== checkoutRenderId) return;
            setElementLoading(false);
        });

        paymentElement.on("loaderror", (event) => {
            if (renderId !== checkoutRenderId) return;
            console.error("Stripe Payment Element load error:", event);
            showMessage("Could not load payment methods.");
            setElementLoading(false);
        });

        paymentElement.mount("#payment-element");

    } catch (error) {
        if (renderId !== checkoutRenderId) return;
        console.error(error);
        showMessage("An error occurred while loading the payment methods.");
        setElementLoading(false);
    }
    
}

async function initOneTimeCheckout(renderId) {
    
    const { choosedColor, choosedPaymentLayout, formLang } = getCommonCheckoutConfig();
    const createCheckoutUrl = getCreateCheckoutUrl("OneTime");

    if (!currentItems) {
        currentItems = getOneTimeItems();
    }

    if (!createCheckoutUrl) {
        showMessage("One-time checkout URL is missing.");
        if (renderId === checkoutRenderId) {
            setElementLoading(false);
        }
        return;
    }

    if (renderId === checkoutRenderId) {
        setElementLoading(true);
    }

    try { 
        const response = await fetch(createCheckoutUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ items: currentItems }),          
        });

        const data = await response.json();

        if (renderId !== checkoutRenderId) {
            return;
        }

        if (!response.ok || !data.clientSecret) {
            showMessage(data.error || "Could not load client secret.");
            setElementLoading(false);
            return;
        }

        elements = stripe.elements({
            clientSecret: data.clientSecret,
            appearance: getCommonAppearance(choosedColor),
            locale: formLang,
        });

        paymentElement = elements.create("payment", {
            layout: choosedPaymentLayout,
        });

        paymentElement.on("ready", () => {
            if (renderId !== checkoutRenderId) return;
            setElementLoading(false);
        });

        paymentElement.on("loaderror", (event) => {
            if (renderId !== checkoutRenderId) return;
            console.error("Stripe Payment Element load error:", event);
            showMessage("Could not load payment methods.");
            setElementLoading(false);
        });

        paymentElement.mount("#payment-element");

    } catch (error) {
        if (renderId !== checkoutRenderId) return;
        console.error(error);
        showMessage("An error occurred while loading the payment methods.");
        setElementLoading(false);
    }
    
} 
 
async function renderCheckout(mode) {
    const renderId = ++checkoutRenderId;

    destroyCheckout();
    currentMode = mode;
    setElementLoading(true);

    if (mode === "Subscription") {
        await initSubscriptionCheckout(renderId);
    } else {
        await initOneTimeCheckout(renderId);
    }
}

/* ---------------------------------- */
/* Submit */
/* ---------------------------------- */

async function handleSubmit(e) {
    e.preventDefault();

    if (!elements) {
        showMessage("The payment form is not ready yet.");
        return;
    }

    setLoading(true);

    try {
        const currentUrl     = document.querySelector("#current-url")?.value || window.location.href;
        const choosedFields  = document.querySelector("#choosed_fields")?.value || "";

        const name  = document.querySelector("#name")?.value || "";
        const email = document.querySelector("#email")?.value || "";

        let street = "";
        let postalCode = "";
        let city = "";
        let phoneNumber = "";
        let country = "";

        switch (choosedFields) {
            case "name_email_address":
            case "name_email_address_required_fields":
                street     = document.querySelector("#street")?.value || "";
                postalCode = document.querySelector("#postal_code")?.value || "";
                city       = document.querySelector("#city")?.value || "";
                break;

            case "name_email_address_telephone":
            case "name_email_address_telephone_required_fields":
                street      = document.querySelector("#street")?.value || "";
                postalCode  = document.querySelector("#postal_code")?.value || "";
                city        = document.querySelector("#city")?.value || "";
                phoneNumber = document.querySelector("#phone_number")?.value || "";
                break;
                
            case "name_email_address_telephone_country_required_fields":
                street      = document.querySelector("#street")?.value || "";
                postalCode  = document.querySelector("#postal_code")?.value || "";
                city        = document.querySelector("#city")?.value || "";
                phoneNumber = document.querySelector("#phone_number")?.value || "";
                country     = document.querySelector("#country")?.value || "";
                break;                

            default:
                break;
        }

        const espadFormId = document.querySelector("#espad-form-id")?.value || "";
        const successUrl  = document.querySelector("#success-url")?.value || "";
        const cancelUrl   = document.querySelector("#cancel-url")?.value || "";

        const stripeMetadataCampaign = document.querySelector("#stripe-metadata-campaign")?.value || "";
        const stripeMetadataProject  = document.querySelector("#stripe-metadata-project")?.value || "";
        const stripeMetadataProduct  = document.querySelector("#stripe-metadata-product")?.value || "";
        const espadPaymentToken      = document.querySelector("#espad_payment_token")?.value || "";

        const url = new URL(currentUrl);
        const params = url.searchParams;

        const keysToRemove = [
            "espad_form_id",
            "success_url",
            "cancel_url",
            "stripe_metadata_campaign",
            "stripe_metadata_project",
            "stripe_metadata_product",
            "espad_payment_token",
            "payment_intent",
            "payment_intent_client_secret",
            "redirect_status"
        ];

        keysToRemove.forEach(key => params.delete(key));
        url.search = params.toString();

        const separator = url.search ? "&" : "?";

        let finalSuccessUrl = successUrl;

        if (currentMode === "Subscription") {
            finalSuccessUrl =
                successUrl + (successUrl.includes("?") ? "&" : "?") + "subscription_payment=1";
        }

        let espadReturnUrl =
            url.toString() + separator +
            "espad_form_id=" + encodeURIComponent(espadFormId) + "&" +
            "success_url=" + encodeURIComponent(finalSuccessUrl) + "&" +
            "cancel_url=" + encodeURIComponent(cancelUrl) + "&" +
            "stripe_metadata_campaign=" + encodeURIComponent(stripeMetadataCampaign) + "&" +
            "stripe_metadata_project=" + encodeURIComponent(stripeMetadataProject) + "&" +
            "stripe_metadata_product=" + encodeURIComponent(stripeMetadataProduct) + "&" +
            "espad_payment_token=" + encodeURIComponent(espadPaymentToken);

        espadReturnUrl += "#payment-form";
    
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
                            city: city,
                            country: country
                        }
                    }
                },
                return_url: espadReturnUrl
            }
        });  

        if (error) {
            if (error.type === "card_error" || error.type === "validation_error") {
                showMessage(error.message);
            } else {
                showMessage(error.message || "An unexpected error has occurred.");
            }
        }   

    } catch (error) {
        console.error(error);
        showMessage("An unexpected error has occurred.");
    }

    setLoading(false);
}

/* ---------------------------------- */
/* Event binding */
/* ---------------------------------- */

function bindPaymentFormSubmitOnce() {
    if (paymentFormHandlerBound) return;

    const paymentForm = document.querySelector("#payment-form");
    if (!paymentForm) return;

    paymentForm.addEventListener("submit", handleSubmit);
    paymentFormHandlerBound = true;
}

function bindOneTimeAmountEvents() {
    const amountRadios = document.querySelectorAll('#prices_box input[name="amount"]');

    amountRadios.forEach((radio) => {
        if (radio.dataset.espadBound === "1") return;

        radio.addEventListener("change", async function(event) {
            const selectedAmount = event.target.value;

            currentItems = [{ amount: parseInt(selectedAmount, 10) }];

            if (currentMode === "OneTime") {
                await renderCheckout("OneTime");
            }
        });

        radio.dataset.espadBound = "1";
    });

    const amountInput = document.getElementById("amountInput");

    if (amountInput && amountInput.dataset.espadBound !== "1") {
        let debounceTimeout;

        amountInput.addEventListener("input", function(event) {
            const valueString = event.target.value;

            if (/[,.]/.test(valueString)) {
                event.target.value = "";
                alert("Please enter a valid number without commas or other characters");
                return;
            }

            if (valueString.length > 5) {
                event.target.value = valueString.slice(0, 5);
                return;
            }

            clearTimeout(debounceTimeout);

            debounceTimeout = setTimeout(async () => {
                const value = valueString.trim();
                const parsed = parseFloat(value);

                if (!isNaN(parsed) && parsed > 0) {
                    const checkedInput = document.querySelector('#espad_page .btn-check:checked');

                    if (checkedInput) {
                        checkedInput.checked = false;
                    }

                    const enteredAmount = Math.round(parsed * 100);

                    const choosedCurrency = document.querySelector("#currency")?.value?.trim() || "USD";
                    const supElement = document.querySelector('#submit #button-text sup');

                    if (supElement) {
                        supElement.innerHTML = (enteredAmount / 100) + " " + choosedCurrency;
                    }

                    currentItems = [{ amount: enteredAmount }];

                    if (currentMode === "OneTime") {
                        await renderCheckout("OneTime");
                    }

                } else if (value !== "") {
                    alert("Please enter a valid number");
                }
            }, 500);
        });

        amountInput.dataset.espadBound = "1";
    }
}

function bindAdvancedModeEvents() { 
    if (baseCheckoutMode !== "Advanced") return;

    const modeRadios = document.querySelectorAll('input[name="advanced_checkout_mode"]');

    modeRadios.forEach((radio) => {
        if (radio.dataset.espadBound === "1") return;

        radio.addEventListener("change", async (event) => {
            const selectedValue = event.target.value;
            const newMode = selectedValue === "subscription" ? "Subscription" : "OneTime";
            
            updatePricesBoxVisibility(newMode);

            if (newMode === currentMode) {
                return;
            }
            
            updateSubmitButtonContent(newMode);

            await renderCheckout(newMode);
            
        });

        radio.dataset.espadBound = "1";
    });
}
  
/* ---------------------------------- */
/* Initialize Checkout */
/* ---------------------------------- */
document.addEventListener("DOMContentLoaded", async () => {
    bindPaymentFormSubmitOnce();
    bindOneTimeAmountEvents();
    bindAdvancedModeEvents();

    currentItems = getOneTimeItems();

    const initialMode = getEffectiveCheckoutMode();
    updateSubmitButtonContent(initialMode);
    await renderCheckout(initialMode);
});


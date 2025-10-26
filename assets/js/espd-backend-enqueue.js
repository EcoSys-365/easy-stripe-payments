document.addEventListener('DOMContentLoaded', function () {
    
    // Check if the "emails disabled" element exists and display a warning alert
    // to inform the admin that automatic email notifications are currently turned off.    
    const emailsDisabled = document.getElementById('espad-emails-disabled');
    
    if ( emailsDisabled ) {    
 
        swal( "Email notifications are disabled" ,  "Emails are not sent automatically after every payment!" ,  "warning" );
        
    }

    // Check if the "emails enabled" element exists and display a success alert
    // to inform the admin that automatic email notifications are currently active.    
    const emailsEnabled = document.getElementById('espad-emails-enabled');
    
    if ( emailsEnabled ) {
        
        swal( "Email notifications are enabled" ,  "Email notifications are triggered after every payment." ,  "success" );
        
    }     
 
    // This script displays a SweetAlert (swal) welcome message when the element
    // with the ID 'espad-welcome-message' exists on the page. 
    // It welcomes users to the Easy Stripe Payments plugin and 
    // offers them two options: 
    //  - "Become a Premium Member" (which opens a registration link in a new tab)
    //  - "Maybe later" (which triggers a follow-up modal with a 20% discount offer).    
    const espadWelcomeMessage = document.getElementById('espad-welcome-message');
    
    if ( espadWelcomeMessage ) {
        
        let registerLink = espadWelcomeMessage.dataset.espadRegisterLink;
        let espadPluginUrl = espadWelcomeMessage.dataset.espadPluginUrl;
         
        swal({
          //title: "",
          content: {
            element: "div",
            attributes: {
              innerHTML: `
                <div class="swal-title" style="font-size: 24px; padding-top: 0px;">Welcome to Easy Stripe Payments!</div>

                <img src="${espadPluginUrl}assets/images/wordpress_rocket.jpg?v=2" alt="Rocket Image" class="espad_lightbox" width="260">
 
                <p style="font-size:16px; line-height:24px; margin-top: 0px;"><b>Thank you for installing Easy Stripe Payments.</b></p>
                &#128161; Tip: The Setup tab includes a step-by-step walkthrough to help you get started in just a few minutes. <br /><br /><b>Wishing you great success and secure payments with Easy Stripe Payments!</b><br /><br />Premium Members receive additional features and access to priority support, while <b class="blue">all core functionality remains freely available.</b><br /><br />&#128274; <b>Want to get even more out of it?</b><br />Become a Premium &#x1F48E; Member for Multiple Stripe Checkouts, Multiple Subscription Payments &amp; Stripe Metadata.
                `
            } 
          },        
          buttons: {
            cancel: {
              text: "Maybe later",
              value: "maybe_later",
              visible: true,
              className: "btn-secondary",
              closeModal: true,
            },
            confirm: {
              text: "Become a Premium Member ⭐",
              value: "premium_member",
              visible: true,
              className: "btn-primary",
              closeModal: true,
            }
          }
        }).then((value) => {

          switch (value) {
            case "premium_member":
                window.open(registerLink, "_blank");  
                break;

            case "maybe_later":
                swal({
                  //title: ``,
                  content: {
                    element: "div",
                    attributes: {
                      innerHTML: `
                        <div class="swal-title" style="font-size: 24px;padding-top: 0px;">Don\'t miss this opportunity - We\'re currently offering <b class='green'>a 20% discount!</b></div>
    
                        <img src="${espadPluginUrl}assets/images/lost_men.jpg?v=2" alt="Lost Men" class="espad_lightbox" width="260">

                        As a Premium &#x1F48E; Member, you\'ll get <b>Priority Support</b> and access to <b class="blue">Multiple Stripe Checkouts</b>, <b>Multiple Subscription Payments</b> &amp; <b class="blue">Stripe Metadata</b>.<br /><br />Membership is quick &amp; easy — register your website for a full year in minutes.
                    `
                    }
                  },
                  buttons: {
                    cancel: {
                      text: "Maybe later",
                      value: "maybe_later",
                      visible: true,
                      className: "btn-secondary",
                      closeModal: true,
                    },
                    confirm: {
                      text: "Become a Premium Member ⭐",
                      value: "premium_member",
                      visible: true,
                      className: "btn-primary",
                      closeModal: true,
                    }
                  }
                }).then((value) => {

                  switch (value) {
                    case "premium_member":
                      window.open(registerLink, "_blank");      
                      break;

                    case "maybe_later":
                          
                      swal( "Demo content installed successfully" , "Check out the Demo Campaign under the Payment Forms tab and see a live preview in the Preview section – you can even run a test payment there to experience the full payment flow, including the confirmation Email." ,  "success" );
                          
                      break;

                    default:
                      break;

                  }
                });
              //console.log("Maybe later clicked");
              break;

            default:
              break;

          }
        });        
        
    }
 
    // Check if the "Copy System Info" button exists and attach a click event listener.
    // When clicked, it selects and copies the content of the system info textarea to the clipboard,
    // and shows a confirmation alert using SweetAlert. Logs a warning if the textarea is not found.    
    const copySystemInfoBtn = document.getElementById('copySystemInfo');

    if ( copySystemInfoBtn ) {
        
        copySystemInfoBtn.addEventListener('click', function () {
            
            const textarea = document.getElementById('systemInfoText');

            if (textarea) {
                
                textarea.select();
                textarea.setSelectionRange(0, 99999); // For mobile devices
                document.execCommand('copy');
                swal("System information copied to clipboard.");
                
            } else {
                
                console.warn('System info textarea not found.');
                
            }
            
        });
        
    }
 
    // Select all FAQ items and attach click event listeners to their questions.
    // When a question is clicked, all other FAQ items are closed, and the clicked item toggles open/closed.
    // This creates an accordion-style behavior for the FAQ section.    
    const faqItems = document.querySelectorAll('.espd-faq-item');
    
    if ( faqItems ) { 

        faqItems.forEach(item => {
            const question = item.querySelector('.espd-faq-question');

            question.addEventListener('click', () => {
                // Close all other FAQ items
                faqItems.forEach(i => {
                    if (i !== item) {
                        i.classList.remove('open');
                    }
                });

                // Toggle the current item
                item.classList.toggle('open');
            });
        }); 
        
    }
    
    // This code checks if the element with ID 'premium-is-active' exists on the page.
    // If it does, a SweetAlert (swal) popup is triggered to inform the user that
    // the premium version is active and the domain is registered. The popup shows
    // a success icon, a custom message, and an "OK" button.    
    const premiumActive = document.getElementById('premium-is-active');
     
    if ( premiumActive ) {
       
        swal({
          title: "Premium is active",
          content: {
            element: "div",
            attributes: {
              innerHTML: `
                Your domain is registered.
              `
            }
          },
          buttons: {
            cancel: {
              text: "OK",
              value: "maybe_later",
              visible: true,
              className: "btn-secondary",
              closeModal: true,
            }
          },
          icon: "success"    
        }).then((value) => {
          // more logic here
        });        
        
    }
  
    // This code checks if the element with ID 'premium-is-inactive' exists on the page.
    // If it does, a SweetAlert (swal) popup is displayed to notify the user that
    // the premium version is not active and the domain is not registered.
    // The popup shows an error icon, a custom message, and an "OK" button.    
    const premiumInactive = document.getElementById('premium-is-inactive');
     
    if ( premiumInactive ) {
        
        swal({
          title: "No Premium – inactive",
          content: {
            element: "div",
            attributes: {
              innerHTML: `
                Your domain is not registered.
              `
            }
          },
          buttons: {
            cancel: {
              text: "OK",
              value: "maybe_later",
              visible: true,
              className: "btn-secondary",
              closeModal: true,
            }
          },
          icon: "error"    
        }).then((value) => {
          // more logic here
        });        
        
    }
    
    // This code checks if the element with ID 'espd-premium-lightbox' exists on the page.
    // If it does, it retrieves two data attributes: 
    // - 'espadRegisterLink' (URL to the registration page) 
    // - 'espadPluginUrl' (base URL of the plugin assets).
    //
    // A SweetAlert popup is displayed to inform the user that their domain is not registered.
    // The popup includes an image, a promotional message, and two buttons: 
    //   1. "Maybe later" – closes the modal or triggers a secondary promo popup with more details.
    //   2. "Become a Premium Member ⭐" – opens the registration link in a new browser tab.
    //
    // If the user selects "Maybe later," a second SweetAlert is shown with another image,
    // a special discount offer, and details about premium membership benefits.
    // Again, the user can choose between "Maybe later" or "Become a Premium Member ⭐ "
    // The confirm button always redirects to the registration page in a new tab.    
    const espdPremiumLightbox = document.getElementById('espd-premium-lightbox');
       
    if ( espdPremiumLightbox ) {
        
        let registerLink = espdPremiumLightbox.dataset.espadRegisterLink;
        let espadPluginUrl = espdPremiumLightbox.dataset.espadPluginUrl;
         
        swal({
          title: "Your domain is not registered",
          content: {
            element: "div",
            attributes: {
              innerHTML: `
                <img src="${espadPluginUrl}assets/images/wordpress_rocket.jpg?v=2" alt="Rocket Image" class="espad_lightbox" width="260">

                <p style='font-size:18px !important; line-height:26px; margin-top: 0px;'>
                    Become a Member &#x1F48E; now &amp;<br />unlock your exclusive discount!
                </p>
                `
            } 
          },        
          buttons: {
            cancel: {
              text: "Maybe later",
              value: "maybe_later",
              visible: true,
              className: "btn-secondary",
              closeModal: true,
            },
            confirm: {
              text: "Become a Premium Member ⭐",
              value: "premium_member",
              visible: true,
              className: "btn-primary",
              closeModal: true,
            }
          }
        }).then((value) => {

          switch (value) {
            case "premium_member":
                window.open(registerLink, "_blank");
                break;

            case "maybe_later":
                swal({
                  //title: ``,
                  content: {
                    element: "div",
                    attributes: {
                      innerHTML: `
                        <div class="swal-title" style="font-size: 24px;padding-top: 0px;">Don\'t miss this opportunity - We\'re currently offering <b class='green'>a 20% discount!</b></div>

                        <img src="${espadPluginUrl}assets/images/lost_men.jpg?v=2" alt="Lost Men" class="espad_lightbox" width="260">

                        As a Premium &#x1F48E; Member, you\'ll get <b>Priority Support</b> and access to <b class="blue">Multiple Stripe Checkouts</b>, <b>Multiple Subscription Payments</b> &amp; <b class="blue">Stripe Metadata</b>.<br /><br />Membership is quick &amp; easy — register your website for a full year in minutes.
                    `
                    } 
                  }, 
                  buttons: {
                    cancel: {
                      text: "Maybe later",
                      value: "maybe_later",
                      visible: true,
                      className: "btn-secondary",
                      closeModal: true,
                    },
                    confirm: {
                      text: "Become a Premium Member ⭐",
                      value: "premium_member",
                      visible: true,
                      className: "btn-primary",
                      closeModal: true,
                    }
                  }
                }).then((value) => {
 
                  switch (value) {
                    case "premium_member":
                      window.open(registerLink, "_blank");
                      break;

                    case "maybe_later":
                      //console.log("Maybe later clicked");
                      break;

                    default:
                      break;

                  }
                });
              //console.log("Maybe later clicked");
              break;

            default:
              break;

          }
        });
        
    }
     
    // Check if the element with ID 'membership-forms-is-false' exists on the page.
    // If it does, show a standard browser alert notifying the user that their domain is not registered.   
    const membershipFormsIsFalse = document.getElementById('membership-forms-is-false');
    
    if ( membershipFormsIsFalse ) {
         
        alert("Your domain is not registered\n\nSign up now and enjoy unlimited forms, unlimited Stripe subscription buttons and your exclusive discount!");
         
    }
       
    // Check if the element with ID 'membership-is-false' exists on the page.
    // If it does, display a SweetAlert (version 1) warning to notify the user that their domain is not registered.   
    const membershipIsFalse = document.getElementById('membership-is-false');

    if ( membershipIsFalse ) { 
          
        swal({
          title: "Your domain is not registered",
          text: "Sign up now and enjoy unlimited forms, unlimited Stripe subscription buttons and your exclusive discount!",
          type: "warning",
          html: true,
          confirmButtonText: "OK",
          footer: '<a href="ESPAD_REGISTER_LINK" target="_blank">Register your domain</a>'
        });
        
    }
       
    /**
     * Dynamically generates and applies custom CSS styles for the Admin 'Preview' payment form.
     *
     * This script:
     * 1. Finds the payment form element (#payment-form).
     * 2. Retrieves dynamic values from hidden input fields:
     *    - "color": defines the accent color used throughout the form.
     *    - "espad_amount_type": determines how the amount input should be displayed.
     * 3. Builds a CSS string that adapts the UI styling (buttons, inputs, progress bars, etc.)
     *    based on the color and amount type.
     * 4. Injects the generated CSS into the document.
     *
     */    
    const paymentForm = document.getElementById('payment-form');

    if ( paymentForm ) {

        const colorInput = paymentForm.querySelector('input[name="color"]');
        const amountTypeInput = paymentForm.querySelector('input[name="espad_amount_type"]');

        const color = colorInput ? colorInput.value : '#0D8889';
        const amountType = amountTypeInput ? amountTypeInput.value : 'fix_amount';
 
        // Dynamic CSS creation
        let customCSS = `
            #espad_page .btn-check:checked + .btn,
            #espad_page :not(.btn-check) + .btn:active,
            #espad_page .btn:first-child:active,
            #espad_page .btn.active,
            #espad_page .btn.show,
            #espad_page #prices_box label.btn:hover,
            #espad_page .progress-bar-fill {
                background-color: ${color} !important;
                color: #fff !important;
            }
            #espad_page .btn-outline-primary,
            #espad_page .progress-label strong {
                color: ${color} !important;
            }
            #espad_page input.form-control:focus {
                border: 2px solid ${color} !important;
                outline: none !important;
                box-shadow: none !important;
            }
            #espad_page #amountInput:focus {
                outline: 2px solid ${color} !important;
                outline-offset: 0;
                box-shadow: none !important;
            }
        `;

        // Amount-type rules
        if (amountType !== 'fix_amount') {
            if (amountType === 'variable_amount') {
                customCSS += `
                    input.btn-check,
                    label.btn-outline-primary {
                        display: none !important;
                    }

                    #amountInput {
                        padding: 13px;
                        border-left: 1px solid #ccc;
                    }
                `;
            } else if (amountType === 'select_amount') {
                customCSS += `
                    #amountInput {
                        display: none;
                    }
                `;
            }
        }

        // Create Style-Tag and attach
        const style = document.createElement('style');
        style.textContent = customCSS;
        document.head.appendChild(style);
    }
   
}); 
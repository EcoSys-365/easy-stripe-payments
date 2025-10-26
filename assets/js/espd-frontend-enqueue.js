document.addEventListener('DOMContentLoaded', function () {
    
    // Show a SweetAlert modal if the payment has failed, prompting the user to contact support
    const espadPaymentFailed = document.getElementById('espad-payment-failed');
        
    if ( espadPaymentFailed ) {
     
        swal({
          title: "Payment failed",
          content: {
            element: "div",
            attributes: {
              innerHTML: `
                <p style="color: #5E5E5E;">
                Your payment has failed. Please contact us.
                </p>
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
    
    // Show a SweetAlert modal if the payment is still pending, informing the user that the page will auto-refresh
    const espadPaymentPending = document.getElementById('espad-payment-pending');
      
    if ( espadPaymentPending ) {
      
        swal({
          title: "Payment is still pending",
          content: {
            element: "div",
            attributes: {
              innerHTML: `
                <p style="color: #5E5E5E;">
                This page will refresh automatically in 10 seconds. Please wait.
                </p>
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
    
   // Show a SweetAlert modal if a recurring payment has failed, prompting the user to contact support
   const espadRecurringPaymentFailed = document.getElementById('espad-recurring-payment-failed');
      
    if ( espadRecurringPaymentFailed ) {
      
        swal({
          title: "Payment failed",
          content: {
            element: "div",
            attributes: {
              innerHTML: `
                <p style="color: #5E5E5E;">
                Your payment has failed. Please contact us.
                </p>
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
     
   // Show a SweetAlert modal when a recurring payment succeeds, notifying the user that their subscription was created successfully    
   const espadRecurringPaymentSuccess = document.getElementById('espad-recurring-payment-success');
      
    if ( espadRecurringPaymentSuccess ) {
      
        swal({
          title: "Payment successful",
          content: {
            element: "div",
            attributes: {
              innerHTML: `
                <p style="color: #5E5E5E;">
                Your subscription has been successfully created
                </p>
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
     
    /**
     * Show Payment Success Modal
     *
     * This JavaScript snippet is responsible for displaying a SweetAlert modal
     * after a successful payment. All values are read from the HTML element
     * with the ID "espad-payment-successful" via data-* attributes.
     *
     * The variables like name, email etc. are defined in payment-process.php
    */
    const espadPaymentSuccessful = document.getElementById('espad-payment-successful');
      
    if ( espadPaymentSuccessful ) {  
        
        let name          = espadPaymentSuccessful.dataset.name;
        let email         = espadPaymentSuccessful.dataset.email;
        let phone         = espadPaymentSuccessful.dataset.phone;
        let address       = espadPaymentSuccessful.dataset.addressStreet;
        let amount        = espadPaymentSuccessful.dataset.amount;
        let currency      = espadPaymentSuccessful.dataset.currency;
        let paymentMethod = espadPaymentSuccessful.dataset.paymentMethod;
  
        // Creating the table dynamically
        let tableRows = `
            <tr>
              <td style="padding: 4px; border: 1px solid #ddd; width: 30%; text-align: left; color: #5E5E5E;">Name:</td>
              <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${name}</td>
            </tr>
            <tr>
              <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">Email:</td>
              <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${email}</td>
            </tr>
        `;

        if (phone) {
            tableRows += `
                <tr>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">Telephone:</td>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${phone}</td>
                </tr>
            `;
        }

        if (address) {
            tableRows += `
                <tr>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">Address:</td>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${address}</td>
                </tr>
            `;
        }

        if (amount && currency) {
            tableRows += `
                <tr>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">Amount:</td>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${amount} ${currency}</td>
                </tr>
            `;
        }

        if (paymentMethod) {
            tableRows += `
                <tr>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">Method:</td>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${paymentMethod}</td>
                </tr>
            `;
        }

        swal({
            title: "Payment successful",
            content: {
                element: "div",
                attributes: {
                    innerHTML: `
                        <table style="width:100%; border-collapse: collapse; font-family: Arial, sans-serif;">
                          <tbody>
                            ${tableRows}
                          </tbody>
                        </table>
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
            // your further logic here
        });
    }   
      
    /**
     * Show Payment Success Reload Modal
     *
     * If the User reloads the page this SweetAlert modal appears
     *
     * This JavaScript snippet is responsible for displaying a SweetAlert modal
     * after a successful payment. All values are read from the HTML element
     * with the ID "espad-payment-successful-reload" via data-* attributes.
     *
     * The variables like name, email etc. are defined in payment-process.php
    */    
    const espadPaymentSuccessfulReload = document.getElementById('espad-payment-successful-reload');
      
    if ( espadPaymentSuccessfulReload ) {  
        
        let name          = espadPaymentSuccessfulReload.dataset.name;
        let email         = espadPaymentSuccessfulReload.dataset.email;
        let phone         = espadPaymentSuccessfulReload.dataset.phone;
        let address       = espadPaymentSuccessfulReload.dataset.addressStreet;
        let amount        = espadPaymentSuccessfulReload.dataset.amount;
        let currency      = espadPaymentSuccessfulReload.dataset.currency;
        let paymentMethod = espadPaymentSuccessfulReload.dataset.paymentMethod;
  
        // Creating the table dynamically
        let tableRows = `
            <tr>
              <td style="padding: 4px; border: 1px solid #ddd; width: 30%; text-align: left; color: #5E5E5E;">Name:</td>
              <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${name}</td>
            </tr>
            <tr>
              <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">Email:</td>
              <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${email}</td>
            </tr>
        `;

        if (phone) {
            tableRows += `
                <tr>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">Telephone:</td>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${phone}</td>
                </tr>
            `;
        }

        if (address) {
            tableRows += `
                <tr>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">Address:</td>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${address}</td>
                </tr>
            `;
        }

        if (amount && currency) {
            tableRows += `
                <tr>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">Amount:</td>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${amount} ${currency}</td>
                </tr>
            `;
        }

        if (paymentMethod) {
            tableRows += `
                <tr>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">Method:</td>
                  <td style="padding: 4px; border: 1px solid #ddd; text-align: left; color: #5E5E5E;">${paymentMethod}</td>
                </tr>
            `;
        }

        swal({
            title: "Payment successful",
            content: {
                element: "div",
                attributes: {
                    innerHTML: `
                        <table style="width:100%; border-collapse: collapse; font-family: Arial, sans-serif;">
                          <tbody>
                            ${tableRows}
                          </tbody>
                        </table>
                        <p style="margin-top: 1em; color: #5E5E5E;">
                          Payment has already been processed
                        </p>
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
            // your further logic here
        });
    }     
    
});

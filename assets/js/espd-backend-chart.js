document.addEventListener('DOMContentLoaded', function () {   
  
    // Initialize the Stripe payouts line chart: 
    // 1. Get the payout data and account ID from the dataset (using an empty array if payoutData is null/empty). 
    // 2. Extract labels (dates) and data (amounts). 
    // 3. Render the Chart.js line chart with tooltips, titles, and axis labels.    
    const payoutChartWelcomeTab = document.getElementById('espad-payout-chart-welcome-tab');
      
    if ( payoutChartWelcomeTab ) { 
         
        // dataset.payoutData is a JSON string; if it is null or empty, use an empty array.
        let payoutChartData = payoutChartWelcomeTab.dataset.payoutData
            ? JSON.parse(payoutChartWelcomeTab.dataset.payoutData)
            : [];

        let accountId = payoutChartWelcomeTab.dataset.accountId || '';

        const labels = payoutChartData.map(item => item.date);
        const data = payoutChartData.map(item => item.amount);        

        const ctx = document.getElementById('payoutChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Stripe Payout Amounts',  
                    data: data,
                    fill: true,
                    borderColor: 'rgba(75, 192, 192, 1)',  
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',  
                    tension: 0.3,
                    pointHoverRadius: 8,  
                    pointHoverBackgroundColor: '#FF5733',  
                    pointHoverBorderColor: '#FF5733',  
                    pointHoverBorderWidth: 3,  
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        mode: 'index',  
                        intersect: false,
                        callbacks: {
                            title: function(tooltipItems) {
                                return 'Payout Date: ' + tooltipItems[0].label;  
                            },
                            label: function(tooltipItem) {
                                return tooltipItem.raw.toFixed(2) + ' USD';  
                            }
                        }
                    }, 
                    title: {
                        display: true,
                        text: 'Stripe Account: ' + accountId,  
                        font: {
                            size: 18,
                            weight: 'bold'
                        },
                        padding: {
                            top: 20,
                            bottom: 20
                        }
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount in USD',  
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date',  
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });        
        
    }
    
});     
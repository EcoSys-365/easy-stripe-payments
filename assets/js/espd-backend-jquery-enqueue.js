jQuery(document).ready(function($) {
 
    // Initialize the Stripe payments table with DataTables for responsive layout and sorting.
    // The table is sorted descending by the 10th column (index 9) by default.

    // Add a click event listener to the "Export CSV" button. When clicked, this function:
    // 1. Loops through all rows and cells of the Stripe payments table.
    // 2. Escapes double quotes and formats each row as a CSV line.
    // 3. Combines all rows into a CSV string and creates a Blob.
    // 4. Triggers a download of the CSV file named "stripe_payments.csv".
    // This allows administrators to export the payment data from the table to a CSV file.    
    $('#espad-table-stripe-payments').DataTable({
        responsive: true,
        order: [[9, 'desc']] // 0 = first column, 'desc' = sorted descending 
    }); 
    
    const exportCsvFile = document.getElementById('export-csv');
      
    if ( exportCsvFile ) {      
 
        document.getElementById("export-csv").addEventListener("click", function () {

            const table = document.getElementById("espad-table-stripe-payments");
            let csv = [];

            for (let row of table.rows) {
            let rowData = [];
            for (let cell of row.cells) {
              let text = cell.innerText.replace(/"/g, '""');
              rowData.push(`"${text}"`);
            }
            csv.push(rowData.join(","));
            }

            const csvString = csv.join("\n");
            const blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
            const link = document.createElement("a");
            link.setAttribute("href", URL.createObjectURL(blob));
            link.setAttribute("download", "stripe_payments.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

        });
        
    }
       
});
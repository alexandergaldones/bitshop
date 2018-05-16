<html>
<head><title>BitPay - Modal CSS invoice demo</title></head>
<body bgcolor="rgb(21,28,111)" textcolor="rgb(255,255,255)">
<button onclick="openInvoice()">Pay Now</button>
<br><br><br>
For more information about BitPay's modal CSS invoice, please see <a href="https://bitpay.com/docs/display-invoice" target="_blank">https://bitpay.com/docs/display-invoice</a>
</body>
<script src="https://bitpay.com/bitpay.js"> </script>
<script>
    function openInvoice() {
        var network = "testnet"
        if (network == "testnet")
            bitpay.setApiUrlPrefix("https://test.bitpay.com")
        else
            bitpay.setApiUrlPrefix("https://bitpay.com")
        bitpay.showInvoice("{{ $invoice_id }}");
    }
</script>
</html>
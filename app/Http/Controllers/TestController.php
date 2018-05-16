<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Bitpay\Bitpay;

class TestController extends Controller
{

    private $bitpay_token = 'BzEq1DVgMxCWYN9BZXaZZLe4Vnn4EcbDQoMMbEeefMUu';



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function create()
    {
        return $this->create04();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create01()
    {
        $privateKey = new \Bitpay\PrivateKey('/Users/xgaldones/bp/bp.pri');

// Generate a random number
        $privateKey->generate();

// You can generate a private key with only one line of code like so
        $privateKey = \Bitpay\PrivateKey::create( config(config('bpay.private_key')) )->generate();

// NOTE: This has overridden the previous $privateKey variable, although its
//       not an issue in this case since we have not used this key for
//       anything yet.

        /**
         * Once we have a private key, a public key is created from it.
         */
        $publicKey = new \Bitpay\PublicKey( config('bpay.public_key') );

// Inject the private key into the public key
        $publicKey->setPrivateKey($privateKey);

// Generate the public key
        $publicKey->generate();

// NOTE: You can again do all of this with one line of code like so:
//       `$publicKey = \Bitpay\PublicKey::create('/tmp/bitpay.pub')->setPrivateKey($privateKey)->generate();`

        /**
         * Now that you have a private and public key generated, you will need to store
         * them somewhere. This optioin is up to you and how you store them is up to
         * you. Please be aware that you MUST store the private key with some type
         * of security. If the private key is comprimised you will need to repeat this
         * process.
         */

        /**
         * It's recommended that you use the EncryptedFilesystemStorage engine to persist your
         * keys. You can, of course, create your own as long as it implements the StorageInterface
         */
        $storageEngine = new \Bitpay\Storage\EncryptedFilesystemStorage( config('bpay.filestorage_password') );
        $storageEngine->persist($privateKey);
        $storageEngine->persist($publicKey);
    }


    public function create02()
    {
        $storageEngine = new \Bitpay\Storage\EncryptedFilesystemStorage('mypassword');
        $privateKey = $storageEngine->load('/Users/xgaldones/bp/bp.pri');
        $publicKey = $storageEngine->load('/Users/xgaldones/bp/bp.pub');

        /**
         * Create the client, there's a lot to it and there are some easier ways, I am
         * showing the long form here to show how various things are injected into the
         * client.
         */
        $client = new \Bitpay\Client\Client();

        /**
         * The network is either livenet or testnet. You can also create your
         * own as long as it implements the NetworkInterface. In this example
         * we will use testnet
         */
        $network = new \Bitpay\Network\Testnet();

        /**
         * The adapter is what will make the calls to BitPay and return the response
         * from BitPay. This can be updated or changed as long as it implements the
         * AdapterInterface
         */
        $adapter = new \Bitpay\Client\Adapter\CurlAdapter();

        /**
         * Now all the objects are created and we can inject them into the client
         */
        $client->setPrivateKey($privateKey);
        $client->setPublicKey($publicKey);
        $client->setNetwork($network);
        $client->setAdapter($adapter);

        /**
         * Visit https://test.bitpay.com/api-tokens and create a new pairing code. Pairing
         * codes can only be used once and the generated code is valid for only 24 hours.
         */
        $pairingCode = 'dKVa772';

        /**
         * Currently this part is required, however future versions of the PHP SDK will
         * be refactor and this part may become obsolete.
         */
        $sin = \Bitpay\SinKey::create()->setPublicKey($publicKey)->generate();
        /**** end ****/

        try {
            $token = $client->createToken(
                array(
                    'pairingCode' => $pairingCode,
                    'label' => 'You can insert a label here',
                    'id' => (string)$sin,
                )
            );
        } catch (\Exception $e) {
            /**
             * The code will throw an exception if anything goes wrong, if you did not
             * change the $pairingCode value or if you are trying to use a pairing
             * code that has already been used, you will get an exception. It was
             * decided that it makes more sense to allow your application to handle
             * this exception since each app is different and has different requirements.
             */
            echo "Pairing failed. Please check whether you're trying to pair a production pairing code on test.";
            $request = $client->getRequest();
            $response = $client->getResponse();
            /**
             * You can use the entire request/response to help figure out what went
             * wrong, but for right now, we will just var_dump them.
             */
            echo (string)$request . PHP_EOL . PHP_EOL . PHP_EOL;
            echo (string)$response . PHP_EOL . PHP_EOL;
            /**
             * NOTE: The `(string)` is include so that the objects are converted to a
             *       user friendly string.
             */

            exit(1); // We do not want to continue if something went wrong
        }

        /**
         * You will need to persist the token somewhere, by the time you get to this
         * point your application has implemented an ORM such as Doctrine or you have
         * your own way to persist data. Such as using a framework or some other code
         * base such as Drupal.
         */
        $persistThisValue = $token->getToken();
        echo 'Token obtained: ' . $persistThisValue . PHP_EOL;
    }

    public function create03()
    {
        // See 002.php for explanation
        $storageEngine = new \Bitpay\Storage\EncryptedFilesystemStorage('mypassword'); // Password may need to be updated if you changed it
        $privateKey    = $storageEngine->load('/Users/xgaldones/bp/bp.pri');
        $publicKey     = $storageEngine->load('/Users/xgaldones/bp/bp.pub');
        $client        = new \Bitpay\Client\Client();
        $network       = new \Bitpay\Network\Testnet();
        $adapter       = new \Bitpay\Client\Adapter\CurlAdapter();
        $client->setPrivateKey($privateKey);
        $client->setPublicKey($publicKey);
        $client->setNetwork($network);
        $client->setAdapter($adapter);
// ---------------------------
        /**
         * The last object that must be injected is the token object.
         */
        $token = new \Bitpay\Token();
        $token->setToken($this->bitpay_token); // UPDATE THIS VALUE
        /**
         * Token object is injected into the client
         */
        $client->setToken($token);
        /**
         * This is where we will start to create an Invoice object, make sure to check
         * the InvoiceInterface for methods that you can use.
         */
        $invoice = new \Bitpay\Invoice();
        $buyer = new \Bitpay\Buyer();
        $buyer
            ->setEmail('alexander.galdones@gmail.com');
// Add the buyers info to invoice
        $invoice->setBuyer($buyer);
        /**
         * Item is used to keep track of a few things
         */
        $item = new \Bitpay\Item();
        $item
            ->setCode('skuNumber1111')
            ->setDescription('General Description of Item 2')
            ->setPrice('2.99');
        $invoice->setItem($item);
        /**
         * BitPay supports multiple different currencies. Most shopping cart applications
         * and applications in general have defined set of currencies that can be used.
         * Setting this to one of the supported currencies will create an invoice using
         * the exchange rate for that currency.
         *
         * @see https://test.bitpay.com/bitcoin-exchange-rates for supported currencies
         */
        $invoice->setCurrency(new \Bitpay\Currency('USD'));
// Configure the rest of the invoice
        $invoice
            ->setOrderId('OrderIdFromYourSystem12345')
            // You will receive IPN's at this URL, should be HTTPS for security purposes!
            ->setNotificationUrl('https://bitshop.ph/bitpay/callback');
        /**
         * Updates invoice with new information such as the invoice id and the URL where
         * a customer can view the invoice.
         */
        try {
            $client->createInvoice($invoice);
        } catch (\Exception $e) {
            $request  = $client->getRequest();
            $response = $client->getResponse();
            echo (string) $request.PHP_EOL.PHP_EOL.PHP_EOL;
            echo (string) $response.PHP_EOL.PHP_EOL;
            exit(1); // We do not want to continue if something went wrong
        }
        echo 'Invoice "'.$invoice->getId().'" created, see '.$invoice->getUrl().PHP_EOL;
    }


    public function create04()
    {
        $storageEngine = new \Bitpay\Storage\EncryptedFilesystemStorage('mypassword'); // Password may need to be updated if you changed it
        $privateKey    = $storageEngine->load('/Users/xgaldones/bp/bp.pri');
        $publicKey     = $storageEngine->load('/Users/xgaldones/bp/bp.pub');
        $client        = new \Bitpay\Client\Client();
        $network       = new \Bitpay\Network\Testnet();
        $adapter       = new \Bitpay\Client\Adapter\CurlAdapter();
        $client->setPrivateKey($privateKey);
        $client->setPublicKey($publicKey);
        $client->setNetwork($network);
        $client->setAdapter($adapter);
        // ---------------------------
        /**
         * The last object that must be injected is the token object.
         */
        $token = new \Bitpay\Token();
        $token->setToken($this->bitpay_token); // UPDATE THIS VALUE
        /**
         * Token object is injected into the client
         */
        $client->setToken($token);
        /**
         * This is where we will start to create an Invoice object, make sure to check
         * the InvoiceInterface for methods that you can use.
         */
        $invoice = new \Bitpay\Invoice();
        $buyer = new \Bitpay\Buyer();
        $buyerEmail = "alexander.galdones@gmail.com";
        $buyer
            ->setEmail($buyerEmail);
        // Add the buyers info to invoice
        $invoice->setBuyer($buyer);
        /**
         * Item is used to keep track of a few things
         */
        $item = new \Bitpay\Item();
        $item
            ->setCode('skuNumber111121222')
            ->setDescription('General Description of Item1223')
            ->setPrice('3.99');
        $invoice->setItem($item);
        /**
         * BitPay supports multiple different currencies. Most shopping cart applications
         * and applications in general have defined set of currencies that can be used.
         * Setting this to one of the supported currencies will create an invoice using
         * the exchange rate for that currency.
         *
         * @see https://test.bitpay.com/bitcoin-exchange-rates for supported currencies
         */
        $invoice->setCurrency(new \Bitpay\Currency('USD'));
        // Configure the rest of the invoice
        $invoice
            ->setOrderId('OrderIdFromYourSystem1234435101212')
            // You will receive IPN's at this URL, should be HTTPS for security purposes!
            ->setNotificationUrl('https://bitshop.ph/bitpay/callback');
        /**
         * Updates invoice with new information such as the invoice id and the URL where
         * a customer can view the invoice.
         */
        try {
            $client->createInvoice($invoice);
        } catch (\Exception $e) {
            $request  = $client->getRequest();
            $response = $client->getResponse();
            echo (string) $request.PHP_EOL.PHP_EOL.PHP_EOL;
            echo (string) $response.PHP_EOL.PHP_EOL;
            exit(1); // We do not want to continue if something went wrong
        }

        return view('hey', ['invoice_id' => $invoice->getId()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }



    public function codrops(){
        return view('tests.codrops');
    }
}

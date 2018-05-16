<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateStockTicker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stocks updater';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

         // create curl resource
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, "http://pse.tools/api/cache/market-depth?symbol=MRC");

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);


        $resp = json_decode($output, true) ;
        $price = $resp['data'][0]['ask_price'];

        // close curl resource to free up system resources
        curl_close($ch);

	$arr = [
            'frames' =>
                [
                    'text' =>  $price,
                    'icon' => 'a19171',
                ]
            ];
	$data_string = json_encode($arr); 

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,"https://developer.lametric.com/api/v1/dev/widget/update/com.lametric.3ec409e8ffc6bf3232eeb7644c1b8fdd/1");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
curl_setopt($ch, CURLOPT_POSTFIELDS, '{
    "frames": [
        {
            "text": ' . $price . ',
            "icon": "19335",
            "index": 0
        }
    ]
}');    
curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
    "Accept: application/json",
    "X-Access-Token: NjkxZWVlZjEwYjI4NTllYjRlNjZmN2FkODczN2ExNDRjMzE2NDBkNDUxOTUyZjk4NmE2NzM0YWYxNDkzZDMyNA==",
    "Cache-Control: no-cache"
    )
);                                                                                                                   
                                                                                                                     
$result = curl_exec($ch);

/*
shell_exec('
curl -X POST \ -H "Accept: application/json" \ -H "X-Access-Token: NjkxZWVlZjEwYjI4NTllYjRlNjZmN2FkODczN2ExNDRjMzE2NDBkNDUxOTUyZjk4NmE2NzM0YWYxNDkzZDMyNA==" \ -H "Cache-Control: no-cache" \
-d \'{
    "frames": [
        {
            "text": "0.65",
            "icon": "19335",
            "index": 0
        }
    ]
}\' \ https://developer.lametric.com/api/v1/dev/widget/update/com.lametric.3ec409e8ffc6bf3232eeb7644c1b8fdd/1
');
*/


// in real life you should use something like:
// curl_setopt($ch, CURLOPT_POSTFIELDS, 
//          http_build_query(array('postvar1' => 'value1')));

// receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//$server_output = curl_exec ($ch);

curl_close ($ch);



    }
}

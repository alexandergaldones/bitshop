<?php

use Illuminate\Http\Request;
use Cache;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::resources([
    'articles' => 'ArticleController'
]);

Route::get('articles', function() {
    // If the Content-Type and Accept headers are set to 'application/json',
    // this will return a JSON structure. This will be cleaned up later.
    return Article::all();
});

Route::get('articles/{id}', function($id) {
    return Article::find($id);
});

Route::post('articles', function(Request $request) {
    return Article::create($request->all);
});

Route::put('articles/{id}', function(Request $request, $id) {
    $article = Article::findOrFail($id);
    $article->update($request->all());

    return $article;
});

Route::delete('articles/{id}', function($id) {
    Article::find($id)->delete();

    return 204;
});

Route::post('token_login', 'Auth\LoginController@login2');
Route::post('token_logout', 'Auth\LoginController@logout');

Route::post('register', 'Auth\RegisterController@register');


Route::get('lametric/test', function() {

    return [
            'frames' =>
                [
                    'text' => 'Api Title',
                    'icon' => 'a19171',
		    //'index' => 0,
		    //'chartData' => [
			//3, 5, 3, 5, 3, 5
		    //	rand(1,8), rand(1,8), rand(1,8), rand(1,8), rand(1,8), rand(1,8), rand(1,8), 
		    //]
                ]
            ];

});


Route::get('lametric/test2', function() {


	$myfile = fopen("/var/www/test/testfile.txt", "w");

	$output = print_r( request()->get('stock-id'), true );
	fwrite($myfile, $output);
	fclose($myfile);
	

	$stocks = [];
	$picks =  explode( ',', request()->get('stock-id') );
	foreach($picks as $pick) {
		$stockCode = trim( substr($pick, 0, strpos($pick, "--")) );
		$stocks[] = $stockCode;	
	}


	 // create curl resource 
        $ch = curl_init(); 
	if(empty(request()->get('stock-id'))) {
		$stocks = ['ALI'];
	} 

        // set url 

	$frames = array();

	foreach($stocks as $key => $stock) {
	        //curl_setopt($ch, CURLOPT_URL, "http://pse.tools/api/cache/market-depth?symbol=" . strtoupper($stock)); 
		curl_setopt($ch, CURLOPT_URL, "https://stocksph.com/feed2/history?symbol=" . strtoupper($stock) . "&resolution=D&from=1515949281&to=1607652109");


	        //return the transfer as a string 
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

	        // $output contains the output string 
        	$output = curl_exec($ch); 


		
		$resp = json_decode($output, true) ;
		$price = end($resp['c']);  //$resp['data'][0]['ask_price'];

		$frame = 
		                [
        	        	    'text' => $price . ' ' . strtoupper($stock),
        		            //'icon' => 'a47',
        		            'icon' => 'i18358',
				    'index' => intval($key),
	                	];

		$frames[] = $frame;
	}

        // close curl resource to free up system resources 
        curl_close($ch);      

    return [
            'frames' => 
				$frames
        	    	
	    ]
	    ;
});


Route::get('/lametric/coinsph', function(){
	$myfile = fopen("/var/www/test/testfile.txt", "w");

	$output = print_r( request()->get('request-type'), true );
	fwrite($myfile, $output);
	fclose($myfile);
	 // create curl resource 
        $ch = curl_init(); 
	
	curl_setopt($ch, CURLOPT_URL, "https://quote.coins.ph/v1/markets/BTC-PHP"); 

	//return the transfer as a string 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

	// $output contains the output string 
	$output = curl_exec($ch); 
		
	$resp = json_decode($output, true) ;

	$request_type = '';
	if(request()->get('request-type') == 'Ask Price') {
		$request_type = 'ask';
	} else { // default Bid Price
		$request_type = 'bid';
	}

	$frames = [];

	$frame = 
	[
			'text' => number_format($resp['market'][$request_type]),
			'icon' => '19435',
			'index' => 0 
	];

	$frames[] = $frame;


	if(request()->get('show-spread')) {
		$spread= number_format($resp['market']['ask'] - $resp['market']['bid']);
		$spread_pct = number_format( 100 - (($resp['market']['bid'] / $resp['market']['ask']) * 100) , 2);
		
		$frame  = 
		[
			'text' => $spread_pct . '%',
			'icon' => '19435',
			'index' => 1 
		];

		$frames[] = $frame;

		$frame  = 
		[
			'text' => $spread ,
			'icon' => '19435',
			'index' => 1 
		];

		$frames[] = $frame;
	}

	return [
		'frames' => $frames
	];

});


/*
Route::get('articles', 'ArticleController@index');
Route::get('articles/{id}', 'ArticleController@show');
Route::post('articles', 'ArticleController@store');
Route::put('articles/{id}', 'ArticleController@update');
Route::delete('articles/{id}', 'ArticleController@delete');
*/

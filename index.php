<?php

//	Then I will. A twitter novel generator.
//	Made for NaNoGenMo 2013.
//	Rodrigo Lanas.
//	Contraculto.com

//	Do you even setup.
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/html; charset=utf-8');
require('bin/TwitterAPIExchange.php');
$tweets = array();

//	Needs a Twitter app to use the search API.
//	You should make one and fill in your data.
$settings = array(
	'oauth_access_token' => "787820996-r3j2ewtbznoWW1aJWTLydcb9JrJZibB2PGAQdjv3",
	'oauth_access_token_secret' => "IEt2pU7ewrtwoSXgOa00glMiJh0cS6kApe10cLJE",
	'consumer_key' => "JUxfLG4aQfHJOYcUflLmlQ",
	'consumer_secret' => "ioZpJ4QewjbDC3PasYkPbegXqf6JgAPwcdq2zwwwk40"
);

//	The phrases used for the search. Everything is possible.
$phrases = array('"Then I will"', '"Then I\'ll"', '"Then I might"', '"Then I probably will"', '"Then maybe I will"', '"Then I definitely will"', '"Then I won\'t"');

//	Function Oriented Programming.
function search($query='',$max='') {
	global $twitter;
	$url = 'https://api.twitter.com/1.1/search/tweets.json';
	$getfield = '?result_type=recent&count=100&q='.urlencode($query);
	if (!empty($max)):
		$getfield .= '&max_id='.$max;
	endif;
	$requestMethod = 'GET';
	$feed = $twitter->setGetfield($getfield)
		->buildOauth($url, $requestMethod)
		->performRequest();
	$feed = json_decode($feed);
	if (!empty($feed->statuses)):
		foreach ($feed->statuses as $tweet):
			if (!stripos($tweet->text, 'RT @')): // Twitter returns *lots* of retweets. Not interested in those because CHAOS.
				//	Here we clean the statuses, making it look like proper english.
				$t = stristr($tweet->text, 'then'); // Cut the part we want.
				$t = str_replace(array("\n", "\r", "chr(13)",  "\t", "\0", "\x0B"), '', $t); // Linebreaks. This doesn't work but it doesn't matter right now.
				$t = preg_replace('|https?://[a-z\.0-9/]+|i', '', $t); // Links.
				//$t = preg_replace('|#[a-zA-Z0-9_]|', '', $t); // Hashtags.
				$t = preg_replace('/[^\00-\255]+/u', '', $t); // Non-english stuff, like emoji.
				$t = str_replace('"', '', $t); // Quotes.
				$t = str_replace('  ', ' ', $t); // Double spaces.
				$t = trim($t, '.,;:?!\'"()[]{} '); // Trim.
				$t = str_replace(' i ', ' I ', ucfirst(strtolower($t))); // And capitalize.
				if ($t !== $query && !stripos($t, '#')):
					$tweets[] = $t;
				endif;
				$ids[] = $tweet->id;
			endif;
		endforeach;
	endif;
	//	Return findings.
	if (!empty($tweets)):
		$data['tweets'] = $tweets;
		$maximum = end($ids);
		$data['max'] = $maximum;
	else:
		$data['tweets'] = '';
		$data['max'] = '';
	endif;
	return $data;
}

//	Get the tweets.
$twitter = new TwitterAPIExchange($settings);
foreach ($phrases as $p):
	$i = 0;
	$data = search($p);
	if (!empty($data['tweets'])):
		foreach($data['tweets'] as $t):
			array_push($tweets, $t);
		endforeach;
	endif;
	$max = $data['max'];
	while (!empty($data['tweets']) && count($data['tweets']) > 1):
		$data = search($p,$max);
		if (!empty($data['tweets'])):
			foreach($data['tweets'] as $t):
				array_push($tweets, $t);
			endforeach;
		endif;
		if ($i >= 2) {break;}
		$max = $data['max'];
		$i++;
	endwhile;
endforeach;

//	Shuffle them.
shuffle($tweets);

//	And display our novel.
//	I'm using a little bit of HTML to make it look pretty, feel free to just plaintext it.
//	Please also note that the first phrase "First I will write some code" is hardcoded for consistency.
?>
<html>
<head>
	<title>Then I will.</title>
	<style>
		body{margin:20px;font-family:sans;line-height:22px}
		#wrapper{width:650px}
		h1{margin-bottom:5px;font-size:20px;}
		h2{margin-bottom:20px;font-size:18px}
		p{font-size:16px}
	</style>
</head>
<body>
	<div id="wrapper">
		<h1>Then I will.</h1>
		<h2>A novel by a script by Rodrigo Lanas.</h2>
		<p>First I will write some code.
			<?php
			foreach ($tweets as $t):
				echo $t;
				if (substr($t, -3, 0) !== '...') { echo'.'; }
				echo ' ';
			endforeach;
			?>
		</p>
	</div>
</body>
</html>

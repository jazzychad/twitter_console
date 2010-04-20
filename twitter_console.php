<?

include("termcolor.php");

/* Twitter credentials */
define("TWITTER_USERNAME", "username");
define("TWITTER_PASSWORD", "password");

/*
 * how many rows to dedicate to each type of message
 * (excluding header row), set to 0 to remove from display
 * all remaining rows on the screen are for Tweets
 */
define("FOLLOW_ROWS", 5);
define("FAVORITE_ROWS", 8);
define("DM_ROWS", 5);
define("MENTION_ROWS", 5);
define("RT_ROWS", 5);

$follow_row = 0;
$favorite_row = 0;
$dm_row = 0;
$mention_row = 0;
$rt_row = 0;


function curlit($url) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "$url");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_USERPWD, TWITTER_USERNAME . ":" . TWITTER_PASSWORD);
  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);

  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}

function userid_to_username($id) {
  /* if you are out of API calls, uncomment the next line to just return the id back... */
  //return $id;
  $aux = curlit("http://api.twitter.com/1/users/show.json?user_id=" . $id);
  $json = json_decode($aux);
  return $json->screen_name;
}

function tweetid_to_user_status($id) {
  /* if you are out of API calls, uncomment the next line to just return the id back... */
  //return array("@user", "tweet_id $id");
  $aux = curlit("http://api.twitter.com/1/statuses/show.json?id=" . $id);
  $json = json_decode($aux);
  $text = str_replace("\n"," ",$json->text);
  return array($json->user->screen_name, $text);
}

function setup_screen() {
  /* does some terminal cursor trickery to setup the screen sections */
  global $favorite_row, $follow_row, $dm_row, $mention_row, $rt_row;

  curclearscreen();
  curhome();
  curfontboldwhite();
  echo "-- TWITTER CONSOLE v0.1 by @jazzychad";
  $toprows = 2;
  curpos(2,1);

  if (FOLLOW_ROWS) {
    echo "==== FOLLOWS =====";
    $follow_row = $toprows + 1;
    $toprows += FOLLOW_ROWS + 1;
    curpos($toprows, 1);
  }

  if (FAVORITE_ROWS) {
    echo "==== FAVORITES =====";
    $favorite_row = $toprows + 1;
    $toprows += FAVORITE_ROWS + 1;
    curpos($toprows, 1);
  }

  if (MENTION_ROWS) {
    echo "===== MENTIONS =====";
    $mention_row = $toprows + 1;
    $toprows += MENTION_ROWS + 1;
    curpos($toprows, 1);
  }

  if (DM_ROWS) {
    echo "===== DMS =====";
    $dm_row = $toprows + 1;
    $toprows += DM_ROWS + 1;
    curpos($toprows, 1);
  }

  if (RT_ROWS) {
    echo "===== RETWEETS =====";
    $rt_row = $toprows + 1;
    $toprows += RT_ROWS + 1;
    curpos($toprows, 1);
  }

  /* rest of screen is for tweets */
  echo "===== TWEETS =====";
  $toprows +=1;

  /* auto scroll the tweet section so no special handling needed */
  curscroll($toprows);
  echocurfontreset();
  curpos($toprows, 1);
}

/* twitter event handlers follow */

function do_follow($source, $target) {
  global $follow_row;
  static $cache = array();
  
  array_push($cache, "@$source followed @$target");
  if (count($cache) > FOLLOW_ROWS) {
    array_shift($cache);
  }

  cursave();
  curpos($follow_row, 1);
  foreach($cache as $line) {
    curclearline();
    echocursetfont("CYAN");
    echo $line . "\n";
    echocurfontreset();
  }
  currestore();

}

function do_rt($source, $orig_source, $text) {
  global $rt_row;
  static $cache = array();

  array_push($cache, array("source" => $source, "orig_source" => $orig_source, "text" => $text));
  if (count($cache) > RT_ROWS) {
    array_shift($cache);
  }

  cursave();
  curpos($rt_row, 1);
  foreach($cache as $info) {
    curclearline();
    echo cursetfont("MAGENTA", "BRIGHT") . $info["source"] . curfontreset() . ": RT @" . cursetfont("WHITE", "BRIGHT") . $info["orig_source"] . curfontreset() . ": " . str_replace("\n", " ", $info["text"]) . "\n";
  }
  currestore();
}

function do_dm($source, $target, $text) {
  global $dm_row;
  static $cache = array();

  array_push($cache, array("source" => $source, "target" => $target, "text" => $text));
  if (count($cache) > DM_ROWS) {
    array_shift($cache);
  }

  cursave();
  curpos($dm_row, 1);
  foreach($cache as $info) {
    curclearline();
    echo "From " . cursetfont("MAGENTA", "BRIGHT") . $info["source"] . curfontreset() . " to " . cursetfont("MAGENTA", "BRIGHT") . $info["target"] . curfontreset() . ": " . cursetfont("WHITE", "BRIGHT") . str_replace("\n", " ", $info["text"]) . "\n" . curfontreset();
  }
  currestore();
}

function do_mention($source, $text) {
  global $mention_row;
  static $cache = array();

  array_push($cache, array("source" => $source, "text" => $text));
  if (count($cache) > MENTION_ROWS) {
    array_shift($cache);
  }

  cursave();
  curpos($mention_row, 1);
  foreach($cache as $info) {
    curclearline();
    echo cursetfont("WHITE", "BRIGHT") . $info["source"] . curfontreset() . ": " . str_replace("\n", " ", $info["text"]) . "\n" . curfontreset();
  }
  currestore();
}


function do_favorite($event, $source, $target, $text) {
  global $favorite_row;
  static $cache = array();

  array_push($cache, array("event" => $event, "text" => "@$source => @$target: $text"));
  if (count($cache) > FAVORITE_ROWS) {
    array_shift($cache);
  }

  cursave();
  curpos($favorite_row, 1);
  foreach($cache as $info) {
    curclearline();
    if ($info["event"] == "favorite") {
      echocursetfont("YELLOW");
      echo $info["text"] . "\n";
      echocurfontreset();
    } else if ($info["event"] == "unfavorite") {
      echocursetfont("RED");
      echo $info["text"] . "\n";
      echocurfontreset();
    }
  }
  currestore();

}




/****** PROGRAM START *******/

setup_screen();

/* listen for messages from the phirehose process */
$msgq = msg_get_queue(6367);

$loopcount = 0;

while (1) {
  $loopcount++;
  $res = msg_receive($msgq, 1, $type, 20000, $msg, true, MSG_IPC_NOWAIT);

  if ($res !== TRUE) {
    usleep(1000000);
    continue;
  }
  if (trim($msg) == "") {
    continue;
  }
  
  if ($json = json_decode($msg)) {
  } else {
    continue;
  }

  if ($json->event == "follow") {

    $source = userid_to_username($json->source->id);
    $target = userid_to_username($json->target->id);

    do_follow($source, $target);

  } else if ($json->event == "favorite") {
    $source = userid_to_username($json->source->id);
    list($target, $text) = tweetid_to_user_status($json->target_object->id);

    do_favorite("favorite", $source, $target, $text);

  } else if ($json->event == "unfavorite") {
    $source = userid_to_username($json->source->id);
    list($target, $text) = tweetid_to_user_status($json->target_object->id);

    do_favorite("unfavorite", $source, $target, $text);

  } else if ($json->event == "retweet") {

    //no-op for now.. will be displayed as text..

  } else if ($json->text != "") {
    /* could be retweet, DM, mention, or regular tweet */

    if (isset($json->retweeted_status)) {

      do_rt($json->user->screen_name, $json->retweeted_status->user->screen_name, $json->retweeted_status->text);

    } else if (isset($json->sender_screen_name)) {
      /* DM */
      do_dm($json->sender_screen_name, $json->recipient_screen_name, $json->text);

    } else {
      /* check for mention */
      if (stripos($json->text, "@" . TWITTER_USERNAME) !== FALSE) {
	/* mention */
	do_mention($json->user->screen_name, $json->text);
      } else {
	/* regular tweet */
	echo cursetfont("WHITE", "BRIGHT") . $json->user->screen_name . curfontreset() . ": " . str_replace("\n", " ", $json->text) . "\n";
	
      }
    }

  } else if (isset($json->delete)) {

    echo cursetfont("RED") . "[delete] user {" . $json->delete->status->user_id . "} deleted tweet {" . $json->delete->status->id . "}\n" . curfontreset();

  } else {
    /* unhandled event format, dump it so we can discover it */
    echo "RAW: -- $msg\n";

  } /* end if "event" == ... */

 } /* end while 1 */


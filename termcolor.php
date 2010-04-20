<?

/*
 * terminal cursor manipulation helper functions
 * see http://www.termsys.demon.co.uk/vtansi.htm for info
 */


$termcolors = array(  "BLACK" =>    0,
		      "RED" =>      1,
		      "GREEN" =>    2,
		      "YELLOW" =>   3,
		      "BLUE" =>     4,
		      "MAGENTA" =>  5,
		      "CYAN" =>     6,
		      "WHITE" =>    7,
		      "RESET" =>    9
		      );

$termattrs = array(  "RESET" =>     0,
		     "BRIGHT" =>    1,
		     "DIM" =>       2, /* or underline */
		     "UNDERLINE" => 3, /* or reverse */
		     "BLINK" =>     4, /* or underline */
		     "REVERSE" =>   7, 
		     "HIDDEN" =>    8, /* or no effect */
		     "DEFAULT" =>   9,
		     "NONE" => ""
		     );

function curclearscreen() { echo "\033[1J"; }
function curscroll($r) { echo "\033[" . $r . "r"; }
function cursave() { echo "\0337"; }
function currestore() { echo "\0338"; }
function curpos($r,$c) { echo "\033[" . $r . ";" . $c . "H"; }
function curup($c = 1) { echo "\033[" . $c . "A"; }
function curdown($c = 1) { echo "\033[" . $c . "B"; }
function curforward($c = 1) { echo "\033[" . $c . "C"; }
function curback($c = 1) { echo "\033[" . $c . "D"; }
function curhome() { curpos(1,1); }
function curfontreset() { return "\033[0m"; }
function echocurfontreset() { echo "\033[0m"; }
function curfontboldwhite() { echo "\033[37;1;49m"; }
function curclearline() { echo "\033[2K"; }

function cursetfont($color, $attr = "NONE", $bg = "RESET") {
  global $termcolors;
  global $termattrs;
  if ($attr == "NONE") {
    return "\033[" . (30 + $termcolors[$color]) . ";" . (40 + $termcolors[$bg]) . "m";
  } else {
    return "\033[" . (30 + $termcolors[$color]) . ";" . $termattrs[$attr] . ";" . (40 + $termcolors[$bg]) . "m";
  }

}

function echocursetfont($color, $attr = "NONE", $bg = "RESET") {
  global $termcolors;
  global $termattrs;

  if ($attr == "NONE") {
    echo "\033[" . (30 + $termcolors[$color]) . ";" . (40 + $termcolors[$bg]) . "m";
  } else {
    echo "\033[" . (30 + $termcolors[$color]) . ";" . $termattrs[$attr] . ";" . (40 + $termcolors[$bg]) . "m";
  }
}

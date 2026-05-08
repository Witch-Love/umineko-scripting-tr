<?php

/**
 * Witch Love simple script patcher
 * 
 * This script replaces matching entries on the dscript file.
 * Usually used for synchronizing the audio with the dialogue.
 */




/**
 * Finds line from the 'key' and replaces the entire line with the new 'value'.
 * 
 * Need to be careful to avoid breaking the game script.
 */
$patches = [
	// umi_hdr
	'loadreg "chiru.file"' => 'loadreg "chiru_tr.file"',
	// 1_12
	'[lv 0*"03"*"10900379"]' =>
		'd [lv 0*"03"*"10900379"][text_speed_t 5]`"....O—`[!w1800]`O—`[!w1800]`O—`[text_speed_t 0][text_fade_t 0][!w1400]`OROSPUUUUUUUUUUUUUUUU!!!"`[\]',
	// 7_2
	'[lv 0*"15"*"70500040"]' =>
		'd [lv 0*"15"*"70500040"][text_speed_t 10][text_fade_t 0]`{n}"{f:5:{m:6:I  f}`[!w600]`{m:15:  you  }`[!w400]`{m:15: insi     }`[!w500]`{m:15:st, }`[!w1000]`{m:15:......}`[!w100]`{m:15:I   ca}`[!w300]`{m:15:n     }{m:15:c }`[!w550]`{m:15:al   l   him}`[!w650]`{m:15:    esp}`[!w300]`{m:15:eci   }`[!w250]`{m:15:ally...}}"`[!w2000][\]',
	'[lv 0*"15"*"70500054"]' =>
		'd [lv 0*"15"*"70500054"][text_speed_t 10][text_fade_t 0]`"{o:0:{e:85:{f:0:{g:0:{m:6:I     }`[!w300]`{m:15:W}`[!w330]`{m:15:I}`[!w200]`{m:15:L}`[!w250]`{m:15:L       }`[!w50]`{m:31:GO      CA}`[!w150]`{m:6:LL     KA}`[!w120]`{m:21:NON}`[!w100]`{m:10:-KUN.}}}}}"`[!w2000][\]',
	'[lv 0*"15"*"70500064"]' =>
		'd [lv 0*"15"*"70500064"][text_speed_t 10][text_fade_t 0]`"{o:0:{e:85:{f:0:{g:0:{m:13:DO     YO}`[!w300]`{m:26: U    WI}`[!w100]`{m:4:SH    T}`[!w150]`{m:41:O    PRO}`[!w100]`{m:71: CEED?}}}}}"`[!w2000][\]',
];




function err($m) {
	echo $m.PHP_EOL;
	die(0);
}

function getUsage() {
	return
	
	'Usage options:'.PHP_EOL.
	'	php script-patch.php script_file language'.PHP_EOL;

}

function main($argc, $argv, $patches) {
	if ($argc < 3) err(getUsage());

	$language = $argv[2];

	if ($language !== 'tr') {
		echo "Language is not Turkish. Skipped patching.".PHP_EOL;
		return;
	}

	$file = $argv[1];
	$lines = file($file);

	$i = 0;
	foreach ($lines as &$line) {
		$i++;
		foreach ($patches as $needle => $replacement) {
			if (strpos($line, $needle) !== false) {
				$line = $replacement . "\n";
				echo "Line patched: ".$i.PHP_EOL;
				break;
			}
		}
	}

	file_put_contents($file, implode('', $lines));

	echo "Finished patching!".PHP_EOL;
}

main($argc, $argv, $patches);

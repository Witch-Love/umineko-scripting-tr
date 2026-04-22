<?php

function err() {
	die(0);
}

function message($type, $message) {
	$is_actions = getenv('GITHUB_ACTIONS') === 'true';

	if ($is_actions) {
		echo "::$type::" . rawurlencode($message);
	} else {
		if ($type == "warning" || $type == "error") {
			fwrite(STDERR, $message);
		} else {
			echo $message;
		}
	}
	echo PHP_EOL;
}

function readDirs($path) {
	$dirHandle = opendir($path);
	$list = [];
	while ($item = readdir($dirHandle)) {
		$newPath = $path."/".$item;
		if ($item != '.' && $item != '..') {
			if (is_dir($newPath)) {
				readDirs($newPath);
			} else {
				array_push($list, "$path/$item");
		 	}
	  	}
	}
	return $list;
}

function main($argc, $argv) {
	if ($argc < 2) err();
	
	ini_set('memory_limit','2048M');
	
	switch ($argv[1]) {
		case 'check':
			if ($argc < 3) err();
			$path = dirname(__FILE__, 2);
			$story_tr = [];
			$story_en = [];
			$exit = false;
			for ($i = 1; $i <= 8; $i++) {
				$story_tr[$i] = readDirs("$path/story/ep$i/{$argv[2]}");
				$story_en[$i] = readDirs("$path/story/ep$i/en");
			}
			for ($i = 1; $i <= count($story_tr); $i++) {
				for ($x = 0; $x < count($story_tr[$i]); $x++) {
					$chapter_tr = $story_tr[$i][$x];
					$chapter_en = $story_en[$i][$x];

					// check : line counts
					$lines_tr = count(file($chapter_tr));
					$lines_en = count(file($chapter_en));
					if ($lines_tr != $lines_en) {
						$msg = "==========================================";
						$msg .= PHP_EOL . "!! ERROR !!";
						$msg .= PHP_EOL . "Line counts don't match";
						$diff = $lines_en - $lines_tr;
						if ($diff > 0) {
							$msg .= PHP_EOL . "( $diff missing line(s) )";
						} else {
							$diff = abs($diff);
							$msg .= PHP_EOL . "( $diff extra line(s) )";
						}
						$msg .= PHP_EOL . "File: $chapter_tr";
						$msg .= PHP_EOL . "Default count: $lines_en";
						$msg .= PHP_EOL . "New count: $lines_tr";
						message("error", $msg);
						$exit = true;
					}

					// check : backticks
					$exp_backtics = "/^`(.*)`$/";

					$handle = fopen($chapter_tr, "r");
					if ($handle) {
						$n = 1;
						while (($line = fgets($handle)) !== false) {
							if (preg_match($exp_backtics, $line) == 0) {
								$msg = "==========================================";
								$msg .= PHP_EOL . "!! ERROR !!";
								$msg .= PHP_EOL . "Missing backtick(s) or wrong line format";
								$msg .= PHP_EOL . "File: $chapter_tr";
								$msg .= PHP_EOL . "Line: $n";
								message("error", $msg);
								$exit = true;
							}
							$n++;
						}
						fclose($handle);
					}

					// check quotation marks
					$handle_tr = fopen($chapter_tr, "r");
					$handle_en = fopen($chapter_en, "r");
					if ($handle_tr && $handle_en) {
						$n = 1;
						while (($line_tr = fgets($handle_tr)) !== false && ($line_en = fgets($handle_en)) !== false) {
							$count_qmarks_tr = substr_count($line_tr, '"');
							$count_qmarks_en = substr_count($line_en, '"');
							if ($count_qmarks_en != $count_qmarks_tr) {
								$msg = "==========================================";
								$msg .= PHP_EOL . "!! WARNING !!";
								$msg .= PHP_EOL . "Quatation marks' count don't match";
								$msg .= PHP_EOL . "File: $chapter_tr";
								$msg .= PHP_EOL . "Line: $n";
								$msg .= PHP_EOL . "Default count: $count_qmarks_en";
								$msg .= PHP_EOL . "New count: $count_qmarks_tr";
								message("warning", $msg);
							}
							$n++;
						}
						fclose($handle_tr);
						fclose($handle_en);
					}
				}
			}
			if ($exit) {
				exit(1);
			}
			message("notice", "All good.");
			break;
		default:
			err();
	}
}

main($argc, $argv);
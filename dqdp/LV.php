<?php declare(strict_types = 1);

namespace dqdp;

class LV
{
	static $kartas = array(
		"nulle", "vien", "div", "trīs", "četr", "piec", "seš", "septiņ", "astoņ", "deviņ",
		"tūksto", "miljon", "miljard", "triljon", "kvadrilion", "kvintiljon"
		);

	# $n: 0-9
	static function num2str($n)
	{
		return LV::$kartas[$n];
	} // num2str

	static function karta($n)
	{
		return LV::$kartas[9 + $n];
	} // num2str

	# $chunk - skaitlis 0..999
	private static function __vardiem_chunk($chunk)
	{
		$rez = '';
		for($r = 0; $r < 3; $r++)
			if(!isset($chunk[$r]))
				$chunk[$r] = 0;

		if($chunk[2] > 0)
		{
			if($chunk[2] > 1)
				$rez .= LV::num2str($chunk[2]);
			$rez .= 'simt ';
		}

		if($chunk[1] == 1)
			if($chunk[0] > 0)
				$rez .= LV::num2str($chunk[0]).'padsmit ';
			else
				$rez .= 'desmit ';
		else {
			if($chunk[1] > 1)
				$rez .= LV::num2str($chunk[1]).'desmit ';
			if($chunk[0]){
				$a = '';
				if($chunk[0] != 3)
					$a = ($chunk[0] == 1) ? 's' : 'i';
				$rez .= LV::num2str($chunk[0]).$a.' ';
			}
		}

		return trim($rez);
	} // __vardiem_chunk

	private static function __beigas(int $int, $b1 = 's', $b2 = 'i')
	{
		$dec = floor($int / 10);
		return (($dec % 10 != 1) && ($int % 10 == 1) ? $b1 : $b2);
		//return (($int != 11) && ($int % 10 == 1) ? 's' : 'i');
	} // __beigas

	# beigas tūkstošiem
	private static function __beigasK(int $int)
	{
		$dec = floor($int / 10);
		return (($dec % 10 != 1) && ($int % 10 == 1) ? 'tis' : 'ši');
		//return (($int != 11) && ($int % 10 == 1) ? 'tis' : 'ši');
	} // __beigas

	static function vardiem($float, $CURR_ID = 'EUR')
	{
		# TODO: citām lokālēm atdalītājs var būt cits, piemēram, ","
		list($part1, $part2) = explode(".", sprintf("%f", $float));
		$chunks = str_split(strrev($part1), 3);
		$part1 = (int)$part1;

		$rez = '';
		$c = count($chunks) - 1;
		do {
			$cip = (int)strrev($chunks[$c]);
			$rez .= LV::__vardiem_chunk($chunks[$c]).' ';
			if($c == 1)
				$rez .= LV::karta($c).LV::__beigasK($cip).' ';
			else
				if($c > 0)
					$rez .= LV::karta($c).LV::__beigas($cip).' ';

		} while($c--);
		$rez = trim($rez);

		$rez = $rez ? $rez : 'nulle';

		$part2 = (int)(('0.'.$part2) * 100);

		if($CURR_ID == 'EUR'){
			return $rez.' euro '.round($part2, 2).' cent'.LV::__beigas($part2);
		} elseif($CURR_ID == 'GBP'){
			return $rez.' sterliņu mārciņa'.LV::__beigas($part1, '', 's').' '.round($part2, 2).' penij'.LV::__beigas($part2);
		} else {
			return $rez.' lat'.LV::__beigas($part1).' '.round($part2, 2).' santīm'.LV::__beigas($part2);
		}
	} // vardiem

} // class LV

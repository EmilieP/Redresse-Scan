<?PHP
$rep='/home/ftp/amphitea';
$rep_saisie='/home/ftp/amphitea_r';
$rep_std ='/tmp/std';
$pointeur = @opendir($rep);

@exec("rm -r $rep_std");
@exec("mkdir $rep_std");

if (!$pointeur) {
	die('probleme');
}
while ($fichier = readdir($pointeur)) {
	if (is_dir("$rep/$fichier") && substr($fichier,0,6)=='ANPERE'  ) {
		@exec("mkdir $rep_saisie/$fichier");
		traite($fichier);
	}
}


function traite($dir) {
	GLOBAL $rep;
	GLOBAL $rep_saisie;
	GLOBAL $rep_std;

	$rep_entree="$rep/$dir" ;
	$pointeur = @opendir($rep_entree);
	if (!$pointeur) {
		die('probleme');
	}
	while ($fichier = readdir($pointeur)) {
		if (is_file("$rep_entree/$fichier") ) {
			$w=exec("identify -format \"%w\" $rep_entree/$fichier ");
			$h=exec("identify -format \"%h\" $rep_entree/$fichier ");
			exec("convert $rep_entree/$fichier -crop $w"."x"."$h+20+20 -threshold 50%  -despeckle  $rep_std/$fichier");
			$cW=recadreImageW("$rep_std/$fichier")+20;
			$cH=recadreImageH("$rep_std/$fichier")+20;
			$w=$w-$cW;
			$h=$h-$cH;
			exec("convert $rep_entree/$fichier -crop $w"."x"."$h+$cW+$cH  -quality 65% $rep_saisie/$dir/$dir-$fichier");
		}
	}
}

function recadreImageW($fichier) {
	$black=0;
	$pas=64;
	$w=0;
	while ($black==0) {
		$w=$w+$pas;
		$ordre="convert $fichier -crop $w"."x"."400+0+300  /tmp/yyy.png";
		exec($ordre);
		$ordre="identify  -verbose /tmp/yyy.png   |sed -n -e '/Histogram:/,/Colormap:/p'  | sed -e 's/.*Histogram://' -e 's/Colormap:.*//'|grep black |cut -d':' -f1 ";
		$black=trim(exec($ordre));
		if ($black>0) {
			if ($pas==1) {
				return ($w-$pas);
			}
			else {
				$black=0;
				$w=$w-$pas;
				$pas=$pas/2;
			}
		}
	}
}

function recadreImageH($fichier) {
	$black=0;
	$pas=32;
	$h=0;
	while ($black==0) {
		$h=$h+$pas;
		$ordre="convert $fichier -crop 100x"."$h+100+0 /tmp/yyy.png";
		echo "$ordre\n";
		exec($ordre);
		$ordre="identify  -verbose /tmp/yyy.png   |sed -n -e '/Histogram:/,/Colormap:/p'  | sed -e 's/.*Histogram://' -e 's/Colormap:.*//'|grep black |cut -d':' -f1 ";
		$black=trim(exec($ordre));
		echo "black=$black\n";
		if ($black>0) {
			if ($pas==1) {
				return ($h-$pas);
			}
			else {
				$black=0;
				$h=$h-$pas;
				$pas=$pas/2;
			}
		}
	}
}



function recadreImageH2($fichier) {
	$black=0;
	$h=1;
	while ($black==0) {
		$ordre="convert $fichier -crop 100x$h+0+0 /tmp/yyy.png";
		echo "$ordre\n";
		exec($ordre);
		$ordre="identify  -verbose /tmp/yyy.png   |sed -n -e '/Histogram:/,/Colormap:/p'  | sed -e 's/.*Histogram://' -e 's/Colormap:.*//'|grep black |cut -d':' -f1 ";
		$black=trim(exec($ordre));
		if ($black==0) {
			$h=$h+1;
		}
		else {
			return $h-1;
		}
	}
}



?>

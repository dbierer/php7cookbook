<?php
// this illustrates differences in foreach() processing
// run this first using PHP 5 then PHP 7
echo 'PHP VERSION: ' . PHP_VERSION;
echo "\nunset() in foreach()\n";
$a = [1, 2, 3];
foreach ($a as $v) {
	printf("%2d\n", $v);
	unset($a[1]);
}

echo "unset() in foreach() after assignment by reference\n";
$a = [1, 2, 3];
$b = &$a;
foreach ($a as $v) {
	printf("%2d\n", $v);
	unset($a[1]);
}

echo "current() in foreach()\n";
$a = [1,2,3];
foreach($a as &$v) {
    printf("%2d - %2d\n", $v, current($a));
}

echo "adding new element in foreach()\n";
$a = [1];
foreach($a as &$v) {
    printf("%2d -\n", $v);
    $a[1]=2;
}

echo "array_pop() in foreach()\n";
$a=[1,2,3,4];
foreach($a as &$v) {
    echo "$v\n";
    array_pop($a);
}

echo "reference in foreach()\n";
$a = [0, 1, 2, 3];
foreach ($a as &$x) {
	foreach ($a as &$y) {
		echo "$x - $y\n";
		if ($x == 0 && $y == 1) {
			unset($a[1]);
			unset($a[2]);
		}
	}
}

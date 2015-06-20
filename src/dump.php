<?php

function dump(...$args) {
	foreach ($args as $arg) {
		dumpSingle($arg);
	}
}

function dumpSingle($arg) {
	if ($arg instanceof Source) {
		foreach ($arg->getSimplified() as $class) {
			dumpSingle($class);
		}

	} else if ($arg instanceof TopClassLike) {
		echo "class {$arg->getFqn()}\n";
		foreach ($arg->getProperties() as $property) {
			$name = $property->props[0]->name;
			echo "- property $name\n";
		}

		foreach ($arg->getMethods() as $method) {
			echo "- method $method->name\n";
		}
	}
}

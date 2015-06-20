<?php

function dump(...$args) {
	foreach ($args as $arg) {
		dumpSingle($arg);
	}
}

function dumpSingle($arg) {
	if ($arg instanceof Source) {
		foreach ($arg->getSimplified() as $class) {
			echo "class $class->name\n";
			foreach ($class->properties as $property) {
				$name = $property->props[0]->name;
				echo "- property $name\n";
			}

			foreach ($class->methods as $method) {
				echo "- method $method->name\n";
			}
		}
	}
}

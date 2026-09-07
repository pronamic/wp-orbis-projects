<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths(
		[
			__DIR__ . '/admin',
			__DIR__ . '/classes',
			__DIR__ . '/includes',
			__DIR__ . '/orbis-projects.php',
			__DIR__ . '/templates',
		]
	)
	->withPhpSets()
	->withTypeCoverageLevel( 0 )
	->withDeadCodeLevel( 0 )
	->withCodeQualityLevel( 0 );

<?php
return [

	'target_php_version' => '7.4',

	// A list of directories that should be parsed for class and
	// method information. After excluding the directories
	// defined in exclude_analysis_directory_list, the remaining
	// files will be statically analyzed for errors.
	//
	// Thus, both first-party and third-party code being used by
	// your application should be included in this list.
	'directory_list' => [
		'.phan/stubs/',
		'lib/',
	],

	'exclude_file_list' => [
		'lib/Less/Tree/Selector.php',
	],

	// A directory list that defines files that will be excluded
	// from static analysis, but whose class and method
	// information should be included.
	'exclude_analysis_directory_list' => [
		'vendor/'
	],

	// A list of plugin files to execute.
	//
	// Documentation about available bundled plugins can be found
	// at https://github.com/phan/phan/tree/v3/.phan/plugins
	//
	'plugins' => [
		// Recommended set from mediawiki-phan-config:
		'AddNeverReturnTypePlugin',
		'DuplicateArrayKeyPlugin',
		'DuplicateExpressionPlugin',
		'LoopVariableReusePlugin',
		'PregRegexCheckerPlugin',
		'RedundantAssignmentPlugin',
		'SimplifyExpressionPlugin',
		'UnreachableCodePlugin',
		'UnusedSuppressionPlugin',
		'UseReturnValuePlugin',

		// Extra ones:
		// 'AlwaysReturnPlugin',
		'DollarDollarPlugin',
		'EmptyStatementListPlugin',
		'PrintfCheckerPlugin',
		'SleepCheckerPlugin',
	],

	'suppress_issue_types' => [
		'PhanDeprecatedFunction',
		'PhanTypeArraySuspiciousNullable',
	],
];

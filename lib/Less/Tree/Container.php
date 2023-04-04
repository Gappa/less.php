<?php
/**
 * @private
 */
class Less_Tree_Container extends Less_Tree_Media {

	public $type = 'Container';

	/**
	 * @see Less_Tree::genCSS
	 */
	public function genCSS( $output ) {
		$output->add( '@container ', $this->currentFileInfo, $this->index );
		$this->features->genCSS( $output );
		Less_Tree::outputRuleset( $output, $this->rules );
	}

	/**
	 * @param Less_Environment $env
	 * @return Less_Tree_Container|Less_Tree_Ruleset
	 * @see less-2.5.3.js#Media.prototype.eval
	 */
	public function compile( $env ) {
		$container = new Less_Tree_Container( [], [], $this->index, $this->currentFileInfo );

		$strictMathBypass = false;
		if ( Less_Parser::$options['strictMath'] === false ) {
			$strictMathBypass = true;
			Less_Parser::$options['strictMath'] = true;
		}

		$container->features = $this->features->compile( $env );

		if ( $strictMathBypass ) {
			Less_Parser::$options['strictMath'] = false;
		}

		$env->containerPath[] = $container;
		$env->containerBlocks[] = $container;

		array_unshift( $env->frames, $this->rules[0] );
		$container->rules = [ $this->rules[0]->compile( $env ) ];
		array_shift( $env->frames );

		array_pop( $env->containerPath );

		return !$env->containerPath ? $container->compileTop( $env ) : $container->compileNested( $env );
	}

	public function emptySelectors() {
		$el = new Less_Tree_Element( '', '&', $this->index, $this->currentFileInfo );
		$sels = [ new Less_Tree_Selector( [ $el ], [], null, $this->index, $this->currentFileInfo ) ];
		$sels[0]->containerEmpty = true;
		return $sels;
	}

	// evaltop
	public function compileTop( $env ) {
		$result = $this;

		if ( count( $env->containerBlocks ) > 1 ) {
			$selectors = $this->emptySelectors();
			$result = new Less_Tree_Ruleset( $selectors, $env->containerBlocks );
			$result->multiContainer = true;
		}

		$env->containerBlocks = [];
		$env->containerPath = [];

		return $result;
	}

	/**
	 * @param Less_Environment $env
	 * @return Less_Tree_Ruleset
	 */
	public function compileNested( $env ) {
		$path = array_merge( $env->containerPath, [ $this ] );
		'@phan-var array<Less_Tree_Container> $path';

		// Extract the media-query conditions separated with `,` (OR).
		foreach ( $path as $key => $p ) {
			$value = $p->features instanceof Less_Tree_Value ? $p->features->value : $p->features;
			$path[$key] = is_array( $value ) ? $value : [ $value ];
		}
		'@phan-var array<array<Less_Tree>> $path';

		// Trace all permutations to generate the resulting media-query.
		//
		// (a, b and c) with nested (d, e) ->
		//	a and d
		//	a and e
		//	b and c and d
		//	b and c and e

		$permuted = $this->permute( $path );
		$expressions = [];
		foreach ( $permuted as $path ) {

			for ( $i = 0, $len = count( $path ); $i < $len; $i++ ) {
				$path[$i] = Less_Parser::is_method( $path[$i], 'toCSS' ) ? $path[$i] : new Less_Tree_Anonymous( $path[$i] );
			}

			for ( $i = count( $path ) - 1; $i > 0; $i-- ) {
				array_splice( $path, $i, 0, [ new Less_Tree_Anonymous( 'and' ) ] );
			}

			$expressions[] = new Less_Tree_Expression( $path );
		}
		$this->features = new Less_Tree_Value( $expressions );

		// Fake a tree-node that doesn't output anything.
		return new Less_Tree_Ruleset( [], [] );
	}

}

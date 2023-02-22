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
		$media = new Less_Tree_Container( [], [], $this->index, $this->currentFileInfo );

		$strictMathBypass = false;
		if ( Less_Parser::$options['strictMath'] === false ) {
			$strictMathBypass = true;
			Less_Parser::$options['strictMath'] = true;
		}

		$media->features = $this->features->compile( $env );

		if ( $strictMathBypass ) {
			Less_Parser::$options['strictMath'] = false;
		}

		$env->mediaPath[] = $media;
		$env->mediaBlocks[] = $media;

		array_unshift( $env->frames, $this->rules[0] );
		$media->rules = [ $this->rules[0]->compile( $env ) ];
		array_shift( $env->frames );

		array_pop( $env->mediaPath );

		return !$env->mediaPath ? $media->compileTop( $env ) : $media->compileNested( $env );
	}

}

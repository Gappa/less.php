<?php
/**
 * @private
 */
class Less_Tree_DetachedRuleset extends Less_Tree {

	/** @var Less_Tree_Ruleset */
	public $ruleset;
	/** @var array|null */
	public $frames;

	public function __construct( $ruleset, $frames = null ) {
		$this->ruleset = $ruleset;
		$this->frames = $frames;
	}

	public function accept( $visitor ) {
		$this->ruleset = $visitor->visitObj( $this->ruleset );
	}

	public function compile( $env ) {
		if ( $this->frames ) {
			$frames = $this->frames;
		} else {
			$frames = $env->frames;
		}
		return new self( $this->ruleset, $frames );
	}

	public function callEval( $env ) {
		if ( $this->frames ) {
			return $this->ruleset->compile( $env->copyEvalEnv( array_merge( $this->frames, $env->frames ) ) );
		}
		return $this->ruleset->compile( $env );
	}
}

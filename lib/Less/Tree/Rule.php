<?php
/**
 * @private
 */
class Less_Tree_Rule extends Less_Tree implements Less_Tree_HasValueProperty {

	public $name;
	/** @var Less_Tree */
	public $value;
	/** @var string */
	public $important;
	public $merge;
	public $index;
	public $inline;
	public $variable;
	public $currentFileInfo;

	/**
	 * @param string|array<Less_Tree_Keyword|Less_Tree_Variable> $name
	 * @param mixed $value
	 * @param null|false|string $important
	 * @param null|false|string $merge
	 * @param int|null $index
	 * @param array|null $currentFileInfo
	 * @param bool $inline
	 */
	public function __construct( $name, $value = null, $important = null, $merge = null, $index = null, $currentFileInfo = null, $inline = false ) {
		$this->name = $name;
		$this->value = ( $value instanceof Less_Tree )
			? $value
			: new Less_Tree_Value( [ $value ] );
		$this->important = $important ? ' ' . trim( $important ) : '';
		$this->merge = $merge;
		$this->index = $index;
		$this->currentFileInfo = $currentFileInfo;
		$this->inline = $inline;
		$this->variable = ( is_string( $name ) && $name[0] === '@' );
	}

	public function accept( $visitor ) {
		$this->value = $visitor->visitObj( $this->value );
	}

	/**
	 * @see less-2.5.3.js#Rule.prototype.genCSS
	 */
	public function genCSS( $output ) {
		$output->add( $this->name . ( Less_Parser::$options['compress'] ? ':' : ': ' ), $this->currentFileInfo, $this->index );
		try {
			$this->value->genCSS( $output );

		} catch ( Less_Exception_Parser $e ) {
			$e->index = $this->index;
			$e->currentFile = $this->currentFileInfo;
			throw $e;
		}
		$output->add( $this->important . ( ( $this->inline || ( Less_Environment::$lastRule && Less_Parser::$options['compress'] ) ) ? "" : ";" ), $this->currentFileInfo, $this->index );
	}

	/**
	 * @see less-2.5.3.js#Rule.prototype.eval
	 * @param Less_Environment $env
	 * @return self
	 */
	public function compile( $env ) {
		$name = $this->name;
		if ( is_array( $name ) ) {
			// expand 'primitive' name directly to get
			// things faster (~10% for benchmark.less):
			if ( count( $name ) === 1 && $name[0] instanceof Less_Tree_Keyword ) {
				$name = $name[0]->value;
			} else {
				$name = $this->CompileName( $env, $name );
			}
		}

		$strictMathBypass = false;
		if ( $name === "font" && !$env->strictMath ) {
			$strictMathBypass = true;
			$env->strictMath = true;
		}

		try {
			$evaldValue = $this->value->compile( $env );

			if ( !$this->variable && $evaldValue instanceof Less_Tree_DetachedRuleset ) {
				throw new Less_Exception_Compiler( "Rulesets cannot be evaluated on a property.", null, $this->index, $this->currentFileInfo );
			}

			if ( Less_Environment::$mixin_stack ) {
				$return = new self( $name, $evaldValue, $this->important, $this->merge, $this->index, $this->currentFileInfo, $this->inline );
			} else {
				$this->name = $name;
				$this->value = $evaldValue;
				$return = $this;
			}

		} catch ( Less_Exception_Parser $e ) {
			if ( !is_numeric( $e->index ) ) {
				$e->index = $this->index;
				$e->currentFile = $this->currentFileInfo;
				$e->genMessage();
			}
			throw $e;
		}

		if ( $strictMathBypass ) {
			$env->strictMath = false;
		}

		return $return;
	}

	public function CompileName( $env, $name ) {
		$output = new Less_Output();
		foreach ( $name as $n ) {
			$n->compile( $env )->genCSS( $output );
		}
		return $output->toString();
	}

	public function makeImportant() {
		return new self( $this->name, $this->value, '!important', $this->merge, $this->index, $this->currentFileInfo, $this->inline );
	}

}

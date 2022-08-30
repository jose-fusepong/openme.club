<?php

namespace FernleafSystems\Wordpress\Services\Utilities;

use FernleafSystems\Wordpress\Services\Services;
use FernleafSystems\Wordpress\Services\Utilities\File\Paths;

class Render {

	const TEMPLATE_ENGINE_TWIG = 0;
	const TEMPLATE_ENGINE_PHP = 1;
	const TEMPLATE_ENGINE_HTML = 2;

	/**
	 * @var array
	 */
	protected $aRenderVars;

	/**
	 * @var array
	 */
	protected $aTemplateRoots;

	/**
	 * @var array
	 */
	protected $aTwigTemplateRoots;

	/**
	 * @var string
	 */
	protected $sTemplate;

	/**
	 * @var int
	 */
	protected $nTemplateEngine;

	public function render() :string {

		switch ( $this->getTemplateEngine() ) {

			case self::TEMPLATE_ENGINE_TWIG :
				$output = $this->renderTwig();
				break;

			case self::TEMPLATE_ENGINE_HTML :
				$output = $this->renderHtml();
				break;

			default:
				$output = $this->renderPhp();
				break;
		}
		return $output;
	}

	private function renderHtml() :string {
		ob_start();
		@include( path_join( $this->getTemplateRoot(), $this->getTemplate() ) );
		return (string)ob_get_clean();
	}

	private function renderPhp() :string {
		if ( count( $this->getRenderVars() ) > 0 ) {
			extract( $this->getRenderVars() );
		}

		$template = path_join( $this->getTemplateRoot(), $this->getTemplate() );
		if ( Services::WpFs()->isFile( $template ) ) {
			ob_start();
			include( $template );
			$contents = ob_get_clean();
		}
		else {
			$contents = 'Error: Template file not found: '.$template;
		}

		return (string)$contents;
	}

	private function renderTwig() :string {
		try {
			return $this->getTwigEnvironment()
						->render( $this->getTemplate(), $this->getRenderVars() );
		}
		catch ( \Exception $e ) {
			return 'Could not render Twig with following Exception: '.$e->getMessage();
		}
	}

	public function display() :self {
		echo $this->render();
		return $this;
	}

	public function clearRenderVars() :self {
		return $this->setRenderVars( [] );
	}

	/**
	 * @return \Twig_Environment
	 */
	private function getTwigEnvironment() {
		$cfg = [
			'debug'            => true,
			'strict_variables' => true,
		];
		if ( @class_exists( 'Twig_Environment' ) ) {
			$env = new \Twig_Environment( new \Twig_Loader_Filesystem( $this->getTemplateRoots() ), $cfg );
		}
		else {
			$env = new \Twig\Environment( new \Twig\Loader\FilesystemLoader( $this->getTemplateRoots() ), $cfg );
		}
		return $env;
	}

	/**
	 * @return string
	 */
	public function getTemplate() {
		$this->sTemplate = Paths::AddExt( (string)$this->sTemplate, $this->getEngineStub() );
		return $this->sTemplate;
	}

	/**
	 * @return int
	 */
	public function getTemplateEngine() {
		if ( !isset( $this->nTemplateEngine )
			 || !in_array( $this->nTemplateEngine, [
				self::TEMPLATE_ENGINE_TWIG,
				self::TEMPLATE_ENGINE_PHP,
				self::TEMPLATE_ENGINE_HTML
			] ) ) {
			$this->nTemplateEngine = self::TEMPLATE_ENGINE_PHP;
		}
		return $this->nTemplateEngine;
	}

	/**
	 * @param string $template
	 */
	public function getTemplateExists( $template = '' ) :bool {
		return strlen( $this->getTemplateRoot( $template ) ) > 0;
	}

	/**
	 * @param string $template
	 * @return string
	 */
	public function getTemplateRoot( $template = '' ) {
		$root = '';
		$template = empty( $template ) ? $this->getTemplate() : $template;
		foreach ( $this->getTemplateRoots() as $possible ) {
			if ( Services::WpFs()->exists( path_join( $possible, $template ) ) ) {
				$root = $possible;
				break;
			}
		}
		return $root;
	}

	public function getTemplateRoots() :array {
		$roots = array_map(
			function ( $root ) {
				return path_join( $root, $this->getEngineStub() );
			},
			$this->getTemplateRootsPlain()
		);
		if ( $this->getTemplateEngine() === self::TEMPLATE_ENGINE_TWIG ) {
			$roots = array_merge( $this->getTwigTemplateRoots(), $roots );
		}
		return array_unique( array_map( 'trailingslashit', array_filter( $roots ) ) );
	}

	/**
	 * @return array
	 */
	private function getTemplateRootsPlain() {
		if ( !is_array( $this->aTemplateRoots ) ) {
			$this->aTemplateRoots = [];
		}
		return $this->aTemplateRoots;
	}

	/**
	 * @return string[]
	 */
	private function getTwigTemplateRoots() {
		return is_array( $this->aTwigTemplateRoots ) ? $this->aTwigTemplateRoots : [];
	}

	public function getRenderVars() :array {
		return is_array( $this->aRenderVars ) ? $this->aRenderVars : [];
	}

	/**
	 * @param array $vars
	 * @return $this
	 */
	public function setRenderVars( $vars ) {
		$this->aRenderVars = $vars;
		return $this;
	}

	/**
	 * @param string $path
	 * @return $this
	 */
	public function setTemplate( $path ) {
		$this->sTemplate = $path;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setTemplateEngineHtml() {
		return $this->setTemplateEngine( self::TEMPLATE_ENGINE_HTML );
	}

	/**
	 * @return $this
	 */
	public function setTemplateEnginePhp() {
		return $this->setTemplateEngine( self::TEMPLATE_ENGINE_PHP );
	}

	/**
	 * @return $this
	 */
	public function setTemplateEngineTwig() {
		return $this->setTemplateEngine( self::TEMPLATE_ENGINE_TWIG );
	}

	/**
	 * @param int $nEngine
	 * @return $this
	 */
	protected function setTemplateEngine( $nEngine ) {
		$this->nTemplateEngine = $nEngine;
		return $this;
	}

	/**
	 * @param string $sPath
	 * @return $this
	 */
	public function setTemplateRoot( $sPath ) {
		if ( !empty( $sPath ) ) {
			$aTemps = $this->getTemplateRootsPlain();
			$aTemps[] = $sPath;
			$this->aTemplateRoots = array_unique( $aTemps );
		}
		return $this;
	}

	/**
	 * @param string $sPath
	 * @return $this
	 */
	public function setTwigTemplateRoot( $sPath ) {
		if ( !empty( $sPath ) ) {
			$aRts = $this->getTwigTemplateRoots();
			$aRts[] = $sPath;
			$this->aTwigTemplateRoots = array_unique( $aRts );
		}
		return $this;
	}

	private function getEngineStub() :string {
		switch ( $this->getTemplateEngine() ) {

			case self::TEMPLATE_ENGINE_TWIG:
				$stub = 'twig';
				break;

			case self::TEMPLATE_ENGINE_HTML:
				$stub = 'html';
				break;

			case self::TEMPLATE_ENGINE_PHP:
			default:
				$stub = 'php';
				break;
		}
		return $stub;
	}
}
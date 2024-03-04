<?php

namespace Changwoo\ShoplicSeminar;

use Changwoo\ShoplicSeminar\Exceptions\ModuleException;
use Changwoo\ShoplicSeminar\Modules\Module;
use Changwoo\ShoplicSeminar\Modules\Shortcodes;
use Closure;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 * @property-read Shortcodes $shortcodes
 */
final class Container {
	private const PREFIX = 'Changwoo\\ShoplicSeminar\\Modules\\';

	private static Container|null $instance = null;

	/**
	 * @var array<string, callable|array>
	 *
	 * Key: module name
	 * Val: callback, or array of constructor arguments
	 */
	private array $args;

	/**
	 * @var array<string, Module>
	 *
	 * Key: module name
	 * Val: Module
	 */
	private array $modules;

	/**
	 * @var array<string, string>
	 *
	 * Key: FQN.
	 * Val: module name, or FQN.
	 */
	private array $resolved;

	/**
	 * @var array<string, object>
	 *
	 * Key: FQN.
	 * Val: instance.
	 */
	private array $store;

	private function __construct() {
		$this->args     = $this->loadModuleArgs();
		$this->resolved = [];
		$this->store    = [];

		$this->resolved[ self::class ] = self::class;
		$this->set( self::class, $this );

		$this->initModules( $this->loadModuleSetup() );
	}

	public function __sleep() {
		throw new RuntimeException();
	}

	public function __wakeup() {
		throw new RuntimeException();
	}

	public static function getInstance(): Container {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function isValidModule( string $fqn ): bool {
		return class_exists( $fqn ) && ( $impl = class_implements( $fqn ) ) && isset( $impl[ Module::class ] );
	}

	public function __get( string $name ) {
		return $this->modules[ $name ] ?? null;
	}

	/**
	 * @throws ModuleException
	 */
	public function __set( string $name, $value ) {
		if ( ! $value instanceof Module ) {
			throw new ModuleException( 'Value should be instance of ' . Module::class . '.' );
		}

		$this->modules[ $name ] = $value;
	}

	public function __isset( string $name ) {
		return isset( $this->modules[ $name ] );
	}

	/**
	 * @template T
	 * @param class-string<T> $identifier
	 *
	 * @return T|object|null
	 */
	public function get( string $identifier ) {
		if ( ! $this->has( $identifier ) ) {
			try {
				$value = $this->instantiate( $identifier );
			} catch ( ModuleException|ReflectionException $e ) {
				$value = null;
			}
			if ( ! isset( $this->store[ $identifier ] ) ) {
				$this->store[ $identifier ] = $value;
			}
		}

		return $this->store[ $identifier ];
	}

	public function has( string $identifier ): bool {
		return array_key_exists( $identifier, $this->store );
	}

	public function set( string $identifier, mixed $value ): void {
		if ( is_null( $value ) ) {
			unset( $this->store[ $identifier ] );

			return;
		}

		$this->store[ $identifier ] = $value;
	}

	/**
	 * Instantiate modules and bind them to filters, and actions.
	 *
	 * @param string $handler
	 * @param string $name
	 *
	 * @return Closure
	 */
	private function bindModule( string $handler, string $name = '' ): Closure {
		return function () use ( $handler, $name ) {
			$split = explode( '@', $handler, 2 );
			$count = count( $split );
			$args  = func_get_args();

			if ( 1 === $count ) {
				$split = array_shift( $split );
				if ( is_callable( $split ) ) {
					return call_user_func_array( $split, $args );
				} else {
					try {
						return $this->instantiateModule( $split, $name );
					} catch ( ModuleException ) {
						// Pass.
					}
				}
			} elseif ( 2 === $count ) {
				$split[0] = $this->instantiateModule( $split[0], $name );
				if ( is_callable( $split ) ) {
					return call_user_func_array( $split, $args );
				}
			}

			throw new ModuleException( "Handler $handler not available!" );
		};
	}

	private function initModules( array $moduleSetup ): void {
		foreach ( $moduleSetup as $hook => $chunks ) {
			foreach ( $chunks as $priority => $setups ) {
				foreach ( $setups as $setup ) {
					$module = '';
					$name   = '';

					if ( is_array( $setup ) ) {
						$module = $setup['module'] ?? '';
						$name   = $setup['name'] ?? '';
					} elseif ( is_string( $setup ) ) {
						$module = $setup;
					}

					if ( $module ) {
						if ( ! $name ) {
							$name = str_replace( [ '/', '\\' ], '', strtolower( $module[0] ) . substr( $module, 1 ) );
						}
						add_action( $hook, $this->bindModule( $module, $name ), (int) $priority );
					}
				}
			}
		}
	}

	/**
	 * @throws ReflectionException
	 * @throws ModuleException
	 */
	private function instantiate( string $fqn ): object {
		// If $fqn were a module, we have located it.
		$name = $this->resolved[ $fqn ] ?? null;

		if ( $name ) {
			// Module can have arguments.
			$args = $this->args[ $name ] ?? [];
		} else {
			// General instances.
			$args = null;
		}

		if ( is_callable( $args ) ) {
			$args = $args( $this, $name );
		} elseif ( is_null( $args ) ) {
			$ref         = new ReflectionClass( $fqn );
			$constructor = $ref->getConstructor();
			$params      = $constructor ? $constructor->getParameters() : [];
			$args        = [];

			foreach ( $params as $param ) {
				$optional = $param->isOptional();
				$typeName = $param->getType()->getName();
				$nullable = $param->getType()->allowsNull();
				$builtin  = $param->getType()->isBuiltin();

				if ( $builtin ) {
					if ( $optional ) {
						$args[] = $param->getDefaultValue();
					} elseif ( $nullable ) {
						$args[] = null;
					} else {
						throw new ModuleException();
					}
					continue;
				}

				// Remove heading '?' for optional parameters.
				if ( $nullable && '?' === $typeName[0] ) {
					$typeName = substr( $typeName, 1 );
				}

				// Is this name already resolved?
				if ( isset( $this->resolved[ $typeName ] ) ) {
					// Support.
					if ( $typeName === $this->resolved[ $typeName ] && $this->has( $typeName ) ) {
						$args[] = $this->get( $typeName );
						continue;
					} elseif ( $typeName !== $this->resolved[ $typeName ] ) {
						// Module.
						$args[] = $this->modules[ $this->resolved[ $typeName ] ];
						continue;
					}
				}

				// Does this name should be resoved now?
				if ( class_exists( $typeName ) ) {
					$args[]                      = $this->get( $typeName );
					$this->resolved[ $typeName ] = $typeName;
				} else {
					throw new ModuleException();
				}
			}
		}

		return new $fqn( ...$args );
	}

	/**
	 * @param string $identifier
	 * @param string $name
	 *
	 * @return Module
	 * @throws ModuleException
	 * @throws ReflectionException
	 */
	private function instantiateModule( string $identifier, string $name ): Module {
		if ( ! isset( $this->$name ) ) {
			$fqn = $this->locateModule( $identifier );

			// Before instantiation.
			$this->resolved[ $fqn ] = $name;

			$instance = $this->instantiate( $fqn );

			$this->$name = $instance;
		}

		return $this->$name;
	}

	private function loadModuleArgs(): array {
		return include plugin_dir_path( SEMINAR_MAIN ) . 'conf/module-args.php';
	}

	private function loadModuleSetup(): array {
		return include plugin_dir_path( SEMINAR_MAIN ) . 'conf/module-setup.php';
	}

	/**
	 * @throws ModuleException
	 */
	private function locateModule( string $identifier ): string {
		$prefix     = self::PREFIX;
		$identifier = str_replace( '/', '\\', $identifier );
		$fqn        = "$prefix$identifier";

		if ( ! self::isValidModule( $fqn ) ) {
			throw new ModuleException( "Identifier $identifier not available." );
		}

		return $fqn;
	}
}

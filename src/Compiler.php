<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Volt;

use Closure;
use Exception as BaseException;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\ViewBaseInterface;
use Phalcon\Volt\Parser\Parser;

use function addslashes;
use function array_key_exists;
use function array_unshift;
use function call_user_func;
use function call_user_func_array;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function implode;
use function is_array;
use function is_object;
use function is_string;
use function lcfirst;
use function method_exists;
use function ord;
use function preg_replace;
use function realpath;
use function serialize;
use function sprintf;
use function str_replace;
use function strlen;
use function strtolower;
use function trigger_error;
use function ucwords;
use function unserialize;
use function var_export;

use const E_USER_DEPRECATED;

/**
 * This class reads and compiles Volt templates into PHP plain code
 *
 *```php
 * $compiler = new \Phalcon\Mvc\View\Engine\Volt\Compiler();
 *
 * $compiler->compile("views/partials/header.volt");
 *
 * require $compiler->getCompiledTemplatePath();
 *```
 */
class Compiler
{
    public const PHVOLT_MODE_CODE    = 1;
    public const PHVOLT_MODE_COMMENT = 2;

    // TODO: add trait InjectionAwareTrait
    /**
     * Modes
     */
    public const PHVOLT_MODE_RAW                   = 0;
    public const PHVOLT_PARSING_FAILED             = 0;
    public const PHVOLT_PARSING_OK                 = 1;
    public const PHVOLT_SCANNER_RETCODE_EOF        = -1;
    public const PHVOLT_SCANNER_RETCODE_ERR        = -2;
    public const PHVOLT_SCANNER_RETCODE_IMPOSSIBLE = -3;
    /**
     * Operators
     */
    public const PHVOLT_T_ADD              = 43; //'+';
    public const PHVOLT_T_ADD_ASSIGN       = 281;
    public const PHVOLT_T_AND              = 266;
    public const PHVOLT_T_ARRAY            = 360;
    public const PHVOLT_T_ARRAYACCESS      = 361;
    public const PHVOLT_T_ASSIGN           = 64; //'=';
    public const PHVOLT_T_AUTOESCAPE       = 317;
    public const PHVOLT_T_BLOCK            = 307;
    public const PHVOLT_T_BREAK            = 320;
    public const PHVOLT_T_CACHE            = 314;
    public const PHVOLT_T_CALL             = 325;
    public const PHVOLT_T_CASE             = 412;
    public const PHVOLT_T_CBRACKET_CLOSE   = 125; //'}';
    public const PHVOLT_T_CBRACKET_OPEN    = 123; //'{';
    public const PHVOLT_T_CLOSE_DELIMITER  = 331;
    public const PHVOLT_T_CLOSE_EDELIMITER = 333;
    public const PHVOLT_T_COLON            = 277;
    public const PHVOLT_T_COMMA            = 269;
    public const PHVOLT_T_CONCAT           = 126; //'~';
    public const PHVOLT_T_CONTINUE         = 319;
    public const PHVOLT_T_DECR             = 280;
    public const PHVOLT_T_DEFAULT          = 413;
    public const PHVOLT_T_DEFINED          = 312;
    public const PHVOLT_T_DIV              = 47; //'/';
    public const PHVOLT_T_DIV_ASSIGN       = 284;
    public const PHVOLT_T_DO               = 316;
    public const PHVOLT_T_DOT              = 46; //'.';
    public const PHVOLT_T_DOUBLE           = 259;
    public const PHVOLT_T_ECHO             = 359;
    public const PHVOLT_T_ELSE             = 301;
    public const PHVOLT_T_ELSEFOR          = 321;
    public const PHVOLT_T_ELSEIF           = 302;
    public const PHVOLT_T_EMPTY            = 380;
    public const PHVOLT_T_EMPTY_STATEMENT  = 358;
    public const PHVOLT_T_ENCLOSED         = 356;
    public const PHVOLT_T_ENDAUTOESCAPE    = 318;
    public const PHVOLT_T_ENDBLOCK         = 308;
    public const PHVOLT_T_ENDCACHE         = 315;
    public const PHVOLT_T_ENDCALL          = 326;
    public const PHVOLT_T_ENDFOR           = 305;
    public const PHVOLT_T_ENDIF            = 303;
    public const PHVOLT_T_ENDMACRO         = 323;
    public const PHVOLT_T_ENDRAW           = 401;
    public const PHVOLT_T_ENDSWITCH        = 414;
    public const PHVOLT_T_EQUALS           = 272;
    public const PHVOLT_T_EVEN             = 381;
    public const PHVOLT_T_EXPR             = 354;
    public const PHVOLT_T_EXTENDS          = 310;
    public const PHVOLT_T_FALSE            = 262;
    /**
     * Special Tokens
     */
    public const PHVOLT_T_FCALL        = 350;
    public const PHVOLT_T_FOR          = 304;
    public const PHVOLT_T_GREATER      = 62; //'>';
    public const PHVOLT_T_GREATEREQUAL = 271;
    public const PHVOLT_T_IDENTICAL    = 274;
    public const PHVOLT_T_IDENTIFIER   = 265;
    /**
     * Reserved words
     */
    public const PHVOLT_T_IF      = 300;
    public const PHVOLT_T_IGNORE  = 257;
    public const PHVOLT_T_IN      = 309;
    public const PHVOLT_T_INCLUDE = 313;
    public const PHVOLT_T_INCR    = 279;
    /**
     * Literals & Identifiers
     */
    public const PHVOLT_T_INTEGER        = 258;
    public const PHVOLT_T_IS             = 311;
    public const PHVOLT_T_ISEMPTY        = 386;
    public const PHVOLT_T_ISEVEN         = 387;
    public const PHVOLT_T_ISITERABLE     = 391;
    public const PHVOLT_T_ISNUMERIC      = 389;
    public const PHVOLT_T_ISODD          = 388;
    public const PHVOLT_T_ISSCALAR       = 390;
    public const PHVOLT_T_ISSET          = 363;
    public const PHVOLT_T_ITERABLE       = 385;
    public const PHVOLT_T_LESS           = 60; //'<';
    public const PHVOLT_T_LESSEQUAL      = 270;
    public const PHVOLT_T_MACRO          = 322;
    public const PHVOLT_T_MINUS          = 368;
    public const PHVOLT_T_MOD            = 37; //'%';
    public const PHVOLT_T_MUL            = 42; //'*';
    public const PHVOLT_T_MUL_ASSIGN     = 283;
    public const PHVOLT_T_NOT            = 33; //'!';
    public const PHVOLT_T_NOTEQUALS      = 273;
    public const PHVOLT_T_NOTIDENTICAL   = 275;
    public const PHVOLT_T_NOT_IN         = 367;
    public const PHVOLT_T_NOT_ISEMPTY    = 392;
    public const PHVOLT_T_NOT_ISEVEN     = 393;
    public const PHVOLT_T_NOT_ISITERABLE = 397;
    public const PHVOLT_T_NOT_ISNUMERIC  = 395;
    public const PHVOLT_T_NOT_ISODD      = 394;
    public const PHVOLT_T_NOT_ISSCALAR   = 396;
    public const PHVOLT_T_NOT_ISSET      = 362;
    public const PHVOLT_T_NULL           = 261;
    public const PHVOLT_T_NUMERIC        = 383;
    public const PHVOLT_T_ODD            = 382;
    /**
     * Delimiters
     */
    public const PHVOLT_T_OPEN_DELIMITER    = 330;
    public const PHVOLT_T_OPEN_EDELIMITER   = 332;
    public const PHVOLT_T_OR                = 267;
    public const PHVOLT_T_PARENTHESES_CLOSE = 41; //')';
    public const PHVOLT_T_PARENTHESES_OPEN  = 40; //'(';
    public const PHVOLT_T_PIPE              = 124; //'|';
    public const PHVOLT_T_PLUS              = 369;
    public const PHVOLT_T_POW               = 278;
    public const PHVOLT_T_QUALIFIED         = 355;
    public const PHVOLT_T_QUESTION          = 63; //'?';
    public const PHVOLT_T_RANGE             = 276;
    public const PHVOLT_T_RAW               = 400;
    public const PHVOLT_T_RAW_FRAGMENT      = 357;
    public const PHVOLT_T_RESOLVED_EXPR     = 364;
    public const PHVOLT_T_RETURN            = 327;
    public const PHVOLT_T_SBRACKET_CLOSE    = 91; //']';
    public const PHVOLT_T_SBRACKET_OPEN     = 93; //'[';
    public const PHVOLT_T_SCALAR            = 384;
    public const PHVOLT_T_SET               = 306;
    public const PHVOLT_T_SLICE             = 365;
    public const PHVOLT_T_STRING            = 260;
    public const PHVOLT_T_SUB               = 45; //'-';
    public const PHVOLT_T_SUB_ASSIGN        = 282;
    /**
     * switch-case statement
     */
    public const PHVOLT_T_SWITCH  = 411;
    public const PHVOLT_T_TERNARY = 366;
    public const PHVOLT_T_TRUE    = 263;
    public const PHVOLT_T_WITH    = 324;

    /**
     * @var bool
     */
    protected bool $autoescape   = false;

    /**
     * @var int
     */
    protected int $blockLevel   = 0;

    /**
     * @var array|null
     *
     * TODO: Make array only?
     */
    protected ?array $blocks = null;

    /**
     * @var string|null
     */
    protected ?string $compiledTemplatePath;

    /**
     * @var DiInterface|null
     */
    protected ?DiInterface $container = null;

    /**
     * @var string|null
     */
    protected ?string $currentBlock = null;

    /**
     * @var string|null
     */
    protected ?string $currentPath = null;

    /**
     * @var int
     */
    protected int $exprLevel = 0;

    /**
     * @var bool
     */
    protected bool $extended = false;
    /**
     * @var array|bool
     *
     * TODO: Make it always array
     */
    protected array | bool $extendedBlocks;
    /**
     * @var array
     */
    protected array $extensions = [];
    /**
     * @var array
     */
    protected array $filters = [];
    /**
     * @var array
     */
    protected array $forElsePointers = [];
    /**
     * @var int
     */
    protected int $foreachLevel = 0;
    /**
     * @var array
     */
    protected array $functions = [];

    /**
     * @var int
     */
    protected int $level = 0;

    /**
     * @var array
     */
    protected array $loopPointers = [];

    /**
     * @var array
     */
    protected array $macros = [];

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var string
     */
    protected string $prefix = "";

    /**
     * @param ViewBaseInterface|null $view
     */
    public function __construct(
        protected ?ViewBaseInterface $view = null
    ) {
    }

    /**
     * Registers a Volt's extension
     *
     * @param object $extension
     *
     * @return Compiler
     */
    public function addExtension(object $extension): Compiler
    {
        /**
         * Initialize the extension
         */
        if (method_exists($extension, 'initialize')) {
            $extension->initialize($this);
        }

        $this->extensions = $extension;

        return $this;
    }

    /**
     * Register a new filter in the compiler
     *
     * @param string $name
     * @param        $definition
     *
     * @return Compiler
     */
    public function addFilter(string $name, $definition): Compiler
    {
        $this->filters[$name] = $definition;

        return $this;
    }

    /**
     * Register a new function in the compiler
     *
     * @param string $name
     * @param        $definition
     *
     * @return Compiler
     */
    public function addFunction(string $name, $definition): Compiler
    {
        $this->functions[$name] = $definition;

        return $this;
    }

    /**
     * Resolves attribute reading
     *
     * @param array $expr
     *
     * @return string
     * @throws BaseException
     */
    public function attributeReader(array $expr): string
    {
        $exprCode = '';
        $left     = $expr['left'];

        if ($left['type'] == static::PHVOLT_T_IDENTIFIER) {
            $variable = $left['value'];

            /**
             * Check if the variable is the loop context
             */
            if ($variable === 'loop') {
                $level                      = $this->foreachLevel;
                $exprCode                   .= '$' . $this->getUniquePrefix() . $level . 'loop';
                $this->loopPointers[$level] = $level;
            } else {
                /**
                 * Services registered in the dependency injector container are
                 * available always
                 */
                if (gettype($this->container) === 'object' && $this->container->has($variable)) {
                    $exprCode .= '$this->' . $variable;
                } else {
                    $exprCode .= '$' . $variable;
                }
            }
        } else {
            $leftCode = $this->expression($left);
            $leftType = $left['type'];

            if ($leftType != static::PHVOLT_T_DOT && $leftType != static::PHVOLT_T_FCALL) {
                $exprCode .= $leftCode;
            } else {
                $exprCode .= $leftCode;
            }
        }

        $exprCode .= '->';
        $right    = $expr['right'];

        if ($right['type'] == static::PHVOLT_T_IDENTIFIER) {
            $exprCode .= $right['value'];
        } else {
            $exprCode .= $this->expression($right);
        }

        return $exprCode;
    }

    /**
     * Compiles a template into a file applying the compiler options
     * This method does not return the compiled path if the template was not compiled
     *
     *```php
     * $compiler->compile("views/layouts/main.volt");
     *
     * require $compiler->getCompiledTemplatePath();
     *```
     *
     * @param string $templatePath
     * @param bool   $extendsMode
     *
     * @return array|mixed|string|null
     * @throws BaseException
     */
    public function compile(string $templatePath, bool $extendsMode = false)
    {
        /**
         * Re-initialize some properties already initialized when the object is
         * cloned
         */
        $this->extended       = false;
        $this->extendedBlocks = false;
        $this->blocks         = null;
        $this->level          = 0;
        $this->foreachLevel   = 0;
        $this->blockLevel     = 0;
        $this->exprLevel      = 0;

        $compilation = null;
        $options     = $this->options;

        /**
         * This makes that templates will be compiled always
         */
        $compileAlways = false;
        if (!isset($options['always'])) {
            if (isset($options['compileAlways'])) {
                $compileAlways = $options['compileAlways'];

                trigger_error(
                    "The 'compileAlways' option is deprecated. Use 'always' instead.",
                    E_USER_DEPRECATED
                );
            }
        } else {
            $compileAlways = $options['always'];
        }

        if (gettype($compileAlways) !== 'boolean') {
            throw new BaseException("'always' must be a bool value");
        }

        /**
         * Prefix is prepended to the template name
         */
        $prefix = $options['prefix'] ?? '';
        if (gettype($prefix) !== 'string') {
            throw new BaseException("'prefix' must be a string");
        }

        /**
         * Compiled path is a directory where the compiled templates will be
         * located
         */
        $compiledPath = '';
        if (!isset($options['path'])) {
            if (isset($options['compiledPath'])) {
                $compiledPath = $options['compiledPath'];

                trigger_error(
                    "The 'compiledPath' option is deprecated. Use 'path' instead.",
                    E_USER_DEPRECATED
                );
            }
        } else {
            $compiledPath = $options['path'];
        }

        /**
         * There is no compiled separator by default
         */
        $compiledSeparator = '';
        if (!isset($options['separator'])) {
            if (isset($options['compiledSeparator'])) {
                $compiledSeparator = $options['compiledSeparator'];

                trigger_error(
                    "The 'compiledSeparator' option is deprecated. Use 'separator' instead.",
                    E_USER_DEPRECATED
                );
            }
        } else {
            $compiledSeparator = $options['separator'];
        }

        if (gettype($compiledSeparator) !== 'string') {
            throw new BaseException("'separator' must be a string");
        }

        /**
         * By default the compile extension is .php
         */
        $compiledExtension = '.php';
        if (!isset($options['extension'])) {
            if (isset($options['compiledExtension'])) {
                $compiledExtension = $options['compiledExtension'];

                trigger_error(
                    "The 'compiledExtension' option is deprecated. Use 'extension' instead.",
                    E_USER_DEPRECATED
                );
            }
        } else {
            $compiledExtension = $options['extension'];
        }

        if (gettype($compiledExtension) !== 'string') {
            throw new BaseException("'extension' must be a string");
        }

        /**
         * Stat option assumes the compilation of the file
         */
        $stat = $options['stat'] ?? true;

        /**
         * Check if there is a compiled path
         */
        if (gettype($compiledPath) === 'string') {
            /**
             * Calculate the template realpath's
             */
            if (!empty($compiledPath)) {
                /**
                 * Create the virtual path replacing the directory separator by
                 * the compiled separator
                 */
                $templateSepPath = $this->prepareVirtualPath(realpath($templatePath), $compiledSeparator);
            } else {
                $templateSepPath = $templatePath;
            }

            /**
             * In extends mode we add an additional 'e' suffix to the file
             */
            if (true === $extendsMode) {
                $compiledTemplatePath = $compiledPath . $prefix . $templateSepPath . $compiledSeparator . 'e' .
                    $compiledSeparator . $compiledExtension;
            } else {
                $compiledTemplatePath = $compiledPath . $prefix . $templateSepPath . $compiledExtension;
            }
        } elseif ($compiledPath instanceof Closure) {
            /**
             * A closure can dynamically compile the path
             */

            $compiledTemplatePath = call_user_func_array(
                $compiledPath,
                [$templatePath, $options, $extendsMode]
            );

            /**
             * The closure must return a valid path
             */
            if (gettype($compiledTemplatePath) !== 'string') {
                throw new BaseException("'path' closure didn't return a valid string");
            }
        } else {
            throw new BaseException("'path' must be a string or a closure");
        }

        /**
         * Compile always must be used only in the development stage
         */
        if (!file_exists($compiledTemplatePath) || true === $compileAlways) {
            /**
             * The file needs to be compiled because it either doesn't exist or
             * needs to compiled every time
             */
            $compilation = $this->compileFile(
                $templatePath,
                $compiledTemplatePath,
                $extendsMode
            );
        } else {
            if (true === $stat) {
                /**
                 * Compare modification timestamps to check if the file
                 * needs to be recompiled
                 */
                if ($this->compareMtime($templatePath, $compiledTemplatePath)) {
                    $compilation = $this->compileFile(
                        $templatePath,
                        $compiledTemplatePath,
                        $extendsMode
                    );
                } elseif ($extendsMode) {
                    /**
                     * In extends mode we read the file that must
                     * contains a serialized array of blocks
                     */
                    $blocksCode = file_get_contents($compiledTemplatePath);
                    if (false === $blocksCode) {
                        throw new BaseException(
                            "Extends compilation file " . $compiledTemplatePath . " could not be opened"
                        );
                    }

                    /**
                     * Unserialize the array blocks code
                     */
                    $compilation = unserialize($blocksCode);
                }
            }
        }

        $this->compiledTemplatePath = $compiledTemplatePath;

        return $compilation;
    }

    /**
     * Compiles a "autoescape" statement returning PHP code
     *
     * @param array $statement
     * @param bool  $extendsMode
     *
     * @return string
     * @throws Exception
     */
    public function compileAutoEscape(array $statement, bool $extendsMode): string
    {
        /**
         * A valid option is required
         */
        if (!isset($statement['enable'])) {
            throw new BaseException('Corrupted statement');
        }

        /**
         * "autoescape" mode
         */
        $oldAutoescape    = $this->autoescape;
        $this->autoescape = $statement['enable'];

        $compilation      = $this->statementList($statement['block_statements'], $extendsMode);
        $this->autoescape = $oldAutoescape;

        return $compilation;
    }

    /**
     * @param array $statement
     * @param bool  $extendsMode
     *
     * @return string
     * @throws Exception
     */
    public function compileCache(array $statement, bool $extendsMode = false): string
    {
        /**
         * @todo Remove this in the next major version
         */
        /**
         * A valid expression is required
         */
        if (!isset($statement['expr'])) {
            throw new BaseException('Corrupt statement: ' . var_export($statement, true));
        }

        /**
         * Cache statement
         */
        $expr        = $statement['expr'];
        $lifetime    = $statement['lifetime'] ?? null;
        $exprCode    = $this->expression($expr);
        $compilation = '<?php $_cache[' . $this->expression($expr) . '] = $this->di->get(\'viewCache\'); ';

        if ($lifetime !== null) {
            $compilation .= '$_cacheKey[' . $exprCode . ']';

            if ($lifetime['type'] == static::PHVOLT_T_IDENTIFIER) {
                $compilation .= '$_cache[' . $exprCode . ']->start(' . $exprCode . ', $' . $lifetime['value'] . '); ';
            } else {
                $compilation .= '$_cache[' . $exprCode . ']->start(' . $exprCode . ', ' . $lifetime['value'] . '); ';
            }
        } else {
            $compilation .= '$_cacheKey[' . $exprCode . '] = $_cache[' . $exprCode . ']->start(' . $exprCode . '); ';
        }

        $compilation .= 'if ($_cacheKey[' . $exprCode . '] === null) { ?>';

        /**
         * Get the code in the block
         */
        $compilation .= $this->statementList($statement['block_statements'], $extendsMode);

        /**
         * Check if the cache has a lifetime
         */
        if ($lifetime !== null) {
            if ($lifetime['type'] == static::PHVOLT_T_IDENTIFIER) {
                $compilation .= '<?php $_cache[' . $exprCode
                    . ']->save(' . $exprCode . ', null, $' . $lifetime['value'] . '); ';
            } else {
                $compilation .= '<?php $_cache[' . $exprCode
                    . ']->save(' . $exprCode . ', null, ' . $lifetime['value'] . '); ';
            }

            $compilation .= '} else { echo $_cacheKey[' . $exprCode . ']; } ?>';
        } else {
            $compilation .= '<?php $_cache[' . $exprCode
                . ']->save(' . $exprCode . '); } else { echo $_cacheKey[' . $exprCode . ']; } ?>';
        }

        return $compilation;
    }

    /**
     * Compiles calls to macros
     *
     * @param array $statement
     * @param bool  $extendsMode
     *
     * @return string
     */
    public function compileCall(array $statement, bool $extendsMode): string
    {
        return '';
    }

    /**
     * Compiles a "case"/"default" clause returning PHP code
     *
     * @throws BaseException
     */
    public function compileCase(array $statement, bool $caseClause = true): string
    {
        if ($caseClause === false) {
            /**
             * "default" statement
             */
            return '<?php default: ?>';
        }

        /**
         * A valid expression is required
         */
        if (!isset($statement['expr'])) {
            throw new BaseException('Corrupt statement: ' . var_export($statement, true));
        }

        /**
         * "case" statement
         */
        return "<?php case " . $this->expression($statement['expr']) . ": ?>";
    }

    /**
     * Compiles a "do" statement returning PHP code
     *
     * @throws BaseException
     */
    public function compileDo(array $statement): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement['expr'])) {
            throw new BaseException('Corrupt statement');
        }

        /**
         * "Do" statement
         */
        return '<?php ' . $this->expression($statement['expr']) . '; ?>';
    }

    /**
     * Compiles a {% raw %}`{{` `}}`{% endraw %} statement returning PHP code
     */
    public function compileEcho(array $statement): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement['expr'])) {
            throw new BaseException('Corrupt statement: ' . var_export($statement, true));
        }

        /**
         * Evaluate common expressions
         */
        $expr     = $statement['expr'];
        $exprCode = $this->expression($expr);

        if ($expr == static::PHVOLT_T_FCALL) {
            if ($this->isTagFactory($expr)) {
                $exprCode = $this->expression($expr, true);
            }

            $name = $expr['name'];
            if ($name == static::PHVOLT_T_IDENTIFIER) {
                /**
                 * super() is a function however the return of this function
                 * must be output as it is
                 */
                if ($name['value'] === 'super') {
                    return $exprCode;
                }
            }
        }

        /**
         * Echo statement
         */
        if (true === $this->autoescape) {
            return '<?= $this->escaper->escapeHtml(' . $exprCode . ')';
        }

        return '<?= ' . $exprCode . ' ?>';
    }

    /**
     * Compiles a "elseif" statement returning PHP code
     *
     * @param array $statement
     *
     * @return string
     * @throws BaseException
     */
    public function compileElseIf(array $statement): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement['expr'])) {
            throw new BaseException('Corrupt statement: ' . var_export($statement, true));
        }

        /**
         * "elseif" statement
         */
        return '<?php } elseif (' . $this->expression($statement['expr']) . ') { ?>';
    }

    /**
     * Compiles a template into a file forcing the destination path
     *
     *```php
     * $compiler->compileFile(
     *     "views/layouts/main.volt",
     *     "views/layouts/main.volt.php"
     * );
     *```
     *
     * @param string $path
     * @param string $compiledPath
     * @param bool   $extendsMode
     *
     * @return string|array
     * @throws Exception
     */
    public function compileFile(string $path, string $compiledPath, bool $extendsMode = false)
    {
        if ($path === $compiledPath) {
            throw new BaseException(
                'Template path and compilation template path cannot be the same'
            );
        }

        /**
         * Check if the template does exist
         */
        if (!file_exists($path)) {
            throw new BaseException('Template file ' . $path . ' does not exist');
        }

        /**
         * Always use file_get_contents instead of read the file directly, this
         * respect the open_basedir directive
         */
        $viewCode = file_get_contents($path);
        if ($viewCode === false) {
            throw new BaseException(
                'Template file ' . $path . ' could not be opened'
            );
        }

        $this->currentPath = $path;
        $compilation       = $this->compileSource($viewCode, $extendsMode);

        /**
         * We store the file serialized if it's an array of blocks
         */
        if (gettype($compilation) === 'array') {
            $finalCompilation = serialize($compilation);
        } else {
            $finalCompilation = $compilation;
        }

        /**
         * Always use file_put_contents to write files instead of write the file
         * directly, this respect the open_basedir directive
         */
        if (false === file_put_contents($compiledPath, $finalCompilation)) {
            throw new BaseException('Volt directory can\'t be written');
        }

        return $compilation;
    }

    /**
     * Generates a 'forelse' PHP code
     *
     * @return string
     */
    public function compileForElse(): string
    {
        $level = $this->foreachLevel;

        if (!isset($this->forElsePointers[$level])) {
            return '';
        }

        $prefix = $this->forElsePointers[$level];
        if (isset($this->loopPointers[$level])) {
            return '<?php $' . $prefix . 'incr++; } if (!$' . $prefix . 'iterated) { ?>';
        }

        return '<?php } if (!$' . $prefix . 'iterated) { ?>';
    }

    /**
     * Compiles a "foreach" intermediate code representation into plain PHP code
     *
     * @throws Exception
     */
    public function compileForeach(array $statement, bool $extendsMode = false): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement['expr'])) {
            throw new BaseException('Corrupted statement');
        }

        $forElse     = null;
        $compilation = '';
        $this->foreachLevel++;
        $prefix = $this->getUniquePrefix();
        $level  = $this->foreachLevel;

        /**
         * prefixLevel is used to prefix every temporal variable
         */
        $prefixLevel = $prefix . $level;

        /**
         * Evaluate common expressions
         */
        $expr     = $statement['expr'];
        $exprCode = $this->expression($expr);

        /**
         * Process the block statements
         */
        $blockStatements = $statement['block_statements'];
        $forElse         = false;

        if (gettype($blockStatements) === 'array') {
            foreach ($blockStatements as $blockStatement) {
                /**
                 * Check if the statement is valid
                 */
                if (!isset($blockStatement['type'])) {
                    break;
                }

                if ($blockStatement['type'] == static::PHVOLT_T_ELSEFOR) {
                    $compilation                   .= '<?php $' . $prefixLevel . 'iterated = false; ?>';
                    $forElse                       = $prefixLevel;
                    $this->forElsePointers[$level] = $forElse;

                    break;
                }
            }
        }

        /**
         * Process statements block
         */
        $code        = $this->statementList($blockStatements, $extendsMode);
        $loopContext = $this->loopPointers;

        /**
         * Generate the loop context for the "foreach"
         */
        if (isset($loopContext[$level])) {
            $compilation .= '<?php $' . $prefixLevel . 'iterator = ' . $exprCode;
            $compilation .= '$' . $prefixLevel . 'incr = 0; ';
            $compilation .= '$' . $prefixLevel . 'loop = new \stdClass(); ';
            $compilation .= '$' . $prefixLevel . 'loop->self = &$' . $prefixLevel . 'loop; ';
            $compilation .= '$' . $prefixLevel . 'loop->length = count($' . $prefixLevel . 'iterator); ';
            $compilation .= '$' . $prefixLevel . 'loop->index = 1; ';
            $compilation .= '$' . $prefixLevel . 'loop->index0 = 1; ';
            $compilation .= '$' . $prefixLevel . 'loop->revindex = $' . $prefixLevel . 'loop->length; ';
            $compilation .= '$' . $prefixLevel . 'loop->revindex0 = $' . $prefixLevel . 'loop->length - 1; ?>';

            $iterator = '$' . $prefixLevel . 'iterator';
        } else {
            $iterator = $exprCode;
        }

        /**
         * Foreach statement
         */
        $variable = $statement['variable'];

        /**
         * Check if a "key" variable needs to be calculated
         */
        if (!empty($statement['key'])) {
            $compilation .= '<?php foreach (' . $iterator . ' as $' . $statement['key'] . ' => $' . $variable . ') { ';
        } else {
            $compilation .= '<?php foreach (' . $iterator . ' as $' . $variable . ') { ';
        }

        /**
         * Check for an "if" expr in the block
         */
        if (!empty($statement['if_expr'])) {
            $compilation .= 'if (' . $this->expression($statement['if_expr']) . ') { ?>';
        } else {
            $compilation .= '?>';
        }

        /**
         * Generate the loop context inside the cycle
         */
        if (isset($loopContext[$level])) {
            $compilation .= '<?php $' . $prefixLevel . 'loop->first = ($' . $prefixLevel . 'incr == 0); ';
            $compilation .= '$' . $prefixLevel . 'loop->index = $'
                . $prefixLevel . 'incr + 1; ';
            $compilation .= '$' . $prefixLevel . 'loop->index0 = $'
                . $prefixLevel . 'incr; ';
            $compilation .= '$' . $prefixLevel . 'loop->revindex = $'
                . $prefixLevel . 'loop->length - $' . $prefixLevel . 'incr; ';
            $compilation .= '$' . $prefixLevel . 'loop->revindex0 = $'
                . $prefixLevel . 'loop->length - ($' . $prefixLevel . 'incr + 1); ';
            $compilation .= '$' . $prefixLevel . 'loop->last = ($'
                . $prefixLevel . 'incr == ($' . $prefixLevel . 'loop->length - 1)); ?>';
        }

        /**
         * Update the forelse var if it's iterated at least one time
         */
        $forElseType = gettype($forElse);
        if ($forElseType === 'string') {
            $compilation .= '<?php $' . $forElse . 'iterated = true; ?>';
        }

        /**
         * Append the internal block compilation
         */
        $compilation .= $code;

        if (isset($statement['if_expr'])) {
            $compilation .= '<?php } ?>';
        }

        if ($forElseType === 'string') {
            $compilation .= '<?php } ?>';
        } else {
            if (isset($loopContext[$level])) {
                $compilation .= '<?php $' . $prefixLevel . 'incr++; } ?>';
            } else {
                $compilation .= '<?php } ?>';
            }
        }

        $this->foreachLevel--;

        return $compilation;
    }

    /**
     * Compiles a 'if' statement returning PHP code
     *
     * @throws BaseException
     */
    public function compileIf(array $statement, bool $extendsMode = false): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement['expr'])) {
            throw new BaseException('Corrupt statement: ' . var_export($statement, true));
        }

        /**
         * Process statements in the "true" block
         */
        $compilation = '<?php if (' . $this->expression($statement['expr']) . ') { ?>'
            . $this->statementList(
                $statement['true_statements'],
                $extendsMode
            );

        /**
         * Check for an "else"/"elseif" block
         */
        if (!empty($statement['false_statements'])) {
            /**
             * Process statements in the "false" block
             */
            $compilation .= '<?php } else { ?>'
                . $this->statementList($statement['false_statements'], $extendsMode);
        }

        $compilation .= '<?php } ?>';

        return $compilation;
    }

    /**
     * Compiles an 'include' statement returning PHP code
     *
     * @throws BaseException
     */
    public function compileInclude(array $statement): string
    {
        /**
         * Include statement
         * A valid expression is required
         */
        if (!isset($statement['path'])) {
            throw new BaseException('Corrupted statement');
        }

        /**
         * Check if the expression is a string
         * If the path is an string try to make an static compilation
         */
        $pathExpr = $statement['path'];
        if ($pathExpr['type'] == 260) {
            /**
             * Static compilation cannot be performed if the user passed extra
             * parameters
             */
            if (!isset($statement['params'])) {
                /**
                 * Get the static path
                 */
                $path      = $pathExpr['value'];
                $finalPath = $this->getFinalPath($path);

                /**
                 * Clone the original compiler
                 * Perform a sub-compilation of the included file
                 * If the compilation doesn't return anything we include the compiled path
                 */
                $subCompiler = clone $this;
                $compilation = $subCompiler->compile($finalPath, false);

                if ($compilation === null) {
                    /**
                     * Use file-get-contents to respect the openbase_dir
                     * directive
                     */
                    $compilation = file_get_contents(
                        $subCompiler->getCompiledTemplatePath()
                    );
                }

                return $compilation;
            }
        }

        /**
         * Resolve the path's expression
         */
        $path = $this->expression($pathExpr);

        /**
         * Use partial
         */
        if (!isset($statement['params'])) {
            return '<?php $this->partial(' . $path . ')';
        }

        return '<?php $this->partial(' . $pathExpr . ', ' . $this->expression($statement['params']) . ')';
    }

    /**
     * Compiles macros
     *
     * @throws BaseException
     */
    public function compileMacro(array $statement, bool $extendsMode): string
    {
        /**
         * A valid name is required
         */
        if (!isset($statement['name'])) {
            throw new BaseException('Corrupted statement');
        }

        /**
         * Check if the macro is already defined
         */
        $name = $statement['name'];
        if (isset($this->macros[$name])) {
            throw new BaseException('Macro "' . $name . '" is already defined');
        }

        /**
         * Register the macro
         */
        $this->macros[$name] = $name;
        $macroName           = '$this->macros[\'' . $name . '\]';
        $code                = '<?php ';

        if (!isset($statement['parameters'])) {
            $code .= $macroName . ' = function() { ?>';
        } else {
            /**
             * Parameters are always received as an array
             */
            $code .= $macroName . ' = function($__p = null) { ';

            foreach ($statement['parameters'] as $position => $parameter) {
                $variableName = $parameter['variable'];

                $code .= 'if (isset($__p[' . $position . '])) { ';
                $code .= '$' . $variableName . ' = $__p[' . $position . '];';
                $code .= ' } else { ';
                $code .= 'if (array_key_exists(\'' . $variableName . '\', $__p)) { ';
                $code .= '$' . $variableName . ' = $__p[\'' . $variableName . '\'];';
                $code .= ' } else { ';

                if (isset($parameter['default'])) {
                    $code .= '$' . $variableName . ' = ' . $this->expression($parameter['default']) . ';';
                } else {
                    $code .= ' throw new \\Phalcon\\Mvc\\View\\Exception(\'Macro "'
                        . $name . '" was called without parameter ' . $variableName . '\'); ';
                }

                $code .= ' } ) ';
            }

            $code .= ' ?>';
        }

        /**
         * Block statements are allowed
         */
        if (isset($statement['block_statements'])) {
            /**
             * Process statements block
             */
            $code .= $this->statementList($statement['block_statements'], $extendsMode) . '<?php }; ';
        } else {
            $code .= '<?php }; ';
        }

        /**
         * Bind the closure to the $this object allowing to call services
         */
        $code .= $macroName . ' = \\Closure::bind(' . $macroName . ', $this); ?>';

        return $code;
    }

    /**
     * Compiles a "return" statement returning PHP code
     *
     * @throws BaseException
     */
    public function compileReturn(array $statement): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement['expr'])) {
            throw new BaseException('Corrupted statement');
        }

        /**
         * "Return" statement
         */
        return '<?php return ' . $this->expression($statement['expr']) . '; ?>';
    }

    /**
     * Compiles a "set" statement returning PHP code
     *
     * @throws BaseException
     */
    public function compileSet(array $statement): string
    {
        /**
         * A valid assignment list is required
         */
        if (!isset($statement['assignments'])) {
            throw new BaseException('Corrupted statement');
        }

        $compilation = '<?php';

        /**
         * A single set can have several assignments
         */
        foreach ($statement['assignments'] as $assignment) {
            $exprCode = $this->expression($assignment['expr']);

            /**
             * Resolve the expression assigned
             */
            $target = $this->expression($assignment['variable']);

            /**
             * Assignment operator
             * Generate the right operator
             */
            switch ($assignment['op']) {
                case static::PHVOLT_T_ADD_ASSIGN:
                    $compilation .= ' ' . $target . ' += ' . $exprCode . ';';
                    break;

                case static::PHVOLT_T_SUB_ASSIGN:
                    $compilation .= ' ' . $target . ' -= ' . $exprCode . ';';
                    break;

                case static::PHVOLT_T_MUL_ASSIGN:
                    $compilation .= ' ' . $target . ' *= ' . $exprCode . ';';
                    break;

                case static::PHVOLT_T_DIV_ASSIGN:
                    $compilation .= ' ' . $target . ' /= ' . $exprCode . ';';
                    break;

                default:
                    $compilation .= ' ' . $target . ' = ' . $exprCode . ';';
                    break;
            }
        }

        $compilation .= ' ?>';

        return $compilation;
    }

    /**
     * Compiles a Volt source code returning a PHP plain version
     *
     * @throws Exception
     */
    public function compileSource(string $viewCode, bool $extendsMode = false): string|array
    {
        /**
         * Enable autoescape globally
         */
        if (array_key_exists('autoescape', $this->options)) {
            if (gettype($this->options['autoescape']) !== 'boolean') {
                throw new Exception("'autoescape' must be bool");
            }

            $this->autoescape = $this->options['autoescape'];
        }

        $parser       = new Parser($viewCode);
        $intermediate = $parser->parseView($this->currentPath);
        $compilation  = $this->statementList($intermediate, $extendsMode);

        /**
         * Check if the template is extending another
         */
        if (true === $this->extended) {
            /**
             * Multiple-Inheritance is allowed
             */
            $finalCompilation = true === $extendsMode ? [] : null;
            foreach ($this->extendedBlocks as $name => $block) {
                /**
                 * If name is a string then is a block name
                 */
                if (true === is_string($name)) {
                    if (isset($this->blocks[$name])) {
                        /**
                         * The block is set in the local template
                         */
                        $localBlock         = $this->blocks[$name];
                        $this->currentBlock = $name;
                        $blockCompilation   = $this->statementList($localBlock);
                    } else {
                        if (true === is_array($block)) {
                            /**
                             * The block is not set local only in the extended
                             * template
                             */
                            $blockCompilation = $this->statementList($block);
                        } else {
                            $blockCompilation = $block;
                        }
                    }

                    if (true === $extendsMode) {
                        $finalCompilation[$name] = $blockCompilation;
                    } else {
                        $finalCompilation .= $blockCompilation;
                    }
                } else {
                    /**
                     * Here the block is an already compiled text
                     */
                    if (true === $extendsMode) {
                        $finalCompilation[] = $block;
                    } else {
                        $finalCompilation .= $block;
                    }
                }
            }

            return $finalCompilation;
        }

        if (true === $extendsMode) {
            /**
             * In extends mode we return the template blocks instead of the
             * compilation
             */
            return $this->blocks;
        }

        return $compilation;
    }

    /**
     * Compiles a template into a string
     *
     *```php
     * echo $compiler->compileString({% raw %}'{{ "hello world" }}'{% endraw %});
     *```
     *
     * @throws Exception
     */
    public function compileString(string $viewCode, bool $extendsMode = false): string
    {
        $this->currentPath = 'eval code';

        return $this->compileSource($viewCode, $extendsMode);
    }

    /**
     * Compiles a 'switch' statement returning PHP code
     *
     * @throws Exception
     */
    public function compileSwitch(array $statement, bool $extendsMode = false): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement['expr'])) {
            throw new BaseException('Corrupt statement: ' . var_export($statement, true));
        }

        $expr = $statement['expr'];

        /**
         * Process statements in the "true" block
         */
        $compilation = '<?php switch (' . $this->expression($expr) . '): ?>';

        /**
         * Check for a "case"/"default" blocks
         */
        if (isset($statement['case_clauses'])) {
            $caseClauses = $statement['case_clauses'];
            $lines       = $this->statementList($caseClauses, $extendsMode);

            /**
             * Any output (including whitespace) between a switch statement and
             * the first case will result in a syntax error. This is the
             * responsibility of the user. However, we can clear empty lines and
             * whitespace here to reduce the number of errors.
             *
             * @link http://php.net/control-structures.alternative-syntax
             */
            if (strlen($lines) !== 0) {
                /**
                 * (*ANYCRLF) - specifies a newline convention: (*CR), (*LF) or (*CRLF)
                 * \h+ - 1+ horizontal whitespace chars
                 * $ - end of line (now, before CR or LF)
                 * m - multiline mode on ($ matches at the end of a line).
                 * u - unicode
                 *
                 * g - global search, - is implicit with preg_replace(), you don't need to include it.
                 */
                $lines = preg_replace(
                    '/(*ANYCRLF)^\h+|\h+$|(\h){2,}/mu',
                    '',
                    $lines
                );
            }

            $compilation .= $lines;
        }

        $compilation .= '<?php endswitch; ?>';

        return $compilation;
    }

    /**
     * Resolves an expression node in an AST volt tree
     *
     * @throws Exception
     */
    final public function expression(array $expr, bool $doubleQuotes = false): string
    {
        $leftCode = '';
        $exprCode = null;
        $this->exprLevel++;

        /**
         * Check if any of the registered extensions provide compilation for
         * this expression
         */
        $extensions = $this->extensions;

        while (true) {
            if (gettype($extensions) === 'array') {
                /**
                 * Notify the extensions about being resolving an expression
                 */
                $exprCode = $this->fireExtensionEvent(
                    'resolveExpression',
                    [$expr]
                );

                if (gettype($exprCode) === 'string') {
                    break;
                }
            }

            if (!isset($expr['type'])) {
                $items = [];

                foreach ($expr as $singleExpr) {
                    $singleExprCode = $this->expression($singleExpr['expr'], $doubleQuotes);

                    if (isset($singleExpr['name'])) {
                        $items[] = '\'' . $singleExpr['name'] . '\' => ' . $singleExprCode;
                    } else {
                        $items[] = $singleExprCode;
                    }
                }

                $exprCode = implode(', ', $items);

                break;
            }

            $type = $expr['type'];

            /**
             * Attribute reading needs special handling
             */
            if ($type == static::PHVOLT_T_DOT) {
                $exprCode = $this->attributeReader($expr);

                break;
            }

            /**
             * Left part of expression is always resolved
             */
            if (isset($expr['left'])) {
                $leftCode = $this->expression($expr['left'], $doubleQuotes);
            }

            /**
             * Operator "is" also needs special handling
             */
            if ($type == static::PHVOLT_T_IS) {
                $exprCode = $this->resolveTest($expr['right'], $leftCode);

                break;
            }

            /**
             * We don't resolve the right expression for filters
             */
            if ($type == self::PHVOLT_T_PIPE) {
                $exprCode = $this->resolveFilter($expr['right'], $leftCode);

                break;
            }

            /**
             * From here, right part of expression is always resolved
             */
            $exprCode  = null;
            $rightCode = isset($expr['right']) ? $this->expression($expr['right'], $doubleQuotes) : '';

            switch ($type) {
                case static::PHVOLT_T_NOT:
                    $exprCode = '!' . $rightCode;
                    break;

                case static::PHVOLT_T_MUL:
                    $exprCode = $leftCode . ' * ' . $rightCode;
                    break;

                case static::PHVOLT_T_ADD:
                    $exprCode = $leftCode . ' + ' . $rightCode;
                    break;

                case static::PHVOLT_T_SUB:
                    $exprCode = $leftCode . ' - ' . $rightCode;
                    break;

                case static::PHVOLT_T_DIV:
                    $exprCode = $leftCode . ' / ' . $rightCode;
                    break;

                case 37:
                    $exprCode = $leftCode . ' % ' . $rightCode;
                    break;

                case static::PHVOLT_T_LESS:
                    $exprCode = $leftCode . ' < ' . $rightCode;
                    break;

                case 61:
                case 62:
                    $exprCode = $leftCode . ' > ' . $rightCode;
                    break;

                case 126:
                    $exprCode = $leftCode . ' . ' . $rightCode;
                    break;

                case 278:
                    $exprCode = 'pow(' . $leftCode . ', ' . $rightCode . ')';
                    break;

                case static::PHVOLT_T_ARRAY:
                    $exprCode = isset($expr['left']) ? '[' . $leftCode . ']' : [];
                    break;

                case 258:
                case 259:
                case static::PHVOLT_T_RESOLVED_EXPR:
                    $exprCode = $expr['value'];
                    break;

                case static::PHVOLT_T_STRING:
                    if ($doubleQuotes === false) {
                        $exprCode = '\'' . str_replace('\'', '\\\'', $expr['value']) . '\'';
                    } else {
                        $exprCode = '"' . $expr['value'] . '"';
                    }
                    break;

                case static::PHVOLT_T_NULL:
                    $exprCode = 'null';
                    break;

                case static::PHVOLT_T_FALSE:
                    $exprCode = 'false';
                    break;

                case static::PHVOLT_T_TRUE:
                    $exprCode = 'true';
                    break;

                case static::PHVOLT_T_IDENTIFIER:
                    $exprCode = '$' . $expr['value'];
                    break;

                case static::PHVOLT_T_AND:
                    $exprCode = $leftCode . ' && ' . $rightCode;
                    break;

                case 267:
                    $exprCode = $leftCode . ' || ' . $rightCode;
                    break;

                case static::PHVOLT_T_LESSEQUAL:
                    $exprCode = $leftCode . ' <= ' . $rightCode;
                    break;

                case 271:
                    $exprCode = $leftCode . ' >= ' . $rightCode;
                    break;

                case 272:
                    $exprCode = $leftCode .= ' == ' . $rightCode;
                    break;

                case 273:
                    $exprCode = $leftCode .= ' != ' . $rightCode;
                    break;

                case 274:
                    $exprCode = $leftCode .= ' === ' . $rightCode;
                    break;

                case 275:
                    $exprCode = $leftCode .= ' !== ' . $rightCode;
                    break;

                case static::PHVOLT_T_RANGE:
                    $exprCode = 'range(' . $leftCode . ', ' . $rightCode . ')';
                    break;

                case static::PHVOLT_T_FCALL:
                    $exprCode = $this->functionCall($expr, $doubleQuotes);
                    break;

                case static::PHVOLT_T_ENCLOSED:
                    $exprCode = '(' . $leftCode . ')';
                    break;

                case static::PHVOLT_T_SLICE:
                    /**
                     * Evaluate the start part of the slice
                     */
                    $startCode = isset($expr['start']) ? $this->expression($expr['start'], $doubleQuotes) : 'null';

                    /**
                     * Evaluate the end part of the slice
                     */
                    $endCode = isset($expr['end']) ? $this->expression($expr['end'], $doubleQuotes) : 'null';

                    $exprCode = '$this->slice(' . $leftCode . ', ' . $startCode . ', ' . $endCode . ')';
                    break;

                case static::PHVOLT_T_NOT_ISSET:
                    $exprCode = '!isset(' . $leftCode . ')';
                    break;

                case static::PHVOLT_T_ISSET:
                    $exprCode = 'isset(' . $leftCode . ')';
                    break;

                case static::PHVOLT_T_NOT_ISEMPTY:
                    $exprCode = '!empty(' . $leftCode . ')';
                    break;

                case static::PHVOLT_T_ISEMPTY:
                    $exprCode = 'empty(' . $leftCode . ')';
                    break;

                case static::PHVOLT_T_NOT_ISEVEN:
                    $exprCode = '!(((' . $leftCode . ') % 2) == 0)';
                    break;

                case static::PHVOLT_T_ISEVEN:
                    $exprCode = '(((' . $leftCode . ') % 2) == 0)';
                    break;

                case static::PHVOLT_T_NOT_ISODD:
                    $exprCode = '!(((' . $leftCode . ') % 2) != 0)';
                    break;

                case static::PHVOLT_T_ISODD:
                    $exprCode = '(((' . $leftCode . ') % 2) != 0)';
                    break;

                case static::PHVOLT_T_NOT_ISNUMERIC:
                    $exprCode = '!is_numeric(' . $leftCode . ')';
                    break;

                case static::PHVOLT_T_ISNUMERIC:
                    $exprCode = 'is_numeric(' . $leftCode . ')';
                    break;

                case static::PHVOLT_T_NOT_ISSCALAR:
                    $exprCode = '!is_scalar(' . $leftCode . ')';
                    break;

                case static::PHVOLT_T_ISSCALAR:
                    $exprCode = 'is_scalar(' . $leftCode . ')';
                    break;

                case static::PHVOLT_T_NOT_ISITERABLE:
                    $exprCode = '!(is_array(' . $leftCode . ') || (' . $leftCode . ') instanceof Traversable)';
                    break;

                case static::PHVOLT_T_ISITERABLE:
                    $exprCode = '(is_array(' . $leftCode . ') || (' . $leftCode . ') instanceof Traversable)';
                    break;

                case static::PHVOLT_T_IN:
                    $exprCode = '$this->isIncluded(' . $leftCode . ', ' . $rightCode . ')';
                    break;

                case static::PHVOLT_T_NOT_IN:
                    $exprCode = '!$this->isIncluded(' . $leftCode . ', ' . $rightCode . ')';
                    break;

                case static::PHVOLT_T_TERNARY:
                    $exprCode = '(' . $this->expression($expr['ternary'], $doubleQuotes)
                        . ' ? ' . $leftCode . ' : ' . $rightCode . ')';
                    break;

                case static::PHVOLT_T_MINUS:
                    $exprCode = '-' . $rightCode;
                    break;

                case static::PHVOLT_T_PLUS:
                    $exprCode = '+' . $rightCode;
                    break;

                default:
                    throw new BaseException(
                        'Unknown expression ' . $type . ' in ' . $expr['file'] . ' on line ' . $expr['line']
                    );
            }

            break;
        }

        $this->exprLevel--;

        return $exprCode;
    }

    /**
     * Fires an event to registered extensions
     *
     * @param string     $name
     * @param array|null $arguments
     *
     * @return string|void
     */
    final public function fireExtensionEvent(string $name, ?array $arguments = null)
    {
        $extensions = $this->extensions;

        if (gettype($extensions) !== 'array') {
            return;
        }

        foreach ($extensions as $extension) {
            /**
             * Check if the extension implements the required event name
             */
            if (method_exists($extension, $name)) {
                if ($arguments !== null) {
                    $status = call_user_func_array(
                        [$extension, $name],
                        $arguments
                    );
                } else {
                    $status = call_user_func([$extension, $name]);
                }

                /**
                 * Only string statuses means the extension processes
                 * something
                 */
                if (gettype($status) === 'string') {
                    return $status;
                }
            }
        }
    }

    /**
     * Resolves function intermediate code into PHP function calls
     *
     * @throws Exception
     */
    public function functionCall(array $expr, bool $doubleQuotes = false): string
    {
        $code          = null;
        $funcArguments = null;

        $arguments = isset($expr['arguments']) ? $this->expression($expr['arguments'], $doubleQuotes) : '';

        $nameExpr = $expr['name'];
        $nameType = $nameExpr['type'];

        /**
         * Check if it's a single function
         */
        if ($nameType == static::PHVOLT_T_IDENTIFIER) {
            $name = $nameExpr['value'];

            /**
             * Check if any of the registered extensions provide compilation for
             * this function
             */
            $extensions = $this->extensions;
            if (gettype($extensions) === 'array') {
                /**
                 * Notify the extensions about being compiling a function
                 */
                $code = $this->fireExtensionEvent(
                    'compileFunction',
                    [$name, $arguments, $funcArguments]
                );

                if (gettype($code) === 'string') {
                    return $code;
                }
            }

            /**
             * Check if it's a user defined function
             */
            $functions = $this->functions;
            if (gettype($functions) === 'array') {
                if (isset($functions[$name])) {
                    $definition     = $functions[$name];
                    $definitionType = gettype($definition);

                    /**
                     * Use the string as function
                     */
                    if ($definitionType === 'string') {
                        return $definition . '(' . $arguments . ')';
                    }

                    /**
                     * Execute the function closure returning the compiled
                     * definition
                     */
                    if ($definitionType === 'object') {
                        if ($definition instanceof Closure) {
                            return call_user_func_array(
                                $definition,
                                [$arguments, $funcArguments]
                            );
                        }
                    }

                    throw new BaseException(
                        'Invalid definition for user function "'
                        . $name . '" in ' . $expr['file'] . ' on line ' . $expr['line']
                    );
                }
            }

            /**
             * This function includes the previous rendering stage
             */
            if ($name === 'get_content' || $name === 'content') {
                return '$this->getContent()';
            }

            /**
             * This function includes views of volt or others template engines
             * dynamically
             */
            if ($name === 'partial') {
                return '$this->partial(' . $arguments . ')';
            }

            /**
             * This function embeds the parent block in the current block
             */
            if ($name === 'super') {
                $extendedBlocks = $this->extendedBlocks;
                if (gettype($extendedBlocks) === 'array') {
                    $currentBlock = $this->currentBlock;

                    if (isset($extendedBlocks[$currentBlock])) {
                        $block     = $extendedBlocks[$currentBlock];
                        $exprLevel = $this->exprLevel;

                        if (gettype($block) === 'array') {
                            $code        = $this->statementListOrExtends($block);
                            $escapedCode = $exprLevel == 1 ? $code : addslashes($code);
                        } else {
                            $escapedCode = $exprLevel == 1 ? $block : addslashes($block);
                        }

                        /**
                         * If the super() is the first level we don't escape it
                         */
                        if ($exprLevel == 1) {
                            return $escapedCode;
                        }

                        return "'" . $escapedCode . "'";
                    }
                }

                return "''";
            }

            $method = lcfirst(
            //\Phalcon\Text::camelize($name)
                ucwords($name)
            );

            $arrayHelpers = [
                'link_to'        => true,
                'image'          => true,
                'form'           => true,
                'submit_button'  => true,
                'radio_field'    => true,
                'check_field'    => true,
                'file_field'     => true,
                "hidden_field"   => true,
                "password_field" => true,
                "text_area"      => true,
                "text_field"     => true,
                "email_field"    => true,
                "date_field"     => true,
                "tel_field"      => true,
                "numeric_field"  => true,
                "image_input"    => true,
            ];

            /**
             * Check if it's a method in Phalcon\Tag
             */
            if (method_exists('Phalcon\\Tag', $method)) {
                if (isset($arrayHelpers[$name])) {
                    return '$this->tag->' . $method . '([' . $arguments . '])';
                }

                return '$this->tag->' . $method . '(' . $arguments . ')';
            }

            /**
             * Get a dynamic URL
             */
            if ($name === 'url') {
                return '$this->url->get(' . $arguments . ')';
            }

            /**
             * Get a static URL
             */
            if ($name === 'static_url') {
                return '$this->url->getStatic(' . $arguments . ')';
            }

            if ($name === 'date') {
                return 'date(' . $arguments . ')';
            }

            if ($name === 'time') {
                return 'time()';
            }

            if ($name === 'dump') {
                return 'var_dump(' . $arguments . ')';
            }

            if ($name === 'version') {
                return 'Phalcon\\Version::get()';
            }

            if ($name === 'version_id') {
                return 'Phalcon\\Version::getId()';
            }

            /**
             * Read PHP constants in templates
             */
            if ($name === 'constant') {
                return 'constant(' . $arguments . ')';
            }

            /**
             * By default it tries to call a macro
             */
            return '$this->callMacro(\'' . $name . '\', [' . $arguments . '])';
        }

        return $this->expression($nameExpr, $doubleQuotes) . '(' . $arguments . ')';
    }

    /**
     * Returns the path to the last compiled template
     */
    public function getCompiledTemplatePath(): string
    {
        return $this->compiledTemplatePath;
    }

    /**
     * Returns the internal dependency injector
     */
    public function getDI(): DiInterface
    {
        return $this->container;
    }

    /**
     * Returns the list of extensions registered in Volt
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Register the user registered filters
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Register the user registered functions
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * Returns a compiler's option
     */
    public function getOption(string $option): ?string
    {
        if (!isset($this->options[$option])) {
            return null;
        }

        return $this->options[$option];
    }

    /**
     * Returns the compiler options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns the path that is currently being compiled
     */
    public function getTemplatePath(): string
    {
        return $this->currentPath;
    }

    /**
     * Return a unique prefix to be used as prefix for compiled variables and
     * contexts
     *
     * @throws BaseException
     */
    public function getUniquePrefix(): string
    {
        /**
         * If the unique prefix is not set we use a hash using the modified
         * Berstein algorithm
         */
        if (!$this->prefix) {
            $this->prefix = $this->uniquePathKey($this->currentPath);
        }

        /**
         * The user could use a closure generator
         */
        if ($this->prefix instanceof Closure) {
            $this->prefix = call_user_func_array(
                $this->prefix,
                [
                    $this,
                ]
            );
        }

        if (gettype($this->prefix) !== 'string') {
            throw new BaseException('The unique compilation prefix is invalid');
        }

        return $this->prefix;
    }

    /**
     * Parses a Volt template returning its intermediate representation
     *
     *```php
     * print_r(
     *     $compiler->parse("{% raw %}{{ 3 + 2 }}{% endraw %}")
     * );
     *```
     */
    public function parse(string $viewCode): array
    {
        // TODO: rewrite from C
        return phvolt_parse_view($viewCode, 'eval code');
    }

    /**
     * @throws Exception
     */
    public function resolveTest(array $test, string $left): string
    {
        $type = $test['type'];

        if ($type === self::PHVOLT_T_IDENTIFIER) {
            $name = $test['value'];

            /**
             * Empty uses the PHP's empty operator
             */
            if ($name === 'empty') {
                return 'empty(' . $left . ')';
            }

            /**
             * Check if a value is even
             */
            if ($name === 'even') {
                return "(((" . $left . ") % 2) == 0)";
            }

            /**
             * Check if a value is odd
             */
            if ($name === "odd") {
                return "(((" . $left . ") % 2) != 0)";
            }

            /**
             * Check if a value is numeric
             */
            if ($name === "numeric") {
                return "is_numeric(" . $left . ")";
            }

            /**
             * Check if a value is scalar
             */
            if ($name === "scalar") {
                return "is_scalar(" . $left . ")";
            }

            /**
             * Check if a value is iterable
             */
            if ($name == "iterable") {
                return "(is_array(" . $left . ") || (" . $left . ") instanceof Traversable)";
            }
        }

        /**
         * Check if right part is a function call
         */
        if ($type === self::PHVOLT_T_FCALL) {
            $testName = $test['name'];

            if (isset($testName['value'])) {
                $name = $testName['value'];

                // TODO: make in single condition
                if ($name === 'divisibleby') {
                    return "(((" . $left . ") % (" . $this->expression($test["arguments"]) . ")) == 0)";
                }

                /**
                 * Checks if a value is equals to other
                 */
                if ($name === "sameas") {
                    return "(" . $left . ") === (" . $this->expression($test["arguments"]) . ")";
                }

                /**
                 * Checks if a variable match a type
                 */
                if ($name === 'type') {
                    return "gettype(" . $left . ") === (" . $this->expression($test["arguments"]) . ")";
                }
            }
        }

        /**
         * Fall back to the equals operator
         */
        return $left . " == " . $this->expression($test);
    }

    /**
     * Sets a single compiler option
     */
    public function setOption(string $option, mixed $value): void
    {
        $this->options[$option] = $value;
    }

    /**
     * Sets the compiler options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Set a unique prefix to be used as prefix for compiled variables
     */
    public function setUniquePrefix(string $prefix): Compiler
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Gets the final path with VIEW
     */
    protected function getFinalPath(string $path): string
    {
        // TODO: Change to instance of View
        if (false === is_object($this->view)) {
            return $path;
        }

        $viewsDirs = $this->view->getViewsDir();
        if (is_array($viewsDirs)) {
            foreach ($viewsDirs as $viewsDir) {
                $path = $viewsDir . $path;
                if (true === file_exists($path)) {
                    return $path;
                }
            }

            // Otherwise, take the last viewsDir
            // TODO: Come back here later
            return $viewsDir . $path;
        }

        return $viewsDirs . $path;
    }

    /**
     * Resolves filter intermediate code into PHP function calls
     *
     * @throws Exception
     */
    final protected function resolveFilter(array $filter, string $left): string
    {
        $code = null;
        $type = $filter['type'];

        /**
         * Check if the filter is a single identifier
         */
        if ($type === self::PHVOLT_T_IDENTIFIER) {
            $name = $filter['value'];
        } else {
            if ($type !== self::PHVOLT_T_FCALL) {
                /**
                 * Unknown filter throw an exception
                 */
                throw new Exception(
                    "Unknown filter type in " . $filter["file"] . " on line " . $filter["line"]
                );
            }

            $functionName = $filter['name'];
            $name         = $functionName['value'];
        }

        $funcArguments = null;
        $arguments     = null;

        /**
         * Resolve arguments
         */
        if (true === isset($filter['arguments'])) {
            $funcArguments = $filter["arguments"];
            /**
             * "default" filter is not the first argument, improve this!
             */
            if ($name !== 'default') {
                $file = $filter['file'];
                $line = $filter['line'];

                /**
                 * TODO: Implement this function directly
                 */
                array_unshift(
                    $funcArguments,
                    [
                        "expr" => [
                            "type"  => 364,
                            "value" => $left,
                            "file"  => $file,
                            "line"  => $line,
                        ],
                        "file" => $file,
                        "line" => $line,
                    ]
                );
            }

            $arguments = $this->expression($funcArguments);
        } else {
            $arguments = $left;
        }

        /**
         * Check if any of the registered extensions provide compilation for
         * this filter
         */
        $extensions = $this->extensions;

        if (true === is_array($extensions)) {
            /**
             * Notify the extensions about being compiling a function
             */
            $code = $this->fireExtensionEvent(
                'compileFilter',
                [$name, $arguments, $funcArguments]
            );

            if (true === is_string($code)) {
                return $code;
            }
        }

        /**
         * Check if it's a user defined filter
         */

        if (true === array_key_exists($name, $this->filters)) {
            $definition = $this->filters[$name];

            /**
             * The definition is a string
             */
            if (true === is_string($definition)) {
                return $definition . '(' . $arguments . ')';
            }

            /**
             * The definition is a closure
             */
            if ($definition instanceof Closure) {
                return call_user_func_array(
                    $definition,
                    [$arguments, $funcArguments]
                );
            }

            /**
             * Invalid filter definition throw an exception
             */
            throw new Exception(
                "Invalid definition for user filter '"
                . $name . "' in " . $filter["file"] . " on line " . $filter["line"]
            );
        }

        /**
         * "length" uses the length method implemented in the Volt adapter
         */
        if ($name === 'length') {
            return '$this->length(' . $arguments . ')';
        }

        /**
         * "e"/"escape" filter uses the escaper component
         */
        if ($name === 'e' || $name === 'escape') {
            return '$this->escaper->escapeHtml(' . $arguments . ')';
        }

        /**
         * "escape_css" filter uses the escaper component to filter CSS
         */
        if ($name === 'escape_css') {
            return '$this->escaper->escapeCss(' . $arguments . ')';
        }

        /**
         * "escape_js" filter uses the escaper component to escape JavaScript
         */
        if ($name === 'escape_js') {
            return '$this->escaper->escapeJs(' . $arguments . ')';
        }

        /**
         * "escape_attr" filter uses the escaper component to escape HTML
         * attributes
         */
        if ($name === 'escape_attr') {
            return '$this->escaper->escapeHtmlAttr(' . $arguments . ')';
        }

        /**
         * "trim" calls the "trim" function in the PHP userland
         */
        if ($name === 'trim') {
            return 'trim(' . $arguments . ')';
        }

        /**
         * "left_trim" calls the "ltrim" function in the PHP userland
         */
        if ($name === 'left_trim') {
            return 'ltrim(' . $arguments . ')';
        }

        /**
         * "right_trim" calls the "rtrim" function in the PHP userland
         */
        if ($name === 'right_trim') {
            return 'rtrim(' . $arguments . ')';
        }

        /**
         * "striptags" calls the "strip_tags" function in the PHP userland
         */
        if ($name === "striptags") {
            return "strip_tags(" . $arguments . ")";
        }

        /**
         * "url_encode" calls the "urlencode" function in the PHP userland
         */
        if ($name === "url_encode") {
            return "urlencode(" . $arguments . ")";
        }

        /**
         * "slashes" calls the "addslashes" function in the PHP userland
         */
        if ($name === "slashes") {
            return "addslashes(" . $arguments . ")";
        }

        /**
         * "stripslashes" calls the "stripslashes" function in the PHP userland
         */
        if ($name === "stripslashes") {
            return "stripslashes(" . $arguments . ")";
        }

        /**
         * "nl2br" calls the "nl2br" function in the PHP userland
         */
        if ($name === "nl2br") {
            return "nl2br(" . $arguments . ")";
        }

        /**
         * "keys" uses calls the "array_keys" function in the PHP userland
         */
        if ($name === "keys") {
            return "array_keys(" . $arguments . ")";
        }

        /**
         * "join" uses calls the "join" function in the PHP userland
         */
        if ($name === "join") {
            return "join('" . $funcArguments[1]["expr"]["value"] . "', " . $funcArguments[0]["expr"]["value"] . ")";
        }

        /**
         * "lower"/"lowercase" calls the "strtolower" function or
         * "mb_strtolower" if the mbstring extension is loaded
         */
        if ($name === "lower" || $name == "lowercase") {
            return "Phalcon\\Text::lower(" . $arguments . ")";
        }

        /**
         * "upper"/"uppercase" calls the "strtoupper" function or
         * "mb_strtoupper" if the mbstring extension is loaded
         */
        if ($name === "upper" || $name == "uppercase") {
            return "Phalcon\\Text::upper(" . $arguments . ")";
        }

        /**
         * "capitalize" filter calls "ucwords"
         */
        if ($name === "capitalize") {
            return "ucwords(" . $arguments . ")";
        }

        /**
         * "sort" calls "sort" method in the engine adapter
         */
        if ($name === "sort") {
            return '$this->sort(' . $arguments . ")";
        }

        /**
         * "json_encode" calls the "json_encode" function in the PHP userland
         */
        if ($name === "json_encode") {
            return "json_encode(" . $arguments . ")";
        }

        /**
         * "json_decode" calls the "json_decode" function in the PHP userland
         */
        if ($name === "json_decode") {
            return "json_decode(" . $arguments . ")";
        }

        /**
         * "format" calls the "sprintf" function in the PHP userland
         */
        if ($name === "format") {
            return "sprintf(" . $arguments . ")";
        }

        /**
         * "abs" calls the "abs" function in the PHP userland
         */
        if ($name === "abs") {
            return "abs(" . $arguments . ")";
        }

        /**
         * "slice" slices string/arrays/traversable objects
         */
        if ($name === "slice") {
            return '$this->slice(' . $arguments . ")";
        }

        /**
         * "default" checks if a variable is empty
         */
        if ($name === "default") {
            return "(empty(" . $left . ") ? (" . $arguments . ") : (" . $left . "))";
        }

        /**
         * This function uses mbstring or iconv to convert strings from one
         * charset to another
         */
        if ($name === "convert_encoding") {
            return '$this->convertEncoding(' . $arguments . ")";
        }

        /**
         * Unknown filter throw an exception
         */
        throw new Exception(
            "Unknown filter \"" . $name . "\" in " . $filter["file"] . " on line " . $filter["line"]
        );
    }

    /**
     * Traverses a statement list compiling each of its nodes
     *
     * @throws Exception
     */
    final protected function statementList(array $statements, bool $extendsMode = false): string
    {
        /**
         * Nothing to compile
         */
        if (0 === count($statements)) {
            return '';
        }

        /**
         * Increase the statement recursion level in extends mode
         */
        $extended  = $this->extended;
        $blockMode = $extended || $extendsMode;

        if (true === $blockMode) {
            $this->blockLevel++;
        }

        $this->level++;
        $compilation = null;
        $extensions  = $this->extensions;

        foreach ($statements as $statement) {
            /**
             * All statements must be arrays
             */
            if (false === is_array($statement)) {
                throw new Exception("Corrupted statement");
            }

            /**
             * Check if the statement is valid
             */
            if (false === isset($statement['type'])) {
                throw new Exception(
                    "Invalid statement in " . $statement["file"] . " on line " . $statement["line"]
                );
            }

            /**
             * Check if extensions have implemented custom compilation for this
             * statement
             */
            if (is_array($extensions)) {
                /**
                 * Notify the extensions about being resolving a statement
                 */
                $tempCompilation = $this->fireExtensionEvent(
                    'compileStatement',
                    [$statement]
                );

                if (is_string($tempCompilation)) {
                    $compilation .= $tempCompilation;

                    continue;
                }
            }

            /**
             * Get the statement type
             */
            $type = $statement['type'];

            /**
             * Compile the statement according to the statement's type
             */
            switch ($type) {
                case self::PHVOLT_T_RAW_FRAGMENT:
                    $compilation .= $statement["value"];
                    break;

                case self::PHVOLT_T_IF:
                    $compilation .= $this->compileIf($statement, $extendsMode);
                    break;

                case self::PHVOLT_T_ELSEIF:
                    $compilation .= $this->compileElseIf($statement);
                    break;

                case self::PHVOLT_T_SWITCH:
                    $compilation .= $this->compileSwitch(
                        $statement,
                        $extendsMode
                    );

                    break;

                case self::PHVOLT_T_CASE:
                    $compilation .= $this->compileCase($statement);
                    break;

                case self::PHVOLT_T_DEFAULT:
                    $compilation .= $this->compileCase($statement, false);
                    break;

                case self::PHVOLT_T_FOR:
                    $compilation .= $this->compileForeach(
                        $statement,
                        $extendsMode
                    );

                    break;

                case self::PHVOLT_T_SET:
                    $compilation .= $this->compileSet($statement);
                    break;

                case self::PHVOLT_T_ECHO:
                    $compilation .= $this->compileEcho($statement);
                    break;

                case self::PHVOLT_T_BLOCK:
                    /**
                     * Block statement
                     */
                    $blockName       = $statement['name'];
                    $blockStatements = $statement["block_statements"] ?? [];
                    $blocks          = $this->blocks;

                    if (true === $blockMode) {
                        if (false === is_array($blocks)) {
                            $blocks = [];
                        }

                        /**
                         * Create an unnamed block.
                         */
                        if ($compilation !== null) {
                            $blocks[]    = $compilation;
                            $compilation = null;
                        }

                        /**
                         * In extends mode we add the block statements to the
                         * blocks variable
                         */
                        $blocks[$blockName] = $blockStatements;
                        $this->blocks       = $blocks;
                    } elseif (true === is_array($blockStatements)) {
                        $compilation .= $this->statementList($blockStatements, $extendsMode);
                    }

                    break;

                case self::PHVOLT_T_EXTENDS:
                    /**
                     * Extends statement
                     */
                    $path      = $statement["path"];
                    $finalPath = $this->getFinalPath($path['value']);
                    $extended  = true;

                    /**
                     * Perform a sub-compilation of the extended file
                     */
                    $subCompiler     = clone $this;
                    $tempCompilation = $subCompiler->compile($finalPath, $extended);

                    /**
                     * If the compilation doesn't return anything we include the
                     * compiled path
                     */
                    if (null === $tempCompilation) {
                        $tempCompilation = file_get_contents($subCompiler->getCompiledTemplatePath());
                    }

                    $this->extended       = true;
                    $this->extendedBlocks = $tempCompilation;
                    $blockMode            = $extended;

                    break;

                case self::PHVOLT_T_INCLUDE:
                    $compilation .= $this->compileInclude($statement);

                    break;

                case self::PHVOLT_T_DO:
                    $compilation .= $this->compileDo($statement);
                    break;

                case self::PHVOLT_T_RETURN:
                    $compilation .= $this->compileReturn($statement);
                    break;

                case self::PHVOLT_T_AUTOESCAPE:
                    $compilation .= $this->compileAutoEscape(
                        $statement,
                        $extendsMode
                    );

                    break;

                case self::PHVOLT_T_CONTINUE:
                    /**
                     * "Continue" statement
                     */
                    $compilation .= "<?php continue; ?>";
                    break;

                case self::PHVOLT_T_BREAK:
                    /**
                     * "Break" statement
                     */
                    $compilation .= "<?php break; ?>";
                    break;

                case 321:
                    /**
                     * "Forelse" condition
                     */
                    $compilation .= $this->compileForElse();
                    break;

                case self::PHVOLT_T_MACRO:
                    /**
                     * Define a macro
                     */
                    $compilation .= $this->compileMacro(
                        $statement,
                        $extendsMode
                    );

                    break;

                case 325:
                    /**
                     * "Call" statement
                     */
                    $compilation .= $this->compileCall(
                        $statement,
                        $extendsMode
                    );

                    break;

                case 358:
                    /**
                     * Empty statement
                     */
                    break;

                default:
                    throw new Exception(
                        "Unknown statement " . $type . " in " . $statement["file"] . " on line " . $statement["line"]
                    );
            }
        }

        /**
         * Reduce the statement level nesting
         */
        if (true === $blockMode) {
            if ($this->blockLevel === 1) {
                if (null !== $compilation) {
                    $this->blocks[] = $compilation;
                }
            }

            $this->blockLevel--;
        }

        $this->level--;

        return $compilation === null ? '' : $compilation;
    }

    /**
     * Compiles a block of statements
     *
     * @param array $statements
     *
     * @return mixed|string
     * @throws Exception
     */
    final protected function statementListOrExtends($statements)
    {
        /**
         * Resolve the statement list as normal
         */
        if (false === is_array($statements)) {
            return $statements;
        }

        /**
         * If all elements in the statement list are arrays we resolve this as a
         * statementList
         */
        $isStatementList = true;

        if (!isset($statements["type"])) {
            foreach ($statements as $statement) {
                if (false === is_array($statement)) {
                    $isStatementList = false;

                    break;
                }
            }
        }

        /**
         * Resolve the statement list as normal
         */
        if (true === $isStatementList) {
            return $this->statementList($statements);
        }

        /**
         * Is an array but not a statement list?
         */
        return $statements;
    }

    /**
     * Compare modification timestamps to check if the $filename1
     * needs to be recompiled
     */
    private function compareMtime(string $filename1, string $filename2): bool
    {
        return filemtime($filename1) >= filemtime($filename2);
    }

    private function isTagFactory(array $expression): bool
    {
        if (isset($expression['name']['left'])) {
            /**
             * There is a value, get it and check it
             */
            $left = $expression['name']['left'];
            if (isset($left['value'])) {
                return $left['value'] === 'tag';
            }

            /**
             * There is a "name" so that is nested, recursion
             */
            if (isset($left['name']) && is_array($left['name'])) {
                return $this->isTagFactory($left);
            }
        }

        return false;
    }

    /**
     * Implementation of zephir_prepare_virtual_path()
     *
     * @param string|null $path
     * @param string|null $virtualSeparator
     *
     * @return string
     */
    private function prepareVirtualPath(?string $path = null, ?string $virtualSeparator = null): string
    {
        $virtualPath = '';

        if ($path === null || $virtualSeparator === null) {
            if ($path !== null) {
                return $path;
            }

            return $virtualPath;
        }

        $path   = strtolower($path);
        $length = strlen($path);
        for ($i = 0; $i < $length; $i++) {
            $char = $path[$i];

            /**
             * Null byte check
             *
             * @see https://www.php.net/manual/en/security.filesystem.nullbytes.php
             */
            if ($char == '\0') {
                break;
            }

            if ($char === '/' || $char === '\\' || $char === ':') {
                $virtualPath .= $virtualSeparator;
            } else {
                $virtualPath .= $char;
            }
        }

        return $virtualPath;
    }

    /**
     * Implementation of zephir_unique_path_key()
     *
     * @param string|null $path
     *
     * @return string|null
     */
    private function uniquePathKey(?string $path = null): ?string
    {
        if ($path === null) {
            return null;
        }

        return sprintf('v%lu', $this->zendInlineHashFunc($path, strlen($path) + 1));
    }

    /**
     * @see https://github.com/php/php-src/blob/81623d3a60599d05c83987dec111bf56809f901d/Zend/zend_hash.h#L263
     */
    private function zendInlineHashFunc(string $arKey, int $nKeyLength): int
    {
        $hash = 5381;
        $i    = 0;

        /* variant with the hash unrolled eight times */
        for (; $nKeyLength >= 8; $nKeyLength -= 8) {
            $hash = (($hash << 5) + $hash) + ord($arKey[$i++]);
            $hash = (($hash << 5) + $hash) + ord($arKey[$i++]);
            $hash = (($hash << 5) + $hash) + ord($arKey[$i++]);
            $hash = (($hash << 5) + $hash) + ord($arKey[$i++]);
            $hash = (($hash << 5) + $hash) + ord($arKey[$i++]);
            $hash = (($hash << 5) + $hash) + ord($arKey[$i++]);
            $hash = (($hash << 5) + $hash) + ord($arKey[$i++]);
            $hash = (($hash << 5) + $hash) + ord($arKey[$i++]);
        }

        switch ($nKeyLength) {
            case 7:
                $hash = (($hash << 5) + $hash) + ord($arKey[$i++]); /* fallthrough... */
            case 6:
                $hash = (($hash << 5) + $hash) + ord($arKey[$i++]); /* fallthrough... */
            case 5:
                $hash = (($hash << 5) + $hash) + ord($arKey[$i++]); /* fallthrough... */
            case 4:
                $hash = (($hash << 5) + $hash) + ord($arKey[$i++]); /* fallthrough... */
            case 3:
                $hash = (($hash << 5) + $hash) + ord($arKey[$i++]); /* fallthrough... */
            case 2:
                $hash = (($hash << 5) + $hash) + ord($arKey[$i++]); /* fallthrough... */
            case 1:
                $hash = (($hash << 5) + $hash) + ord($arKey[$i] ?? '');
                break;
            case 0:
                break;
        }

        return $hash;
    }
}

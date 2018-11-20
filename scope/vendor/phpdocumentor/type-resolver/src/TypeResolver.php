<?php

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2015 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
namespace _PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection;

use _PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Array_;
use _PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Compound;
use _PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Context;
use _PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Iterable_;
use _PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Nullable;
use _PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Object_;
final class TypeResolver
{
    /** @var string Definition of the ARRAY operator for types */
    const OPERATOR_ARRAY = '[]';
    /** @var string Definition of the NAMESPACE operator in PHP */
    const OPERATOR_NAMESPACE = '\\';
    /** @var string[] List of recognized keywords and unto which Value Object they map */
    private $keywords = array('string' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\String_::class, 'int' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Integer::class, 'integer' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Integer::class, 'bool' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Boolean::class, 'boolean' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Boolean::class, 'float' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Float_::class, 'double' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Float_::class, 'object' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Object_::class, 'mixed' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Mixed_::class, 'array' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Array_::class, 'resource' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Resource_::class, 'void' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Void_::class, 'null' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Null_::class, 'scalar' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Scalar::class, 'callback' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Callable_::class, 'callable' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Callable_::class, 'false' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Boolean::class, 'true' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Boolean::class, 'self' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Self_::class, '$this' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\This::class, 'static' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Static_::class, 'parent' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Parent_::class, 'iterable' => \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Iterable_::class);
    /** @var FqsenResolver */
    private $fqsenResolver;
    /**
     * Initializes this TypeResolver with the means to create and resolve Fqsen objects.
     *
     * @param FqsenResolver $fqsenResolver
     */
    public function __construct(\_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\FqsenResolver $fqsenResolver = null)
    {
        $this->fqsenResolver = $fqsenResolver ?: new \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\FqsenResolver();
    }
    /**
     * Analyzes the given type and returns the FQCN variant.
     *
     * When a type is provided this method checks whether it is not a keyword or
     * Fully Qualified Class Name. If so it will use the given namespace and
     * aliases to expand the type to a FQCN representation.
     *
     * This method only works as expected if the namespace and aliases are set;
     * no dynamic reflection is being performed here.
     *
     * @param string $type     The relative or absolute type.
     * @param Context $context
     *
     * @uses Context::getNamespace()        to determine with what to prefix the type name.
     * @uses Context::getNamespaceAliases() to check whether the first part of the relative type name should not be
     *     replaced with another namespace.
     *
     * @return Type|null
     */
    public function resolve($type, \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Context $context = null)
    {
        if (!\is_string($type)) {
            throw new \InvalidArgumentException('Attempted to resolve type but it appeared not to be a string, received: ' . \var_export($type, \true));
        }
        $type = \trim($type);
        if (!$type) {
            throw new \InvalidArgumentException('Attempted to resolve "' . $type . '" but it appears to be empty');
        }
        if ($context === null) {
            $context = new \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Context('');
        }
        switch (\true) {
            case $this->isNullableType($type):
                return $this->resolveNullableType($type, $context);
            case $this->isKeyword($type):
                return $this->resolveKeyword($type);
            case $this->isCompoundType($type):
                return $this->resolveCompoundType($type, $context);
            case $this->isTypedArray($type):
                return $this->resolveTypedArray($type, $context);
            case $this->isFqsen($type):
                return $this->resolveTypedObject($type);
            case $this->isPartialStructuralElementName($type):
                return $this->resolveTypedObject($type, $context);
            // @codeCoverageIgnoreStart
            default:
                // I haven't got the foggiest how the logic would come here but added this as a defense.
                throw new \RuntimeException('Unable to resolve type "' . $type . '", there is no known method to resolve it');
        }
        // @codeCoverageIgnoreEnd
    }
    /**
     * Adds a keyword to the list of Keywords and associates it with a specific Value Object.
     *
     * @param string $keyword
     * @param string $typeClassName
     *
     * @return void
     */
    public function addKeyword($keyword, $typeClassName)
    {
        if (!\class_exists($typeClassName)) {
            throw new \InvalidArgumentException('The Value Object that needs to be created with a keyword "' . $keyword . '" must be an existing class' . ' but we could not find the class ' . $typeClassName);
        }
        if (!\in_array(\_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Type::class, \class_implements($typeClassName))) {
            throw new \InvalidArgumentException('The class "' . $typeClassName . '" must implement the interface "phpDocumentor\\Reflection\\Type"');
        }
        $this->keywords[$keyword] = $typeClassName;
    }
    /**
     * Detects whether the given type represents an array.
     *
     * @param string $type A relative or absolute type as defined in the phpDocumentor documentation.
     *
     * @return bool
     */
    private function isTypedArray($type)
    {
        return \substr($type, -2) === self::OPERATOR_ARRAY;
    }
    /**
     * Detects whether the given type represents a PHPDoc keyword.
     *
     * @param string $type A relative or absolute type as defined in the phpDocumentor documentation.
     *
     * @return bool
     */
    private function isKeyword($type)
    {
        return \in_array(\strtolower($type), \array_keys($this->keywords), \true);
    }
    /**
     * Detects whether the given type represents a relative structural element name.
     *
     * @param string $type A relative or absolute type as defined in the phpDocumentor documentation.
     *
     * @return bool
     */
    private function isPartialStructuralElementName($type)
    {
        return $type[0] !== self::OPERATOR_NAMESPACE && !$this->isKeyword($type);
    }
    /**
     * Tests whether the given type is a Fully Qualified Structural Element Name.
     *
     * @param string $type
     *
     * @return bool
     */
    private function isFqsen($type)
    {
        return \strpos($type, self::OPERATOR_NAMESPACE) === 0;
    }
    /**
     * Tests whether the given type is a compound type (i.e. `string|int`).
     *
     * @param string $type
     *
     * @return bool
     */
    private function isCompoundType($type)
    {
        return \strpos($type, '|') !== \false;
    }
    /**
     * Test whether the given type is a nullable type (i.e. `?string`)
     *
     * @param string $type
     *
     * @return bool
     */
    private function isNullableType($type)
    {
        return $type[0] === '?';
    }
    /**
     * Resolves the given typed array string (i.e. `string[]`) into an Array object with the right types set.
     *
     * @param string $type
     * @param Context $context
     *
     * @return Array_
     */
    private function resolveTypedArray($type, \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Context $context)
    {
        return new \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Array_($this->resolve(\substr($type, 0, -2), $context));
    }
    /**
     * Resolves the given keyword (such as `string`) into a Type object representing that keyword.
     *
     * @param string $type
     *
     * @return Type
     */
    private function resolveKeyword($type)
    {
        $className = $this->keywords[\strtolower($type)];
        return new $className();
    }
    /**
     * Resolves the given FQSEN string into an FQSEN object.
     *
     * @param string $type
     * @param Context|null $context
     *
     * @return Object_
     */
    private function resolveTypedObject($type, \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Context $context = null)
    {
        return new \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Object_($this->fqsenResolver->resolve($type, $context));
    }
    /**
     * Resolves a compound type (i.e. `string|int`) into the appropriate Type objects or FQSEN.
     *
     * @param string $type
     * @param Context $context
     *
     * @return Compound
     */
    private function resolveCompoundType($type, \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Context $context)
    {
        $types = [];
        foreach (\explode('|', $type) as $part) {
            $types[] = $this->resolve($part, $context);
        }
        return new \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Compound($types);
    }
    /**
     * Resolve nullable types (i.e. `?string`) into a Nullable type wrapper
     *
     * @param string $type
     * @param Context $context
     *
     * @return Nullable
     */
    private function resolveNullableType($type, \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Context $context)
    {
        return new \_PhpScoper5bf3cbdac76b4\phpDocumentor\Reflection\Types\Nullable($this->resolve(\ltrim($type, '?'), $context));
    }
}

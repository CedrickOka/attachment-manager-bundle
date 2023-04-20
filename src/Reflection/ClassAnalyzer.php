<?php

namespace Oka\AttachmentManagerBundle\Reflection;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
final class ClassAnalyzer
{
    /**
     * Return TRUE if the given object use the given trait, FALSE if not.
     */
    public function hasTrait(\ReflectionClass $class, string $traitName, bool $recursive = false): bool
    {
        if (in_array($traitName, $class->getTraitNames())) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if (false === $recursive || false === $parentClass) {
            return false;
        }

        return $this->hasTrait($parentClass, $traitName, $recursive);
    }

    /**
     * Return TRUE if the given object has the given method, FALSE if not.
     */
    public function hasMethod(\ReflectionClass $class, string $methodName): bool
    {
        return $class->hasMethod($methodName);
    }

    /**
     * Return TRUE if the given object has the given property, FALSE if not.
     */
    public function hasProperty(\ReflectionClass $class, string $propertyName): bool
    {
        if ($class->hasProperty($propertyName)) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if (false === $parentClass) {
            return false;
        }

        return $this->hasProperty($parentClass, $propertyName);
    }
}

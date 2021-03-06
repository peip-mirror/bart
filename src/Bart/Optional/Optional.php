<?php
namespace Bart\Optional;

use Bart\Exceptions\IllegalStateException;

/**
 * This class represents optional values. Instances of Optional can include either Some or None, for
 * values that exist or do not, respectively.
 *
 * Implementation inspired by:
 * http://nitschinger.at/A-Journey-on-Avoiding-Nulls-in-PHP
 * https://gist.github.com/philix/7312211
 *
 * Class Optional
 * @package Bart\Optional
 */
abstract class Optional
{
    private function __construct()
    {

    }

    /**
     * Checks that a reference is not null and returns it or throws an exception if it is
     * @param mixed $reference
     * @param string|null $exceptionMessage
     * @return mixed
     * @throws IllegalStateException
     */
    protected static function notNull($reference, $exceptionMessage = null)
    {
        $message = $exceptionMessage === null ? 'Disallowed null in reference.' : $exceptionMessage;

        if ($reference === null || $reference instanceof None) {
            throw new IllegalStateException($message);
        }

        return $reference;
    }

    /**
     * Returns an instance that contains no references
     * @return None
     */
    public static function absent()
    {
        return None::instance();
    }

    /**
     * Creates an instance containing the provided reference
     * @param mixed $ref
     * @return Some
     * @throws IllegalStateException
     */
    public static function from($ref)
    {
        // Some checks if the value is null on instantiation
        return new Some($ref);
    }

    /**
     * Returns an Optional instance of the reference or returns None if it's null
     * @param mixed $ref
     * @return None|Some
     */
    public static function fromNullable($ref)
    {
        return ($ref === null || $ref instanceof None) ? static::absent() : new Some($ref);
    }

    /**
     * Whether or not the instance is present
     * @return bool
     */
    public abstract function isPresent();

    /**
     * Whether the instance is absent
     * @return bool
     */
    public abstract function isAbsent();

    /**
     * Returns the value of the option. This method may throw
     * exceptions for nonexistent values.
     * @return mixed
     */
    public abstract function get();

    /**
     * Gets the contained reference, or a provided default value if it is absent
     * @param mixed $default
     * @return mixed
     */
    public abstract function getOrElse($default);

    /**
     * Gets the contained reference, or null if it is absent. The idea of
     * Optional is to avoid using null, but there may be cases where it is still relevant.
     * @return mixed
     */
    public abstract function getOrNull();

    /**
     * Returns an Optional containing the result of calling $callable on
     * the contained value. If no value exists, as in the case of None, then
     * this method will simply return None. The method will return None
     * if the result of applying $callable to the contained value is null.
     * @param callable $callable
     * @return Some|None
     */
    public abstract function map(Callable $callable);

    /**
     * Whether the contained value equals the value contained in another Optional
     * @param Optional $object
     * @return bool
     */
    public abstract function equals(Optional $object);

    /**
     * @return null
     */
    public function __toString() {
        return null;
    }

}

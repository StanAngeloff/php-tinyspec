# Tiny TDD for PHP

php-tinyspec is a one-file framework for test-driven development in PHP 5.3+

## A simple spec

    \Spec\describe('Calculator')->add(array(
      'The __constructor' => array(
        'can be called with no arguments' => function() {
          new Calculator();
        }
      ),
      'Operations' => array(
        'topic' => function() { return new Calculator(); },
        'sum works with floats' => function($topic) {
          $topic->sum(1.5, 2.5);
          \Spec\assert_must(4 === $topic->result);
        },
        'sum fails with strings' => function($topic) {
          \Spec\assert_throws('cannot sum strings');
          $topic->sum('left', 'right');
        }
      )
    ))->run();

The result of running the specs:

    % php example.php
    Calculator:
      The __constructor:
        - can be called with no arguments:                              PASS
      Operations:
        - sum works with floats:                                        PASS
        - sum fails with strings:                                       PASS

    Expectations: 3, passed: 3, failed 0; 100.00% successful.

## Assertions

- **assert_throws**($needle)

   Executed after the spec to validate an exception with the given message was
   thrown. Fails when no exception was encountered or the message did not
   contain `$needle`.

- **assert_is_true**($subject, $message)

   Strict `$subject === true`

- **assert_is_not_true**($subject, $message)

   Strict `$subject !== true`

- **assert_is_false**($subject, $message)

   Strict `$subject === false`

- **assert_is_not_false**($subject, $message)

   Strict `$subject !== false`

- **assert_is_null**($subject, $message)

   Strict `$subject === null`

- **assert_is_not_null**($subject, $message)

   Strict `$subject !== null`

- **assert_key_missing**($array, $key, $message = null)

   Validate `$key` exists in `$array` even if its value is `NULL`.

- **assert_key_not_missing**($array, $key, $message = null)

   Validate `$key` is missing in `$array`.

- **assert_is_array**($subject, $message = null)

   Validate `$subject` is an array.

- **assert_is_string**($subject, $message = null)

   Validate `$subject` is a string.

- **assert_value_empty**($subject, $message)

   Validate no value was present in `$subject`. Uses `empty(..)` internally.

- **assert_value_not_empty**($subject, $message)

   Validate non-falsy value was present in `$subject`.

- **assert_hash_equal**($subject, $reference, $message)

   Serialize `$subject` and `$reference` and compare their hash values. Both
   variables must be exactly the same (incl. ordering of keys) for this
   validation to pass.

- **assert_is_reference**(&$a, &$b, $message = null)

   Works on objects and arrays only. Inserts a temporary key/property and
   unsets it if it exists in both variables.

- **assert_must**($condition, $message)

   Validate `$condition` is non-falsy.

## Contribute

There is plenty of work to be done -- starting from how specs are run to what
assertions are built into the framework. If you find your favourite `assert_?`
method to be missing, fork the project and add it.

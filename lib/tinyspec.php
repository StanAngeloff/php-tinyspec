<?php

namespace Spec;


function describe($description)
{ return new Group($description); }

function format($string)
{
    return str_replace(
        array('{bold}',  '{/bold}',  '{underline}', '{/underline}', '{yellow}', '{/yellow}', '{red}',    '{/red}',   '{green}',  '{/green}', '{white}',  '{/white}'),
        array("\033[1m", "\033[22m", "\033[4m",     "\033[24m",     "\033[33m",  "\033[39m", "\033[31m", "\033[39m", "\033[32m", "\033[39m", "\033[37m", "\033[39m"),
        $string
    );
}

final class assert
{
    public static function throws($needle)
    {
        self::queue(function() use ($needle) {
            assert::is_true(Group::$unit['failed'], format("{red}did not throw '{bold}$needle{/bold}'{/red}"));
            assert::is_not_false(strpos(Group::$unit['message'], $needle), format('{red}' . Group::$unit['message'] . " did not contain '{bold}$needle{/bold}'{/red}"));
            Group::$unit['failed'] = false;
        });
    }

    public static function equals($subject, $value, $message)
    { self::must($subject == $value, $message); }

    public static function strict_equals($subject, $value, $message)
    { self::must($subject === $value, $message); }

    public static function is_true($subject, $message)
    { self::strict_equals($subject, true, $message); }

    public static function is_not_true($subject, $message)
    { self::must(true !== $subject, $message); }

    public static function is_false($subject, $message)
    { self::strict_equals($subject, false, $message); }

    public static function is_not_false($subject, $message)
    { self::must(false !== $subject, $message); }

    public static function is_null($subject, $message)
    { self::strict_equals($subject, null, $message); }

    public static function is_not_null($subject, $message)
    { self::must(null !== $subject, $message); }

    public static function key_missing($array, $key, $message = null)
    { self::is_false(array_key_exists($key, $array), (isset ($message) ? $message : "'$key' key was present")); }

    public static function key_not_missing($array, $key, $message = null)
    { self::is_true(array_key_exists($key, $array), (isset ($message) ? $message : "'$key' key was missing")); }

    public static function is_array($subject, $message = null)
    { self::is_true(is_array($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not an 'array'")); }

    public static function is_bool($subject, $message = null)
    { self::is_true(is_bool($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not a 'bool'")); }

    public static function is_callable($subject, $message = null)
    { self::is_true(is_callable($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not a 'callable'")); }

    public static function is_double($subject, $message = null)
    { self::is_true(is_double($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not a 'double'")); }

    public static function is_float($subject, $message = null)
    { self::is_true(is_float($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not a 'float'")); }

    public static function is_integer($subject, $message = null)
    { self::is_true(is_integer($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not an 'integer'")); }

    public static function is_long($subject, $message = null)
    { self::is_true(is_long($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not a 'long'")); }

    public static function is_numeric($subject, $message = null)
    { self::is_true(is_numeric($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not 'numeric'")); }

    public static function is_object($subject, $message = null)
    { self::is_true(is_object($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not an 'object'")); }

    public static function is_real($subject, $message = null)
    { self::is_true(is_real($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not a 'real'")); }

    public static function is_resource($subject, $message = null)
    { self::is_true(is_resource($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not a 'resource'")); }

    public static function is_scalar($subject, $message = null)
    { self::is_true(is_scalar($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not a 'scalar'")); }

    public static function is_string($subject, $message = null)
    { self::is_true(is_string($subject), (isset ($message) ? $message : "'" . var_export($subject, true) . "' is not a 'string'")); }

    public static function value_empty($subject, $message)
    { self::is_true(empty($subject), $message); }

    public static function value_not_empty($subject, $message)
    { self::is_false(empty($subject), $message); }

    public static function hash_equals($subject, $reference, $message)
    { self::strict_equals(strcmp(sha1(serialize($subject)), sha1(serialize($reference))), 0, $message); }

    public static function is_reference(&$a, &$b, $message = null)
    {
        if (is_array($a) && is_array($b)) {
            $a['__assert_reference'] = true;
            self::is_true(isset ($b['__assert_reference']), (isset ($message) ? $message : 'array $b did not appear to be a reference to array $a'));
            unset ($b['__assert_reference']);
        } else if (is_object($a) && is_object($b)) {
            $a->__assert_reference = true;
            self::is_true(isset ($b->__assert_reference), (isset ($message) ? $message : 'objects $b did not appear to be a reference to object $a'));
            unset ($b->__assert_reference);
        } else {
            self::strict_equals($a, $b, $message);
        }
    }

    public static function must($condition, $message)
    {
        if ( ! $condition) {
            throw new AssertException($message);
        }
    }

    const QUEUE_EMPTY = ':empty';
    const QUEUE_POP   = ':pop';

    public static function queue($block)
    {
        static $queue = array();
        if (self::QUEUE_EMPTY === $block) {
            return $queue = array();
        } else if (self::QUEUE_POP === $block) {
            return array_pop($queue);
        }
        array_push($queue, $block);
        return sizeof ($queue);
    }

    public static function before()
    { self::queue(self::QUEUE_EMPTY); }

    public static function after()
    {
        while ($operation = self::queue(self::QUEUE_POP)) {
            call_user_func($operation);
            if (Group::$unit['failed']) {
                break;
            }
        }
    }
}


final class Group
{
    public static $unit;

    private $children;
    private $description;
    private $total;
    private $passed;
    private $failed;
    private $level;
    private $previous;

    public function __construct($description)
    {
        $this->children    = array();
        $this->description = $description;
        $this->total       = 0;
        $this->passed      = 0;
        $this->failed      = 0;
        $this->level       = 0;
    }

    public function add($group)
    {
        array_push($this->children, $group);
        return $this;
    }

    public function run()
    {
        $this->set_up();
        $this->total  = 0;
        $this->passed = 0;
        $this->failed = 0;
        $this->level  = 1;
        print format("{yellow}{bold}{underline}" . $this->description . "{/underline}:{/bold}{/yellow}\n");
        foreach ($this->children as $group) {
            $this->run_group($group);
        }
        print "\n";
        $this->tear_down();
        print format(
            "{bold}Expectations: {$this->total}, {/bold}"
          . "{bold}passed: {/bold}{green}{$this->passed}{/green}{bold}, {/bold}"
          . "{bold}failed {/bold}{red}{$this->failed}{/red}{bold}; {/bold}"
          . '{bold}' . sprintf('%.2f%% successful', round(($this->passed / $this->total) * 100, 2)) . ".{/bold}\n"
        );

        exit ($this->failed ? 0x01 : 0x00);
    }

    private function set_up()
    {
        $this->previous = set_error_handler(array($this, 'catch_error'));
    }

    private function tear_down()
    {
        set_error_handler($this->previous);
        $this->previous = null;
    }

    private function padd($offset = 0)
    {
        return str_repeat('  ', $this->level + $offset - 1);
    }

    private function run_group($group, $topic = null)
    {
        foreach ($group as $message => $action) {
            $this->level ++;
            if (is_array($action)) {
                print $this->padd() . format("{yellow}{bold}" . $message . ":{/bold}{/yellow}\n");
                $this->run_group($action, $topic);
            } else if (is_callable($action)) {
                if ('topic' === $message) {
                    $topic = $this->invoke($action);
                    if (self::$unit['failed']) {
                        print ($padd = $this->padd()) . format("{white}- $message: {/white}");
                        $this->failed --;
                    } else {
                        $this->total  --;
                        $this->passed --;
                    }
                } else {
                    print ($padd = $this->padd()) . format("{white}- $message: {/white}");
                    $this->invoke($action, array($topic));
                    print str_repeat(' ', 64 - strlen($padd) - strlen($message));
                }
                if (self::$unit['failed']) {
                    print format("{red}{bold}" . 'FAIL' . "{/bold}{/red}\n");
                    print ($padd = $this->padd(+1)) . str_replace(array("\r\n", "\r", "\n"), "\n" . $padd, wordwrap(self::$unit['message'], 120)) . "\n";
                } else if ('topic' !== $message) {
                    print format("{green}{bold}" . 'PASS' . "{/bold}{/green}\n");
                }
            }
            $this->level --;
        }
    }

    private function invoke($callable, array $args = array())
    {
        self::$unit = array('failed' => false, 'message' => null);
        $result = null;
        $this->total ++;
        try {
            assert::before();
            $result = call_user_func_array($callable, $args);
        } catch (\Exception $exception) {
            $this->catch_exception($exception);
        }
        try {
            assert::after();
        } catch (\Exception $exception) {
            $this->catch_exception($exception);
        }
        if (self::$unit['failed']) {
            $this->failed ++;
        } else {
            $this->passed ++;
        }
        return $result;
    }

    public function catch_error($code, $message, $file, $line)
    {
        self::$unit['failed']  = true;
        self::$unit['message'] = format("{red}$message in $file at line $line{/red}");
    }

    private function catch_exception($exception)
    {
        self::$unit['failed']  = true;
        self::$unit['message'] = format('{red}' . (string) $exception . '{/red}');
    }
}


final class AssertException extends \Exception
{
    public function __construct($message)
    {
        $this->message = $message;
    }

    public function __toString()
    {
        return basename(__CLASS__) . ': ' . $this->message;
    }
}

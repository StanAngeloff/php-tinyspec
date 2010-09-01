<?php

namespace Spec;


function describe($description)
{ return new Group($description); }

const QUEUE_EMPTY = ':empty';
const QUEUE_POP   = ':pop';

function assert_queue($block)
{
    static $queue = array();
    if (QUEUE_EMPTY === $block) {
        return $queue = array();
    } else if (QUEUE_POP === $block) {
        return array_pop($queue);
    }
    array_push($queue, $block);
    return sizeof ($queue);
}

function assert_before()
{ assert_queue(QUEUE_EMPTY); }

function assert_after()
{
    while ($operation = assert_queue(QUEUE_POP)) {
        call_user_func($operation);
        if (Group::$unit['failed']) {
            break;
        }
    }
}

function assert_throws($needle)
{
    assert_queue(function() use ($needle) {
        assert_is_true(Group::$unit['failed'], format("{red}did not throw '{bold}$needle{/bold}'{/red}"));
        assert_is_not_false(strpos(Group::$unit['message'], $needle), format('{red}' . Group::$unit['message'] . " did not contain '{bold}$needle{/bold}'{/red}"));
        Group::$unit['failed'] = false;
    });
}

function assert_is_true($subject, $message)
{ assert_must(true === $subject, $message); }

function assert_is_not_true($subject, $message)
{ assert_must(true !== $subject, $message); }

function assert_is_false($subject, $message)
{ assert_must(false === $subject, $message); }

function assert_is_not_false($subject, $message)
{ assert_must(false !== $subject, $message); }

function assert_is_null($subject, $message)
{ assert_must(null === $subject, $message); }

function assert_is_not_null($subject, $message)
{ assert_must(null !== $subject, $message); }

function assert_key_missing($array, $key, $message = null)
{ assert_is_false(array_key_exists($key, $array), (isset ($message) ? $message : "'$key' key was present")); }

function assert_key_not_missing($array, $key, $message = null)
{ assert_must(array_key_exists($key, $array), (isset ($message) ? $message : "'$key' key was missing")); }

function assert_is_array($subject, $message = null)
{ assert_must(is_array($subject), (isset ($message) ? $message : "'$subject' is not an 'array'")); }

function assert_is_string($subject, $message = null)
{ assert_must(is_string($subject), (isset ($message) ? $message : "'$subject' is not a 'string'")); }

function assert_value_empty($subject, $message)
{ assert_must(empty($subject), $message); }

function assert_value_not_empty($subject, $message)
{ assert_must( ! empty($subject), $message); }

function assert_hash_equal($subject, $reference, $message)
{ assert_must(0 === strcmp(sha1(serialize($subject)), sha1(serialize($reference))), $message); }

function assert_is_reference(&$a, &$b, $message = null)
{
    if (is_array($a)) {
        $a['__assert_reference'] = true;
        assert_is_true(isset ($b['__assert_reference']), (isset ($message) ? $message : 'array $b did not appear to be a reference to array $a'));
        unset ($b['__assert_reference']);
    } else {
        $a->__assert_reference = true;
        assert_is_true(isset ($b->__assert_reference), (isset ($message) ? $message : 'objects $b did not appear to be a reference to object $a'));
        unset ($b->__assert_reference);
    }
}

function assert_must($condition, $message)
{
    if ( ! $condition) {
        throw new AssertException($message);
    }
}

function format($string)
{
    return str_replace(
        array('{bold}',  '{/bold}',  '{underline}', '{/underline}', '{yellow}', '{/yellow}', '{red}',    '{/red}',   '{green}',  '{/green}', '{white}',  '{/white}'),
        array("\033[1m", "\033[22m", "\033[4m",     "\033[24m",     "\033[33m",  "\033[39m", "\033[31m", "\033[39m", "\033[32m", "\033[39m", "\033[37m", "\033[39m"),
        $string
    );
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
            assert_before();
            $result = call_user_func_array($callable, $args);
        } catch (\Exception $exception) {
            $this->catch_exception($exception);
        }
        try {
            assert_after();
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

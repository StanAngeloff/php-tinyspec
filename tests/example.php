<?php

require __DIR__ . '/../lib/tinyspec.php';


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
      \Spec\assert_must(4 == $topic->result, 'did not return correct value');
    },
    'sum fails with strings' => function($topic) {
      \Spec\assert_throws('cannot sum strings');
      $topic->sum('left', 'right');
    }
  )
))->run();


final class Calculator
{
    public $result;

    public function __constructor()
    {}

    public function sum($a, $b)
    {
        if (is_string($a) || is_string($b)) {
            throw new \Exception('cannot sum strings');
        }
        $this->result = $a + $b;
    }
}

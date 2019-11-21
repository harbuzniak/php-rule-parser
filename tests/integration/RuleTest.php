<?php

declare(strict_types=1);

/**
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        https://github.com/nicoSWD
 * @author      Nicolas Oelgart <nico@oelgart.com>
 */

use nicoSWD\Rule;

class RuleTest extends \PHPUnit\Framework\TestCase
{
    public function testBasicRuleWithCommentsEvaluatesCorrectly()
    {
        $string = '
            /**
             * This is a test rule with comments
             */

             // This is true
             2 < 3 && (
                // this is false, because foo does not equal 4
                foo == 4
                // but bar is greater than 6
                || bar > 6
             )';

        $vars = [
            'foo' => 5,
            'bar' => 7,
        ];

        $rule = new Rule\Rule($string, $vars);

        $this->assertTrue($rule->isTrue());
        $this->assertTrue(!$rule->isFalse());
    }

    public function testVariableCallback()
    {
        $string = 'foo.bar === 10 && bar.foo === 5 && foo.bar > bar.foo2 && a === 10';
        $map = [
            'foo.bar' => 10,
            'bar.foo' => 5,
            'bar.foo2' => 1,
            'a' => 10,
        ];
        $rule = new Rule\Rule($string, [], function (string $name) use ($map) {
            return $map[$name];
        });
        $this->assertTrue($rule->isTrue());
    }

    public function testStringVariableCallback()
    {
        $string = 'foo.bar === "a@b.c" && bar.foo === \'https://ab.c\' ';
        $map = [
            'foo.bar' => 'a@b.c',
            'bar.foo' => 'https://ab.c',
        ];
        $rule = new Rule\Rule($string, [], function (string $name) use ($map) {
            return $map[$name];
        });
        $this->assertTrue($rule->isTrue());
    }

    public function testIsValidReturnsFalseOnInvalidSyntax()
    {
        $ruleStr = '(2 == 2) && (1 < 3 && 3 > 2 (1 == 1))';

        $rule = new Rule\Rule($ruleStr);

        $this->assertFalse($rule->isValid());
        $this->assertSame('Unexpected "(" at position 28', $rule->getError());
    }

    public function testIsValidReturnsTrueOnValidSyntax()
    {
        $ruleStr = '(2 == 2) && (1 < 3 && 3 > 2 || (1 == 1))';

        $rule = new Rule\Rule($ruleStr);

        $this->assertTrue($rule->isValid());
        $this->assertEmpty($rule->getError());
        $this->assertTrue($rule->isTrue());
    }
}

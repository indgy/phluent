<?php

namespace Tests\Unit;

use function Phluent\is_assoc;
use function Phluent\is_json;
use function Phluent\str_split_caps;
use function Phluent\collect;
use function Phluent\query;
use Phluent\Collection;
use Phluent\Tests\Assets\Contact;


class FunctionsTest extends \PHPUnit\Framework\TestCase
{
    function testIsAssocShouldReturnFalseIfNotArray()
    {
        $data = [
            'bob@example.com',
            false,
            true,
            456
        ];
        foreach ($data as $a) {
            $this->assertFalse(is_assoc($a));
        }
    }
    function testIsAssocShouldReturnFalseIfIndexedArray()
    {
        $a = [
            'bob@example.com',
            'jill@example.com',
            'john@example.com',
        ];
        $this->assertFalse(is_assoc($a));
    }
    function testIsAssocShouldReturnFalseIfMixedArrayKeys()
    {
        $a = [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            1 => 'This is one'
        ];
        $this->assertTrue(is_assoc($a));
    }
    function testIsAssocShouldReturnTrueAssocArray()
    {
        $a = [
            'name' => 'Bob',
            'email' => 'bob@example.com',
        ];
        $this->assertTrue(is_assoc($a));
    }

    function testIsJsonShouldReturnTrueWithJsonString()
    {
        $a = json_encode([
            'name' => 'Bob',
            'email' => 'bob@example.com',
        ]);
        $this->assertTrue(is_json($a));
    }
    function testIsJsonShouldReturnFalse()
    {
        $a = [
            'name' => 'Bob',
            'email' => 'bob@example.com',
        ];
        $this->assertFalse(is_json($a));
        $a = 'Bob';
        $this->assertFalse(is_json($a));
        $a = 456;
        $this->assertFalse(is_json($a));
    }

    /**
     * @dataProvider provideStringsToSplit
     */
    function testStrSplitCapsShould($expected, $str)
    {
        $this->assertEquals($expected, str_split_caps($str));
    }
    public function provideStringsToSplit()
    {
        return [
            'caps' => ['A String Split On Caps', 'AStringSplitOnCaps'],
            'caps trimmed' => ['A String Split On Caps', ' AStringSplitOnCaps '],
            'caps with spaces' => ['A String Split On Caps', ' AString SplitOn Caps '],
        ];
    }

    function testQueryReturnsInstanceOfQuery()
    {
        $this->assertInstanceOf('\Phluent\Query', query());
    }
    function testQueryReturnsInstanceOfQueryWithTableNameSet()
    {
        $q = query('contacts');
        $this->assertInstanceOf('\Phluent\Query', $q);
        $this->assertEquals('SELECT * FROM `contacts`', $q->getSql());
    }
    
    function testCollectReturnsInstanceOfCollection()
    {
        $this->assertInstanceOf(Collection::class, collect([]));
    }
    function testCollectAcceptsAssocArrays()
    {
        $items = [
            [
                'first_name' => 'Bob',
                'last_name' => 'Dobalina',
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
            ]
        ];
        $c = collect($items, Contact::class);
        $this->assertInstanceOf(Collection::class, $c);
        $this->assertEquals(2, $c->count());
        $this->assertInstanceOf(Contact::class, $c->first());
    }
    function testCollectAcceptsObjects()
    {
        $items = [
            (object) [
                'first_name' => 'Bob',
                'last_name' => 'Dobalina',
            ],
            (object) [
                'first_name' => 'John',
                'last_name' => 'Smith',
            ]
        ];
        $c = collect($items, Contact::class);

        $this->assertInstanceOf(Collection::class, $c);
        $this->assertEquals(2, $c->count());
        $this->assertInstanceOf(Contact::class, $c->first());
    }
    function testCollectAcceptsEntities()
    {
        $items = [
            new Contact([
                'first_name' => 'Bob',
                'last_name' => 'Dobalina',
            ]),
            new Contact([
                'first_name' => 'John',
                'last_name' => 'Smith',
            ])
        ];
        $c = collect($items);

        $this->assertInstanceOf(Collection::class, $c);
        $this->assertEquals(2, $c->count());
        $this->assertInstanceOf(Contact::class, $c->first());
    }
    function testCollectThrowsExceptionWhenNotProvidingEntity()
    {
        $this->expectException('InvalidArgumentException');
        $items = [
            [
                'first_name' => 'Bob',
                'last_name' => 'Dobalina',
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
            ]
        ];
        $c = collect($items);
    }
}

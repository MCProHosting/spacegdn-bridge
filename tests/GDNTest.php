<?php

use Mcprohosting\Spacegdn\Bridge;

class GDNTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testBaseFunctionality()
    {
        $out = ['results' => ['foo', 'bar']];
        $response = Mockery::mock('GuzzleHttp\Client');
        $response->shouldReceive('json')->once()->andReturn($out);

        $client = Mockery::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->with('http://foo/v2/?json')->once()->andReturn($response);

        $bridge = new Bridge($client);
        $bridge->setEndpoint('foo');
        $this->assertEquals($out, $bridge->results());
        $this->assertEquals($out['results'], $bridge->toArray());
        $this->assertEquals($out['results'], $bridge->results);
        $this->assertEquals(2, count($bridge));
        $this->assertEquals(json_encode($out['results']), $bridge->__toString());

        foreach ($bridge as $item) {
            $this->assertEquals(array_shift($out['results']), $item);
        }
    }

    public function testParameters()
    {
        $bridge = new Bridge();
        $bridge->setEndpoint('foo')
            ->withParents()
            ->orderBy('date', 'desc')
            ->page(2)
            ->get('jar');
        $this->assertEquals('http://foo/v2/?parents=1&sort=date.desc&page=2&r=jar&json', $bridge->buildUrl());
    }

    public function testSetsEndpoint()
    {
        $bridge = new Bridge();
        $bridge->setEndpoint('foo');
        $this->assertEquals('http://foo/v2/?json', $bridge->buildUrl());

        $bridge->setEndpoint('https://foo');
        $this->assertEquals('https://foo/v2/?json', $bridge->buildUrl());

        $bridge->setEndpoint('http://foo');
        $this->assertEquals('http://foo/v2/?json', $bridge->buildUrl());
    }

    public function testWhere()
    {
        $bridge = new Bridge();
        $bridge->where('foo', '$eq', 'bar');
        $this->assertEquals('v2/?where=foo.%24eq.bar&json', $bridge->buildUrl());

        $bridge->where('foo', '$in', ['a', 'b']);
        $this->assertEquals('v2/?where=foo.%24in.a%2Cb&json', $bridge->buildUrl());
    }

    public function testBeloning()
    {
        $bridge = new Bridge();
        $bridge->belongingTo('a');
        $this->assertEquals('v2/a/?json', $bridge->buildUrl());
        $bridge->clear();

        $bridge->belongingTo(['a', 'b', 'c']);
        $this->assertEquals('v2/a/b/c/?json', $bridge->buildUrl());
    }
} 

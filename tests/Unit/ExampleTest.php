<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Redis;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        try {
            //create redis instance
            $redis = new Redis();
            //connect with server and port
            $redis->connect('localhost', 6379);
            //set value
            $redis->set('website', 'www.phpflow.com');
            //get value
            $website = $redis->get('website');
            //print www.phpflow.com
            echo $website;
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
}

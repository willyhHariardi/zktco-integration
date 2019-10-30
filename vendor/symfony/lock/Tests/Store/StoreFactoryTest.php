<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Lock\Tests\Store;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Traits\RedisProxy;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\MemcachedStore;
use Symfony\Component\Lock\Store\RedisStore;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Lock\Store\StoreFactory;
use Symfony\Component\Lock\Store\ZookeeperStore;

/**
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class StoreFactoryTest extends TestCase
{
    /**
     * @dataProvider validConnections
     */
    public function testCreateStore($connection, string $expectedStoreClass)
    {
        $store = StoreFactory::createStore($connection);

        $this->assertInstanceOf($expectedStoreClass, $store);
    }

    public function validConnections()
    {
        if (class_exists(\Redis::class)) {
            yield [$this->createMock(\Redis::class), RedisStore::class];
        }
        if (class_exists(RedisProxy::class)) {
            yield [$this->createMock(RedisProxy::class), RedisStore::class];
        }
        yield [new \Predis\Client(), RedisStore::class];
        if (class_exists(\Memcached::class)) {
            yield [new \Memcached(), MemcachedStore::class];
        }
        if (class_exists(\Zookeeper::class)) {
            yield [$this->createMock(\Zookeeper::class), ZookeeperStore::class];
        }
        if (\extension_loaded('sysvsem')) {
            yield ['semaphore', SemaphoreStore::class];
        }
        if (class_exists(\Memcached::class) && class_exists(AbstractAdapter::class)) {
            yield ['memcached://server.com', MemcachedStore::class];
        }

        yield ['flock', FlockStore::class];
        yield ['flock://'.sys_get_temp_dir(), FlockStore::class];
    }
}

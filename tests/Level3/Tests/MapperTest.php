<?php
/*
 * This file is part of the Level3 package.
 *
 * (c) Máximo Cuadros <maximo@yunait.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Level3\Tests;
use Level3\Hub;
use Level3\Resource\Parameters;

use Mockery as m;

class MapperTest extends TestCase
{
    public function getMapperMock($constructor = array())
    {
        return m::mock(
            'Level3\Mapper[mapFinder,mapGetter,mapPoster,mapPutter,mapDeleter,mapPatcher]',
            $constructor
        );
    }

    public function testSetBaseURI()
    {
        $mapper = $this->getMapperMock();

        $expected = 'foo/';
        $mapper->setBaseURI($expected);

        $this->assertSame($expected, $mapper->getBaseURI());
    }

    public function testSetBaseURIWithoutTrallingSlash()
    {
        $mapper = $this->getMapperMock();

        $expected = 'foo/';
        $mapper->setBaseURI('foo');

        $this->assertSame($expected, $mapper->getBaseURI());
    }

    public function testBoot()
    {
        $repository = new RepositoryMock($this->createLevel3Mock());
        $repository->setKey('foo');

        $hub =  m::mock('Level3\Hub');
        $hub->shouldReceive('get')->once()->with('foo')->andReturn($repository);
        $hub->shouldReceive('getKeys')->once()->andReturn(array('foo'));

        $mapper = $this->getMapperMock();
        $mapper->shouldReceive('mapGetter')->once()->with($repository, '/foo/{id}');
        $mapper->shouldReceive('mapPutter')->once()->with($repository, '/foo');
        $mapper->shouldReceive('mapPoster')->once()->with($repository, '/foo/{id}');
        $mapper->shouldReceive('mapDeleter')->once()->with($repository, '/foo/{id}');
        $mapper->shouldReceive('mapFinder')->once()->with($repository, '/foo');
        $mapper->shouldReceive('mapPatcher')->once()->with($repository, '/foo/{id}');

        $mapper->boot($hub);
    }

    public function testGetCurieURI()
    {
        $mapper = $this->getMapperMock();
        $this->assertSame(
            '/foo/{id}', 
            $mapper->getCurieURI('foo', 'Level3\Repository\Deleter')
        );
    }

    public function testGetURI()
    {
        $mapper = $this->getMapperMock();
        $this->assertSame(
            '/foo/1', 
            $mapper->getURI(
                'foo', 
                'Level3\Repository\Deleter', 
                new Parameters(array('id' => 1))
            )
        );
    }

    public function testGetURIWithOutParams()
    {
        $mapper = $this->getMapperMock();
        $this->assertSame(
            '/foo', 
            $mapper->getURI(
                'foo', 
                'Level3\Repository\Finder'
            )
        );
    }
}


class RepositoryMock
    extends 
        \Level3\Repository 
    implements 
        \Level3\Repository\Getter, 
        \Level3\Repository\Finder, 
        \Level3\Repository\Putter, 
        \Level3\Repository\Poster, 
        \Level3\Repository\Deleter,
        \Level3\Repository\Patcher
{
    public function delete(Parameters $parameters) {}
    public function get(Parameters $parameters) {}
    public function post(Parameters $parameters, $data) {}
    public function put(Parameters $parameters, $data) {}
    public function patch(Parameters $parameters, $data) {}
    public function find(Parameters $parameters, $sort, $lowerBound, $upperBound, array $criteria) {}

}